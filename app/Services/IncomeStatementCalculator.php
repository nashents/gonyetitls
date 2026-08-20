<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\BillExpense;
use App\Models\JournalEntryLine;

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
        private $defaultCurrencyId,
        private ?int $companyId = null
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
     * Income grouped by account, for approved invoices in the date range,
     * optionally restricted to a specific set of account ids (used to split
     * the main "Income" section from an "Other Income" section covering
     * every other Income-group account type - Discount, Other Income, Gain
     * on Foreign Exchange, Uncategorized Income). Cash basis restricts to
     * paid invoices, matching the "Cash Basis (Paid)" option shown in the
     * report type dropdown.
     *
     * @return array{0: float, 1: array<int, float>}
     */
    public function incomeAmounts(?array $accountIds = null): array
    {
        $rows = Invoice::whereDate('date', '>=', $this->from)
            ->whereDate('date', '<=', $this->to)
            ->where('authorization', 'approved')
            ->when($this->cashBasis, fn ($q) => $q->where('status', 'Paid'))
            ->when($accountIds !== null, fn ($q) => $q->whereIn('account_id', $accountIds))
            ->get(['account_id', 'currency_id', 'total', 'exchange_amount']);

        $byAccount = [];
        $total = 0.0;

        foreach ($rows as $row) {
            $amount = $this->reportingAmount($row->currency_id, $row->total, $row->exchange_amount);
            $total += $amount;
            $byAccount[$row->account_id] = ($byAccount[$row->account_id] ?? 0) + $amount;
        }

        [$ledgerTotal, $ledgerByAccount] = $this->ledgerOnlyIncomeAmounts($accountIds);
        $total += $ledgerTotal;
        foreach ($ledgerByAccount as $accountId => $amount) {
            $byAccount[$accountId] = ($byAccount[$accountId] ?? 0) + $amount;
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

        [$ledgerTotal, $ledgerByAccount] = $this->ledgerOnlyExpenseAmounts($accountIds);
        $total += $ledgerTotal;
        foreach ($ledgerByAccount as $accountId => $amount) {
            $byAccount[$accountId] = ($byAccount[$accountId] ?? 0) + $amount;
        }

        return [$total, $byAccount];
    }

    /**
     * Revenue/expense that only ever reaches the general ledger directly -
     * payroll, realized FX gain/loss, credit/debit notes, manual journal
     * entries - none of which have an Invoice or BillExpense row behind
     * them, so incomeAmounts()/expenseAmounts() above would silently drop
     * them without this. Scoped to entries with no invoice_id/bill_id so
     * Invoice- and Bill-sourced activity (already counted above) is never
     * double-counted. Deliberately NOT scoped by horse/trailer/vehicle/
     * driver/transporter dimension tags - the Horses/Transporters P&L
     * reports (app/Http/Livewire/Horses/ProfitLoss, .../Transporters/
     * ProfitLoss) are built entirely from Trip/BillExpense data and never
     * call this calculator, so nothing here reaches them regardless.
     *
     * Always included regardless of the cash/accrual toggle - a ledger
     * posting has no "unpaid" state of its own to filter on (unlike an
     * Invoice/Bill), so there's nothing meaningful for cash-basis to
     * restrict here.
     */
    private function ledgerOnlyIncomeAmounts(?array $accountIds = null): array
    {
        if (!$this->companyId) {
            return [0.0, []];
        }

        $rows = JournalEntryLine::query()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->join('accounts', 'accounts.id', '=', 'journal_entry_lines.account_id')
            ->join('account_types', 'account_types.id', '=', 'accounts.account_type_id')
            ->join('account_type_groups', 'account_type_groups.id', '=', 'account_types.account_type_group_id')
            ->where('journal_entries.company_id', $this->companyId)
            ->where('journal_entries.status', '!=', 'draft')
            ->where('account_type_groups.name', 'Income')
            ->whereNull('journal_entries.invoice_id')
            ->when($accountIds !== null, fn ($q) => $q->whereIn('journal_entry_lines.account_id', $accountIds))
            ->whereDate('journal_entries.date', '>=', $this->from)
            ->whereDate('journal_entries.date', '<=', $this->to)
            ->get([
                'journal_entry_lines.account_id',
                'journal_entry_lines.currency_id',
                'journal_entry_lines.debit',
                'journal_entry_lines.credit',
                'journal_entry_lines.exchange_debit',
                'journal_entry_lines.exchange_credit',
            ]);

        $byAccount = [];
        $total = 0.0;

        foreach ($rows as $row) {
            $isBaseCurrency = is_null($row->currency_id) || (int) $row->currency_id === (int) $this->defaultCurrencyId;
            $debit = $isBaseCurrency ? (float) $row->debit : (float) $row->exchange_debit;
            $credit = $isBaseCurrency ? (float) $row->credit : (float) $row->exchange_credit;

            $amount = $credit - $debit; // Income accounts are credit-normal
            $total += $amount;
            $byAccount[$row->account_id] = ($byAccount[$row->account_id] ?? 0) + $amount;
        }

        return [$total, $byAccount];
    }

    /** Same as ledgerOnlyIncomeAmounts() but for the expense side - see that method's docblock. */
    private function ledgerOnlyExpenseAmounts(array $accountIds): array
    {
        if (!$this->companyId) {
            return [0.0, []];
        }

        $rows = JournalEntryLine::query()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->whereIn('journal_entry_lines.account_id', $accountIds)
            ->where('journal_entries.company_id', $this->companyId)
            ->where('journal_entries.status', '!=', 'draft')
            ->whereNull('journal_entries.bill_id')
            ->whereDate('journal_entries.date', '>=', $this->from)
            ->whereDate('journal_entries.date', '<=', $this->to)
            ->get([
                'journal_entry_lines.account_id',
                'journal_entry_lines.currency_id',
                'journal_entry_lines.debit',
                'journal_entry_lines.credit',
                'journal_entry_lines.exchange_debit',
                'journal_entry_lines.exchange_credit',
            ]);

        $byAccount = [];
        $total = 0.0;

        foreach ($rows as $row) {
            $isBaseCurrency = is_null($row->currency_id) || (int) $row->currency_id === (int) $this->defaultCurrencyId;
            $debit = $isBaseCurrency ? (float) $row->debit : (float) $row->exchange_debit;
            $credit = $isBaseCurrency ? (float) $row->credit : (float) $row->exchange_credit;

            $amount = $debit - $credit; // Expense accounts are debit-normal
            $total += $amount;
            $byAccount[$row->account_id] = ($byAccount[$row->account_id] ?? 0) + $amount;
        }

        return [$total, $byAccount];
    }
}
