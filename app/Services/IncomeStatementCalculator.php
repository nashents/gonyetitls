<?php

namespace App\Services;

use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\Auth;

/**
 * Shared revenue/expense calculation for the Profit & Loss (Income
 * Statement) report, used by both the live Livewire report and the
 * PDF/Excel exports so they never drift apart.
 *
 * Built on the general ledger - the same journal lines, filters (company,
 * status != draft, date range) and reporting-currency conversion as the
 * Trial Balance, Account Transactions, Balance Sheet and Cash Flow - so an
 * account's figure here always equals its Trial Balance figure for the
 * same period. It used to be built from invoice/bill documents instead,
 * which disagreed with the ledger (COMDELTIC Jan-Oct 2026: Sales 80,863 vs
 * 202,048): invoice totals include VAT, used only the header account,
 * counted 0 for foreign invoices without exchange_amount, ignored the
 * Customer Advances deferral/release, credit notes against invoices,
 * customer-supplied fuel, etc.
 *
 * Cash basis ("Cash Basis (Paid)"): the same ledger lines, minus postings
 * of invoices/bills that aren't fully paid yet; postings with no invoice
 * or bill behind them (payroll, journals, FX, customer fuel) always count.
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
        $this->companyId = $companyId ?? (Auth::user()?->employee?->company_id ?? Auth::user()?->company_id);
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
     * Income grouped by account (credit-normal: credits - debits), optionally
     * restricted to a set of account ids (the main "Income" section vs
     * "Other Income").
     *
     * @return array{0: float, 1: array<int, float>}
     */
    public function incomeAmounts(?array $accountIds = null): array
    {
        return $this->ledgerAmounts($accountIds, creditNormal: true);
    }

    /**
     * Expenses (Cost of Goods Sold, Operating or Other Expenses, by the given
     * account ids) grouped by account (debit-normal: debits - credits).
     *
     * @return array{0: float, 1: array<int, float>}
     */
    public function expenseAmounts(array $accountIds): array
    {
        if (empty($accountIds)) {
            return [0.0, []];
        }

        return $this->ledgerAmounts($accountIds, creditNormal: false);
    }

    /** @return array{0: float, 1: array<int, float>} */
    private function ledgerAmounts(?array $accountIds, bool $creditNormal): array
    {
        if (! $this->companyId || ($accountIds !== null && empty($accountIds))) {
            return [0.0, []];
        }

        $base = (int) $this->defaultCurrencyId;
        $debit = "CASE WHEN journal_entry_lines.currency_id IS NULL OR journal_entry_lines.currency_id = {$base} THEN journal_entry_lines.debit ELSE journal_entry_lines.exchange_debit END";
        $credit = "CASE WHEN journal_entry_lines.currency_id IS NULL OR journal_entry_lines.currency_id = {$base} THEN journal_entry_lines.credit ELSE journal_entry_lines.exchange_credit END";
        $net = $creditNormal ? "SUM({$credit}) - SUM({$debit})" : "SUM({$debit}) - SUM({$credit})";

        $rows = JournalEntryLine::query()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('journal_entries.company_id', $this->companyId)
            ->where('journal_entries.status', '!=', 'draft')
            ->whereDate('journal_entries.date', '>=', $this->from)
            ->whereDate('journal_entries.date', '<=', $this->to)
            ->when($accountIds !== null, fn ($q) => $q->whereIn('journal_entry_lines.account_id', $accountIds))
            ->when($this->cashBasis, function ($q) {
                // Only fully paid invoices/bills; other postings always count.
                $q->where(fn ($x) => $x->whereNull('journal_entries.invoice_id')
                    ->orWhereExists(fn ($s) => $s->selectRaw('1')->from('invoices')
                        ->whereColumn('invoices.id', 'journal_entries.invoice_id')->where('invoices.status', 'Paid')))
                  ->where(fn ($x) => $x->whereNull('journal_entries.bill_id')
                    ->orWhereExists(fn ($s) => $s->selectRaw('1')->from('bills')
                        ->whereColumn('bills.id', 'journal_entries.bill_id')->where('bills.status', 'Paid')));
            })
            ->groupBy('journal_entry_lines.account_id')
            ->selectRaw("journal_entry_lines.account_id, {$net} as amount")
            ->get();

        $byAccount = [];
        foreach ($rows as $row) {
            $byAccount[$row->account_id] = (float) $row->amount;
        }

        return [array_sum($byAccount), $byAccount];
    }
}
