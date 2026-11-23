<?php

namespace App\Services\Freight;

use App\Models\ChargeFreeDayPolicy;
use App\Models\ChargeRateTier;
use App\Models\ContainerChargeExposure;
use App\Models\ChargeType;
use App\Models\ShippingContainer;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PortExposureService
{
    /**
     * Which charge types start/stop tracking on a given container lifecycle
     * transition. Note collected_from_port both stops port_storage AND
     * starts detention — both fire from the same transition.
     *
     * Detention's window (collected_from_port -> empty_returned) is a strict
     * subset of Demurrage's (discharged -> empty_returned) by design — these
     * are independent, non-summable what-if estimates for two different
     * possible carrier billing structures, not additive liability. Detention
     * tracking is opt-in: startTracking() no-ops when no ChargeFreeDayPolicy
     * is configured for (detention, that container's shipping line), so
     * lines that just run one demurrage meter end-to-end never get a
     * phantom duplicate detention figure.
     */
    const START_MAP = [
        'discharged' => ['port_storage', 'demurrage'],
        'collected_from_port' => ['detention'],
    ];

    const STOP_MAP = [
        'collected_from_port' => ['port_storage'],
        'empty_returned' => ['demurrage', 'detention'],
    ];

    public function __construct(private FreeDayCalculatorService $calculator)
    {
    }

    /** Start/stop stage codes of a built-in charge (port_storage, demurrage, detention), read from the maps above. */
    public static function builtInClock(string $exposureCode): array
    {
        $find = fn (array $map) => collect($map)->search(fn ($codes) => in_array($exposureCode, $codes, true)) ?: null;

        return ['start' => $find(self::START_MAP), 'stop' => $find(self::STOP_MAP)];
    }

    /**
     * Which charge clocks each stage starts/stops: stage code =>
     * ['starts' => [charge name...], 'stops' => [...]]. Built-ins from the
     * maps above, other free-time Charge Types from their own milestones -
     * exactly what handleContainerTransition() acts on.
     */
    public static function stageClockEffects(): array
    {
        $effects = [];
        $names = ContainerChargeExposure::chargeTypes();

        foreach (['starts' => self::START_MAP, 'stops' => self::STOP_MAP] as $key => $map) {
            foreach ($map as $stage => $codes) {
                foreach ($codes as $code) {
                    $effects[$stage][$key][] = $names[$code] ?? $code;
                }
            }
        }

        $builtIn = array_keys(ContainerChargeExposure::CHARGE_TYPES);
        foreach (ChargeType::tracksFreeTime()->whereNotIn('exposure_code', $builtIn)->orderBy('name')->get() as $type) {
            if ($type->start_milestone) {
                $effects[$type->start_milestone]['starts'][] = $type->name;
            }
            if ($type->stop_milestone) {
                $effects[$type->stop_milestone]['stops'][] = $type->name;
            }
        }

        return $effects;
    }

    public function startTracking(ShippingContainer $container, string $chargeType, Carbon $startDate): ?ContainerChargeExposure
    {
        $policy = $this->resolvePolicy($chargeType, $container->shipping_line_id);

        if (!$policy) {
            return null;
        }

        $exposure = ContainerChargeExposure::updateOrCreate(
            ['shipping_container_id' => $container->id, 'charge_type' => $chargeType],
            [
                'shipment_id' => $container->shipment_id,
                'free_days' => $policy->free_days,
                'start_date' => $startDate->copy()->startOfDay(),
                'stop_date' => null,
                'status' => 'within_free_period',
            ]
        );

        return $this->recalculate($exposure);
    }

    public function stopTracking(ShippingContainer $container, string $chargeType, Carbon $stopDate): ?ContainerChargeExposure
    {
        $exposure = ContainerChargeExposure::where('shipping_container_id', $container->id)
            ->where('charge_type', $chargeType)
            ->first();

        if (!$exposure) {
            return null;
        }

        $exposure->stop_date = $stopDate->copy()->startOfDay();

        return $this->recalculate($exposure);
    }

    public function recalculate(ContainerChargeExposure $exposure): ContainerChargeExposure
    {
        $lastFreeDay = $this->calculator->lastFreeDay(Carbon::parse($exposure->start_date), $exposure->free_days);
        $stopDate = $exposure->stop_date ? Carbon::parse($exposure->stop_date) : null;
        $chargeableDays = $this->calculator->chargeableDays($lastFreeDay, $stopDate);

        $tiers = $this->resolveRateTiers($exposure->charge_type, $exposure->shipping_container->shipping_line_id);

        $exposure->last_free_day = $lastFreeDay;
        $exposure->chargeable_days = $chargeableDays;
        $exposure->estimated_exposure = $this->calculator->estimateExposure($chargeableDays, $tiers);
        $exposure->currency_id = optional($tiers->sortBy('day_from')->first())->currency_id ?? $exposure->currency_id;
        $exposure->status = $this->calculator->status($lastFreeDay, $stopDate);
        $exposure->save();

        return $exposure;
    }

    public function handleContainerTransition(ShippingContainer $container, string $newStatus, Carbon $eventDate): void
    {
        // Other Charge Types that track free time carry their own milestones
        // (built-ins never have milestones set; they use the maps above).
        $builtIn = array_keys(ContainerChargeExposure::CHARGE_TYPES);
        $customStarts = ChargeType::tracksFreeTime()->whereNotIn('exposure_code', $builtIn)
            ->where('start_milestone', $newStatus)->pluck('exposure_code')->all();
        // Stops also apply to deleted types so already-running clocks still end.
        $customStops = ChargeType::withTrashed()->tracksFreeTime()->whereNotIn('exposure_code', $builtIn)
            ->where('stop_milestone', $newStatus)->pluck('exposure_code')->all();

        foreach (array_merge(self::START_MAP[$newStatus] ?? [], $customStarts) as $chargeType) {
            $this->startTracking($container, $chargeType, $eventDate);
        }

        foreach (array_merge(self::STOP_MAP[$newStatus] ?? [], $customStops) as $chargeType) {
            $this->stopTracking($container, $chargeType, $eventDate);
        }
    }

    /**
     * Recalculates only this container's still-open exposures. Called on
     * read (e.g. when its Containers row is expanded) since status can
     * advance purely from time passing with no new lifecycle transition;
     * already-stopped exposures are immutable and are skipped.
     */
    public function refreshOpenExposures(ShippingContainer $container): Collection
    {
        return $container->exposures()->whereNull('stop_date')->get()
            ->map(fn (ContainerChargeExposure $exposure) => $this->recalculate($exposure));
    }

    private function resolvePolicy(string $chargeType, ?int $shippingLineId): ?ChargeFreeDayPolicy
    {
        $lineSpecific = ChargeFreeDayPolicy::where('charge_type', $chargeType)
            ->where('shipping_line_id', $shippingLineId)
            ->orderBy('id')
            ->first();

        if ($lineSpecific) {
            return $lineSpecific;
        }

        if ($shippingLineId === null) {
            return null;
        }

        return ChargeFreeDayPolicy::where('charge_type', $chargeType)
            ->whereNull('shipping_line_id')
            ->orderBy('id')
            ->first();
    }

    private function resolveRateTiers(string $chargeType, ?int $shippingLineId): Collection
    {
        $lineSpecific = ChargeRateTier::where('charge_type', $chargeType)
            ->where('shipping_line_id', $shippingLineId)
            ->get();

        if ($lineSpecific->isNotEmpty()) {
            return $lineSpecific;
        }

        return ChargeRateTier::where('charge_type', $chargeType)
            ->whereNull('shipping_line_id')
            ->get();
    }
}
