<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\BillExpense;

/**
 * Shared revenue/expense calculation for the Profit & Loss (Income
 * Statement) report, used by both the live Livewire report and the
 * PDF export so the two never drift apart.
 */
class IncomeStatementCalculator
{
    public function __construct(
        private string $from,
        private string $to,
        private bool $cashBasis,
        private $defaultCurrencyId
    ) {
    }

    /** @deprecated use ReportFormatter::isNumericAmount() */
    public static function isNumericAmount($value): bool
    {
        return ReportFormatter::isNumericAmount($value);
    }

    /** @deprecated use ReportFormatter::money() */
    public static function money($value): string
    {
        return ReportFormatter::money($value);
    }

    /** @deprecated use ReportFormatter::percent() */
    public static function percent($value): string
    {
        return ReportFormatter::percent($value);
    }

    /** @deprecated use ReportFormatter::waveMoney() */
    public static function waveMoney($value, string $currencyCode = ''): string
    {
        return ReportFormatter::waveMoney($value, $currencyCode);
    }

    /**
     * Resolve the amount of a row in the reporting currency: the native
     * amount when the row is already in the default currency (including
     * rows with no currency recorded, which are assumed to be in the
     * default currency), otherwise the pre-converted exchange amount.
     */
    private function reportingAmount($currencyId, $nativeAmount, $exchangeAmount): float
    {
        $isBaseCurrency = is_null($currencyId) || (int) $currencyId === (int) $this->defaultCurrencyId;

        if ($isBaseCurrency) {
            return ReportFormatter::isNumericAmount($nativeAmount) ? (float) $nativeAmount : 0.0;
        }

        return ReportFormatter::isNumericAmount($exchangeAmount) ? (float) $exchangeAmount : 0.0;
    }

    /**
     * Income grouped by account, for approved invoices in the date range.
     * Cash basis restricts to paid invoices, matching the "Cash Basis
     * (Paid)" option shown in the report type dropdown.
     *
     * @return array{0: float, 1: array<int, float>}
     */
    public function incomeAmounts(): array
    {
        $rows = Invoice::whereDate('date', '>=', $this->from)
            ->whereDate('date', '<=', $this->to)
            ->where('authorization', 'approved')
            ->when($this->cashBasis, fn ($q) => $q->where('status', 'Paid'))
            ->get(['account_id', 'currency_id', 'total', 'exchange_amount']);

        $byAccount = [];
        $total = 0.0;

        foreach ($rows as $row) {
            $amount = $this->reportingAmount($row->currency_id, $row->total, $row->exchange_amount);
            $total += $amount;
            $byAccount[$row->account_id] = ($byAccount[$row->account_id] ?? 0) + $amount;
        }

        return [$total, $byAccount];
    }

    /**
     * Expenses (Cost of Goods Sold or Operating Expenses, depending on the
     * given account ids) grouped by account, for bill expenses posted to
     * approved bills in the date range. Cash basis restricts to paid bills.
     *
     * @return array{0: float, 1: array<int, float>}
     */
    public function expenseAmounts(array $accountIds): array
    {
        if (empty($accountIds)) {
            return [0.0, []];
        }

        $rows = BillExpense::whereIn('account_id', $accountIds)
            ->whereHas('bill', function ($q) {
                $q->whereDate('bill_date', '>=', $this->from)
                    ->whereDate('bill_date', '<=', $this->to)
                    ->where('authorization', 'approved');

                if ($this->cashBasis) {
                    $q->where('status', 'Paid');
                }
            })
            ->with('bill:id,currency_id')
            ->get(['id', 'account_id', 'bill_id', 'subtotal_incl', 'exchange_amount']);

        $byAccount = [];
        $total = 0.0;

        foreach ($rows as $row) {
            $amount = $this->reportingAmount($row->bill->currency_id ?? null, $row->subtotal_incl, $row->exchange_amount);
            $total += $amount;
            $byAccount[$row->account_id] = ($byAccount[$row->account_id] ?? 0) + $amount;
        }

        return [$total, $byAccount];
    }
}
