<?php

namespace App\Services\Freight;

use App\Models\FreightJob;
use App\Models\FreightRateCard;
use App\Models\ShippingContainer;
use Carbon\Carbon;

/**
 * Finds the rate card that prices a freight job cost (buy) or charge (sell)
 * line, and works out the line's quantity and amount from it.
 *
 * A blank field on a card means "any". Every filled field must match:
 *   line:  charge type, vendor (buy) / customer (sell), container type, date
 *   job:   freight service type, primary transport mode, from/to destination,
 *          cargo on the job's shipments
 * The most specific card wins (most filled fields), then the latest
 * effective_from, then the newest card.
 */
class FreightRateCardMatcher
{
    public function match(
        FreightJob $job,
        string $direction,
        $chargeTypeId,
        $partyId = null,
        $containerId = null,
        $date = null
    ): ?FreightRateCard {
        if (!$chargeTypeId) {
            return null;
        }

        $date = $date ? Carbon::parse($date) : ($job->opened_at ?? now());
        $date = $date->toDateString();
        $partyColumn = $direction === 'buy' ? 'vendor_id' : 'customer_id';

        $job->loadMissing(['shipments.containers', 'shipments.cargo_items']);
        $containerTypes = $this->containerTypes($job, $containerId);
        $cargoIds = $job->shipments->flatMap->cargo_items->pluck('cargo_id')->filter()->unique()->values()->all();

        $orNull = fn ($column, $value) => fn ($q) => $value
            ? $q->whereNull($column)->orWhere($column, $value)
            : $q->whereNull($column);

        $candidates = FreightRateCard::query()
            ->where('is_active', true)
            ->where('direction', $direction)
            ->whereNotNull('rate')
            ->where($orNull('charge_type_id', $chargeTypeId))
            ->where($orNull($partyColumn, $partyId))
            ->where($orNull('freight_service_type_id', $job->freight_service_type_id))
            ->where($orNull('mode', $job->primary_transport_mode))
            ->where($orNull('origin_id', $job->from))
            ->where($orNull('destination_id', $job->to))
            ->where(fn ($q) => $q->whereNull('cargo_id')->orWhereIn('cargo_id', $cargoIds ?: [0]))
            ->where(fn ($q) => $q->whereNull('effective_from')->orWhereDate('effective_from', '<=', $date))
            ->where(fn ($q) => $q->whereNull('effective_to')->orWhereDate('effective_to', '>=', $date))
            ->get()
            ->filter(fn (FreightRateCard $card) => blank($card->container_type)
                || in_array(mb_strtolower(trim($card->container_type)), $containerTypes, true));

        return $candidates
            ->sortBy([
                fn ($a, $b) => $this->specificity($b) <=> $this->specificity($a),
                fn ($a, $b) => optional($b->effective_from)->timestamp <=> optional($a->effective_from)->timestamp,
                fn ($a, $b) => $b->id <=> $a->id,
            ])
            ->first();
    }

    /**
     * Quantity, rate and amount for a line priced from $card.
     *
     * @return array{quantity: float, chargeable_days: ?int, rate: ?float, amount: ?float, currency_id: ?int}
     */
    public function price(FreightRateCard $card, FreightJob $job, $containerId = null, $chargeableDays = null): array
    {
        $job->loadMissing(['shipments.containers', 'shipments.cargo_items']);
        $cargo = $job->shipments->flatMap->cargo_items;
        $days = null;

        switch ($card->rate_basis) {
            case 'per_container':
                $quantity = $containerId ? 1 : max(1, $job->shipments->flatMap->containers->count());
                break;
            case 'per_kg':
                $quantity = (float) $cargo->sum(fn ($item) => (float) ($item->chargeable_weight ?: $item->gross_weight));
                break;
            case 'per_cbm':
                $quantity = (float) $cargo->sum('cbm');
                break;
            case 'per_day':
                $quantity = 1;
                $days = (int) $chargeableDays ?: 1;
                break;
            default: // flat, per_shipment, per_document
                $quantity = 1;
        }

        $rate = $card->unitRate();
        $amount = $rate === null ? null : round($rate * $quantity * ($days ?? 1), 2);

        return [
            'quantity' => $quantity,
            'chargeable_days' => $days,
            'rate' => $rate,
            'amount' => $amount,
            'currency_id' => $card->currency_id,
        ];
    }

    /** Container type(s) a card's container_type is checked against (lower-cased). */
    private function containerTypes(FreightJob $job, $containerId): array
    {
        $containers = $containerId
            ? ShippingContainer::whereKey($containerId)->get()
            : $job->shipments->flatMap->containers;

        return $containers->pluck('container_type')->filter()
            ->map(fn ($type) => mb_strtolower(trim($type)))->unique()->values()->all();
    }

    private function specificity(FreightRateCard $card): int
    {
        return collect([
            $card->charge_type_id, $card->vendor_id, $card->customer_id, $card->freight_service_type_id,
            $card->mode, $card->origin_id, $card->destination_id, $card->cargo_id, $card->container_type,
        ])->filter(fn ($value) => filled($value))->count();
    }
}
