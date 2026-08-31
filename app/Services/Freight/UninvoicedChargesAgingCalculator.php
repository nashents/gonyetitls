<?php

namespace App\Services\Freight;

use App\Models\FreightCharge;
use Carbon\Carbon;

/**
 * Uninvoiced Freight Charges Aging: approved FreightCharge lines that have
 * not yet been turned into an Invoice (see FreightAccountingService, which
 * uses this exact eligibility filter but scoped to one job at a time),
 * bucketed by how long they've sat uninvoiced since date_billed. Mirrors
 * AgedPayablesCalculator's bucket shape.
 */
class UninvoicedChargesAgingCalculator
{
    const ELIGIBLE_STATUS = 'approved';
    const BUCKETS = ['Current', '1-30 Days', '31-60 Days', '61-90 Days', '90+ Days'];

    public function __construct(private string $asOfDate)
    {
    }

    private function bucketFor(int $daysUninvoiced): string
    {
        return match (true) {
            $daysUninvoiced <= 0 => self::BUCKETS[0],
            $daysUninvoiced <= 30 => self::BUCKETS[1],
            $daysUninvoiced <= 60 => self::BUCKETS[2],
            $daysUninvoiced <= 90 => self::BUCKETS[3],
            default => self::BUCKETS[4],
        };
    }

    /**
     * @return array{
     *     0: array<int, array{label: string, buckets: array<string, float>, total: float}>,
     *     1: array{buckets: array<string, float>, total: float}
     * } [rows keyed by customer_id, grand totals]
     */
    public function byCustomer(): array
    {
        $asOf = Carbon::parse($this->asOfDate)->startOfDay();

        $charges = FreightCharge::where('status', self::ELIGIBLE_STATUS)
            ->whereNull('invoice_id')
            ->with('customer:id,name')
            ->get(['id', 'customer_id', 'date_billed', 'exchange_amount', 'created_at']);

        $rows = [];
        $grandTotals = array_fill_keys(self::BUCKETS, 0.0);
        $grandTotal = 0.0;

        foreach ($charges as $charge) {
            $amount = (float) $charge->exchange_amount;

            if ($amount == 0) {
                continue;
            }

            $customerId = $charge->customer_id ?? 0;
            $referenceDate = $charge->date_billed ?? $charge->created_at;
            $daysUninvoiced = Carbon::parse($referenceDate)->startOfDay()->diffInDays($asOf, false);
            $bucket = $this->bucketFor($daysUninvoiced);

            $rows[$customerId] ??= [
                'label' => $charge->customer->name ?? 'Unassigned Customer',
                'buckets' => array_fill_keys(self::BUCKETS, 0.0),
                'total' => 0.0,
            ];

            $rows[$customerId]['buckets'][$bucket] += $amount;
            $rows[$customerId]['total'] += $amount;

            $grandTotals[$bucket] += $amount;
            $grandTotal += $amount;
        }

        usort($rows, fn ($a, $b) => $b['total'] <=> $a['total']);

        return [$rows, ['buckets' => $grandTotals, 'total' => $grandTotal]];
    }
}
