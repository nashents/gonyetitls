<?php

namespace App\Services\Freight;

use App\Models\FreightCost;
use Carbon\Carbon;

/**
 * Unbilled Freight Costs Aging: verified/approved FreightCost lines that
 * have not yet been turned into a Bill (see FreightAccountingService,
 * which uses this exact eligibility filter but scoped to one job at a
 * time), bucketed by how long they've sat unbilled since date_received.
 * Mirrors AgedPayablesCalculator's bucket shape.
 */
class UnbilledCostsAgingCalculator
{
    const ELIGIBLE_STATUSES = ['verified', 'approved'];
    const BUCKETS = ['Current', '1-30 Days', '31-60 Days', '61-90 Days', '90+ Days'];

    public function __construct(private string $asOfDate)
    {
    }

    private function bucketFor(int $daysUnbilled): string
    {
        return match (true) {
            $daysUnbilled <= 0 => self::BUCKETS[0],
            $daysUnbilled <= 30 => self::BUCKETS[1],
            $daysUnbilled <= 60 => self::BUCKETS[2],
            $daysUnbilled <= 90 => self::BUCKETS[3],
            default => self::BUCKETS[4],
        };
    }

    /**
     * @return array{
     *     0: array<int, array{label: string, buckets: array<string, float>, total: float}>,
     *     1: array{buckets: array<string, float>, total: float}
     * } [rows keyed by vendor_id, grand totals]
     */
    public function byVendor(): array
    {
        $asOf = Carbon::parse($this->asOfDate)->startOfDay();

        $costs = FreightCost::whereIn('verification_status', self::ELIGIBLE_STATUSES)
            ->whereNull('bill_id')
            ->with('vendor:id,name')
            ->get(['id', 'vendor_id', 'date_received', 'exchange_amount', 'created_at']);

        $rows = [];
        $grandTotals = array_fill_keys(self::BUCKETS, 0.0);
        $grandTotal = 0.0;

        foreach ($costs as $cost) {
            $amount = (float) $cost->exchange_amount;

            if ($amount == 0) {
                continue;
            }

            $vendorId = $cost->vendor_id ?? 0;
            $referenceDate = $cost->date_received ?? $cost->created_at;
            $daysUnbilled = Carbon::parse($referenceDate)->startOfDay()->diffInDays($asOf, false);
            $bucket = $this->bucketFor($daysUnbilled);

            $rows[$vendorId] ??= [
                'label' => $cost->vendor->name ?? 'Unassigned Vendor',
                'buckets' => array_fill_keys(self::BUCKETS, 0.0),
                'total' => 0.0,
            ];

            $rows[$vendorId]['buckets'][$bucket] += $amount;
            $rows[$vendorId]['total'] += $amount;

            $grandTotals[$bucket] += $amount;
            $grandTotal += $amount;
        }

        usort($rows, fn ($a, $b) => $b['total'] <=> $a['total']);

        return [$rows, ['buckets' => $grandTotals, 'total' => $grandTotal]];
    }
}
