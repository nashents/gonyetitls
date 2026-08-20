<?php

namespace App\Services;

use App\Models\Company;
use App\Models\JournalEntryLine;

/**
 * Balance Sheet, built from the same posted double-entry ledger as the
 * Trial Balance and Cash Flow Statement. Because every journal entry has
 * equal debits and credits, Assets - Liabilities - Equity always equals
 * cumulative (Income - Expenses) since inception - this is a mathematical
 * identity of double-entry bookkeeping, not an approximation - so folding
 * that figure in as "Net Income" under Equity is what makes the sheet
 * balance, exactly as it would in any accounting package that doesn't
 * run formal period-closing entries.
 */
class BalanceSheetCalculator
{
    public function __construct(
        private int $companyId,
        private string $asOfDate
    ) {
    }

    /** See TrialBalanceCalculator::reportingCurrencyId() for the rule this applies. */
    private function reportingCurrencyId(): ?int
    {
        return Company::find($this->companyId)?->currency_id;
    }

    private function baseQuery()
    {
        return JournalEntryLine::query()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->join('accounts', 'accounts.id', '=', 'journal_entry_lines.account_id')
            ->join('account_types', 'account_types.id', '=', 'accounts.account_type_id')
            ->join('account_type_groups', 'account_type_groups.id', '=', 'account_types.account_type_group_id')
            ->where('journal_entries.company_id', $this->companyId)
            ->where('journal_entries.status', '!=', 'draft')
            ->where('journal_entries.date', '<=', $this->asOfDate);
    }

    /**
     * Per-account balances for every account under the given
     * AccountTypeGroup, as of the report date. Assets are debit-normal
     * (debit - credit); Liabilities and Equity are credit-normal, so
     * $creditNormal flips the sign to display them as positive balances.
     *
     * @return array{0: float, 1: array<int, array{label: string, amount: float}>}
     */
    public function groupBalances(string $groupName, bool $creditNormal = false): array
    {
        $base = (int) $this->reportingCurrencyId();

        $rows = $this->baseQuery()
            ->where('account_type_groups.name', $groupName)
            ->selectRaw("
                accounts.id as account_id,
                accounts.name as account_name,
                SUM(CASE WHEN journal_entry_lines.currency_id IS NULL OR journal_entry_lines.currency_id = {$base} THEN journal_entry_lines.debit ELSE journal_entry_lines.exchange_debit END)
                    - SUM(CASE WHEN journal_entry_lines.currency_id IS NULL OR journal_entry_lines.currency_id = {$base} THEN journal_entry_lines.credit ELSE journal_entry_lines.exchange_credit END) as raw_balance
            ")
            ->groupBy('accounts.id', 'accounts.name')
            ->get();

        $items = [];
        $total = 0.0;

        foreach ($rows as $row) {
            $balance = $creditNormal ? -1 * (float) $row->raw_balance : (float) $row->raw_balance;

            if (abs($balance) < 0.005) {
                continue;
            }

            $items[] = ['label' => $row->account_name, 'amount' => $balance];
            $total += $balance;
        }

        return [$total, $items];
    }

    /**
     * Cumulative net income (Income minus Expenses) since inception up to
     * the report date - the figure that reconciles Assets against
     * Liabilities + Equity when the books have never been formally closed.
     */
    public function netIncomeToDate(): float
    {
        [$incomeTotal] = $this->groupBalances('Income', creditNormal: true);
        [$expenseTotal] = $this->groupBalances('Expenses', creditNormal: false);

        return $incomeTotal - $expenseTotal;
    }
}
