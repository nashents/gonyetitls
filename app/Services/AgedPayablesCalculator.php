<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Bill;
use App\Models\SupplierJournal;

/**
 * Aged Payables: outstanding vendor balances bucketed by how overdue they
 * are, as of a given date. Unlike Aged Receivables, Bill.due_date is a
 * real, required due date (not a fallback-to-self field like
 * Invoice.expiry), so this buckets by days *past due* rather than by bill
 * age, with a "Current" bucket for bills that exist but aren't due yet -
 * the standard shape of an accounts-payable aging report. Filters on
 * balance > 0 rather than status, since a "Partial" bill still owes money.
 */
class AgedPayablesCalculator
{
    const BUCKETS = ['Current', '1-30 Days', '31-60 Days', '61-90 Days', '90+ Days'];

    public function __construct(
        private string $asOfDate,
        private $defaultCurrencyId
    ) {
    }

    /**
     * Bills have no separate "exchange_balance" column (only a native
     * `balance` and the `exchange_rate` captured at bill creation), so
     * foreign-currency balances are converted using that rate directly.
     */
    private function reportingAmount($currencyId, $nativeAmount, $exchangeRate): float
    {
        if (!ReportFormatter::isNumericAmount($nativeAmount)) {
            return 0.0;
        }

        $isBaseCurrency = is_null($currencyId) || (int) $currencyId === (int) $this->defaultCurrencyId;

        if ($isBaseCurrency) {
            return (float) $nativeAmount;
        }

        $rate = ReportFormatter::isNumericAmount($exchangeRate) ? (float) $exchangeRate : 1.0;

        return (float) $nativeAmount * $rate;
    }

    private function bucketFor(int $daysOverdue): string
    {
        return match (true) {
            $daysOverdue <= 0 => self::BUCKETS[0],
            $daysOverdue <= 30 => self::BUCKETS[1],
            $daysOverdue <= 60 => self::BUCKETS[2],
            $daysOverdue <= 90 => self::BUCKETS[3],
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

        // A bill only "exists" as of the report date once it's been
        // entered; its due date may still be in the future (Current).
        $bills = Bill::whereDate('bill_date', '<=', $this->asOfDate)
            ->where('authorization', 'approved')
            ->whereRaw('CAST(balance AS DECIMAL(18,2)) > 0')
            ->with('vendor:id,name')
            ->get(['id', 'vendor_id', 'currency_id', 'due_date', 'balance', 'exchange_rate']);

        $rows = [];
        $grandTotals = array_fill_keys(self::BUCKETS, 0.0);
        $grandTotal = 0.0;

        foreach ($bills as $bill) {
            $amount = $this->reportingAmount($bill->currency_id, $bill->balance, $bill->exchange_rate);

            if ($amount == 0) {
                continue;
            }

            $vendorId = $bill->vendor_id ?? 0;
            // Positive = days past due, negative/zero = not yet due (Current).
            $daysOverdue = Carbon::parse($bill->due_date)->startOfDay()->diffInDays($asOf, false);
            $bucket = $this->bucketFor($daysOverdue);

            $rows[$vendorId] ??= [
                'label' => $bill->vendor->name ?? 'Uncategorized Vendor',
                'buckets' => array_fill_keys(self::BUCKETS, 0.0),
                'total' => 0.0,
            ];

            $rows[$vendorId]['buckets'][$bucket] += $amount;
            $rows[$vendorId]['total'] += $amount;

            $grandTotals[$bucket] += $amount;
            $grandTotal += $amount;
        }

        // Suppliers journals: a credit is an open amount owed from its own
        // date (journals carry no due date, so the date is treated as due);
        // a debit's unallocated remainder reduces the vendor's balance in
        // that date's bucket (the allocated part already came off a bill
        // balance above).
        $allocated = '(select COALESCE(SUM(bp.amount+0),0) from bill_payments bp where bp.supplier_journal_id = supplier_journals.id and bp.deleted_at is null)';

        $journals = SupplierJournal::posted()
            ->whereDate('date', '<=', $this->asOfDate)
            ->with('vendor:id,name')
            ->select('supplier_journals.*')
            ->selectRaw("{$allocated} as allocated_total")
            ->get();

        foreach ($journals as $journal) {
            $open = $journal->isDebit()
                ? -round((float) $journal->amount - (float) $journal->allocated_total, 2)
                : (float) $journal->amount;

            $amount = $this->reportingAmount($journal->currency_id, number_format($open, 2, '.', ''), (string) $journal->exchange_rate);

            if (abs($amount) < 0.005) {
                continue;
            }

            $vendorId = $journal->vendor_id ?? 0;
            $daysOverdue = Carbon::parse($journal->date)->startOfDay()->diffInDays($asOf, false);
            $bucket = $this->bucketFor($daysOverdue);

            $rows[$vendorId] ??= [
                'label' => $journal->vendor->name ?? 'Uncategorized Vendor',
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
