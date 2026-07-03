<?php

namespace App\Services;

use App\Models\Invoice;

/**
 * Income grouped by customer, for the Income by Customer report. Mirrors
 * IncomeStatementCalculator::incomeAmounts() exactly, just grouped by
 * customer_id instead of account_id.
 */
class CustomerIncomeCalculator
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
     * @return array{0: float, 1: array<int, float>} [total, [customer_id => amount]]
     */
    public function incomeByCustomer(): array
    {
        $rows = Invoice::whereDate('date', '>=', $this->from)
            ->whereDate('date', '<=', $this->to)
            ->where('authorization', 'approved')
            ->when($this->cashBasis, fn ($q) => $q->where('status', 'Paid'))
            ->get(['customer_id', 'currency_id', 'total', 'exchange_amount']);

        $byCustomer = [];
        $total = 0.0;

        foreach ($rows as $row) {
            $amount = $this->reportingAmount($row->currency_id, $row->total, $row->exchange_amount);
            $total += $amount;
            $customerId = $row->customer_id ?? 0;
            $byCustomer[$customerId] = ($byCustomer[$customerId] ?? 0) + $amount;
        }

        return [$total, $byCustomer];
    }
}
