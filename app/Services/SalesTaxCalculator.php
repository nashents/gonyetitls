<?php

namespace App\Services;

use App\Models\Tax;
use App\Models\BillExpense;
use App\Models\InvoiceItem;

/**
 * Sales Tax Report: output tax (collected on sales) vs input tax (paid on
 * purchases), grouped by Tax record. Tax rate/type is only knowable at the
 * line-item level - InvoiceItem.tax_id / BillExpense.tax_id - since neither
 * Invoice nor Bill headers carry a tax_id, only an aggregate tax_amount.
 */
class SalesTaxCalculator
{
    public function __construct(
        private string $from,
        private string $to,
        private bool $cashBasis,
        private $defaultCurrencyId
    ) {
    }

    /**
     * Convert a native line-item amount to the reporting currency. Unlike
     * Invoice/BillExpense headers (which have a pre-computed exchange
     * total), line items only carry their own exchange_rate, so subtotal
     * and tax_amount are converted independently by that rate.
     */
    private function reportingAmount($currencyId, $nativeAmount, $exchangeRate): float
    {
        $isBaseCurrency = is_null($currencyId) || (int) $currencyId === (int) $this->defaultCurrencyId;

        if (!ReportFormatter::isNumericAmount($nativeAmount)) {
            return 0.0;
        }

        if ($isBaseCurrency) {
            return (float) $nativeAmount;
        }

        $rate = ReportFormatter::isNumericAmount($exchangeRate) ? (float) $exchangeRate : 1.0;

        return (float) $nativeAmount * $rate;
    }

    /**
     * Output tax: tax collected on sales, from invoice line items on
     * approved invoices in the date range. Cash basis restricts to paid
     * invoices.
     *
     * @return array{0: float, 1: float, 2: array<int, array{taxable: float, tax: float}>}
     */
    public function outputTax(): array
    {
        $rows = InvoiceItem::whereHas('invoice', function ($q) {
                $q->whereDate('date', '>=', $this->from)
                    ->whereDate('date', '<=', $this->to)
                    ->where('authorization', 'approved');

                if ($this->cashBasis) {
                    $q->where('status', 'Paid');
                }
            })
            ->with('invoice:id,currency_id')
            ->get(['id', 'invoice_id', 'tax_id', 'subtotal', 'tax_amount', 'exchange_rate']);

        return $this->aggregate($rows, fn ($row) => $row->invoice->currency_id ?? null);
    }

    /**
     * Input tax: tax paid on purchases, from bill expense line items on
     * approved bills in the date range. Cash basis restricts to paid bills.
     *
     * @return array{0: float, 1: float, 2: array<int, array{taxable: float, tax: float}>}
     */
    public function inputTax(): array
    {
        $rows = BillExpense::whereHas('bill', function ($q) {
                $q->whereDate('bill_date', '>=', $this->from)
                    ->whereDate('bill_date', '<=', $this->to)
                    ->where('authorization', 'approved');

                if ($this->cashBasis) {
                    $q->where('status', 'Paid');
                }
            })
            ->with('bill:id,currency_id')
            ->get(['id', 'bill_id', 'tax_id', 'subtotal', 'tax_amount', 'exchange_rate']);

        return $this->aggregate($rows, fn ($row) => $row->bill->currency_id ?? null);
    }

    private function aggregate($rows, callable $currencyResolver): array
    {
        $byTax = [];
        $taxableTotal = 0.0;
        $taxTotal = 0.0;

        foreach ($rows as $row) {
            $currencyId = $currencyResolver($row);
            $taxable = $this->reportingAmount($currencyId, $row->subtotal, $row->exchange_rate);
            $tax = $this->reportingAmount($currencyId, $row->tax_amount, $row->exchange_rate);

            $taxId = $row->tax_id ?? 0;
            $byTax[$taxId] ??= ['taxable' => 0.0, 'tax' => 0.0];
            $byTax[$taxId]['taxable'] += $taxable;
            $byTax[$taxId]['tax'] += $tax;

            $taxableTotal += $taxable;
            $taxTotal += $tax;
        }

        return [$taxableTotal, $taxTotal, $byTax];
    }

    /**
     * Resolve tax_id => display label ("Value Added Tax 15.5%", or
     * "No Tax" for line items with no tax assigned), for the given set of
     * tax ids that actually showed up in the aggregated data.
     *
     * @return array<int, string>
     */
    public function taxLabels(array $taxIds): array
    {
        $labels = Tax::whereIn('id', array_filter($taxIds))->pluck('name', 'id')->all();
        $labels[0] = 'No Tax';

        return $labels;
    }
}
