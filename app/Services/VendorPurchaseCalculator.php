<?php

namespace App\Services;

use App\Models\Bill;

/**
 * Purchases grouped by vendor, for the Purchase by Vendors report. Mirrors
 * CustomerIncomeCalculator exactly - sums Bill.total (the whole bill, not
 * just specific expense accounts) grouped by vendor_id, consistent with
 * how Income by Customer sums Invoice.total grouped by customer_id.
 */
class VendorPurchaseCalculator
{
    public function __construct(
        private string $from,
        private string $to,
        private bool $cashBasis,
        private $defaultCurrencyId
    ) {
    }

    private function reportingAmount($currencyId, $nativeAmount, $exchangeAmount): float
    {
        $isBaseCurrency = is_null($currencyId) || (int) $currencyId === (int) $this->defaultCurrencyId;

        if ($isBaseCurrency) {
            return ReportFormatter::isNumericAmount($nativeAmount) ? (float) $nativeAmount : 0.0;
        }

        return ReportFormatter::isNumericAmount($exchangeAmount) ? (float) $exchangeAmount : 0.0;
    }

    /**
     * @return array{0: float, 1: array<int, float>} [total, [vendor_id => amount]]
     */
    public function purchasesByVendor(): array
    {
        $rows = Bill::whereDate('bill_date', '>=', $this->from)
            ->whereDate('bill_date', '<=', $this->to)
            ->where('authorization', 'approved')
            ->when($this->cashBasis, fn ($q) => $q->where('status', 'Paid'))
            ->get(['vendor_id', 'currency_id', 'total', 'exchange_amount']);

        $byVendor = [];
        $total = 0.0;

        foreach ($rows as $row) {
            $amount = $this->reportingAmount($row->currency_id, $row->total, $row->exchange_amount);
            $total += $amount;
            $vendorId = $row->vendor_id ?? 0;
            $byVendor[$vendorId] = ($byVendor[$vendorId] ?? 0) + $amount;
        }

        return [$total, $byVendor];
    }
}
