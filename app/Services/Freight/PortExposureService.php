<?php

namespace App\Services\Freight;

use App\Models\ChargeFreeDayPolicy;
use App\Models\ChargeRateTier;
use App\Models\ContainerChargeExposure;
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

    public function startTracking(ShippingContainer $container, string $chargeType, Carbon $startDate): ?ContainerChargeExposure
    {
        $policy = $this->resolvePolicy($chargeType, $container->shipping_line_vendor_id);

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

        $tiers = $this->resolveRateTiers($exposure->charge_type, $exposure->shipping_container->shipping_line_vendor_id);

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
        foreach (self::START_MAP[$newStatus] ?? [] as $chargeType) {
            $this->startTracking($container, $chargeType, $eventDate);
        }

        foreach (self::STOP_MAP[$newStatus] ?? [] as $chargeType) {
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

    private function resolvePolicy(string $chargeType, ?int $vendorId): ?ChargeFreeDayPolicy
    {
        $lineSpecific = ChargeFreeDayPolicy::where('charge_type', $chargeType)
            ->where('shipping_line_vendor_id', $vendorId)
            ->orderBy('id')
            ->first();

        if ($lineSpecific) {
            return $lineSpecific;
        }

        if ($vendorId === null) {
            return null;
        }

        return ChargeFreeDayPolicy::where('charge_type', $chargeType)
            ->whereNull('shipping_line_vendor_id')
            ->orderBy('id')
            ->first();
    }

    private function resolveRateTiers(string $chargeType, ?int $vendorId): Collection
    {
        $lineSpecific = ChargeRateTier::where('charge_type', $chargeType)
            ->where('shipping_line_vendor_id', $vendorId)
            ->get();

        if ($lineSpecific->isNotEmpty()) {
            return $lineSpecific;
        }

        return ChargeRateTier::where('charge_type', $chargeType)
            ->whereNull('shipping_line_vendor_id')
            ->get();
    }
}
