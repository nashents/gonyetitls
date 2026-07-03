<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Invoice;

/**
 * Aged Receivables: outstanding customer balances bucketed by age, as of
 * a given date. Ages by invoice date (there is no reliable due-date field
 * on Invoice - `expiry` is a quote-validity date, not a payment due date,
 * and no payment-terms concept exists for customer invoices), and filters
 * on balance > 0 rather than status, since a "Partial" invoice still owes
 * money and must be included. Mirrors the bucket definitions already used
 * by the existing Customers > Debtors report (0-30 / 31-60 / 61-90 / 90+).
 */
class AgedReceivablesCalculator
{
    const BUCKETS = ['0-30 Days', '31-60 Days', '61-90 Days', '90+ Days'];

    public function __construct(
        private string $asOfDate,
        private $defaultCurrencyId
    ) {
    }

    /**
     * Invoices have no separate "exchange_balance" column (only a native
     * `balance` and the `exchange_rate` captured at invoice creation), so
     * foreign-currency balances are converted using that rate directly -
     * the same approach SalesTaxCalculator uses for line items.
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

    private function bucketFor(int $daysOld): string
    {
        return match (true) {
            $daysOld <= 30 => self::BUCKETS[0],
            $daysOld <= 60 => self::BUCKETS[1],
            $daysOld <= 90 => self::BUCKETS[2],
            default => self::BUCKETS[3],
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
        $asOf = Carbon::parse($this->asOfDate);

        $invoices = Invoice::whereDate('date', '<=', $this->asOfDate)
            ->where('authorization', 'approved')
            ->whereRaw('CAST(balance AS DECIMAL(18,2)) > 0')
            ->with('customer:id,name')
            ->get(['id', 'customer_id', 'currency_id', 'date', 'balance', 'exchange_rate']);

        $rows = [];
        $grandTotals = array_fill_keys(self::BUCKETS, 0.0);
        $grandTotal = 0.0;

        foreach ($invoices as $invoice) {
            $amount = $this->reportingAmount($invoice->currency_id, $invoice->balance, $invoice->exchange_rate);

            if ($amount == 0) {
                continue;
            }

            $customerId = $invoice->customer_id ?? 0;
            // Invoice date is guaranteed <= asOfDate by the query filter above,
            // so an unsigned diff is unambiguous here.
            $daysOld = Carbon::parse($invoice->date)->startOfDay()->diffInDays($asOf->copy()->startOfDay());
            $bucket = $this->bucketFor($daysOld);

            $rows[$customerId] ??= [
                'label' => $invoice->customer->name ?? 'Uncategorized Customer',
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
