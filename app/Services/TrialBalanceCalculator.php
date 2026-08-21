<?php

namespace App\Services;

use Illuminate\Support\Collection;
use App\Models\Company;
use App\Models\JournalEntryLine;
use App\Services\Accounting\JournalEntryPairFilter;

/**
 * Trial Balance: per-account debit/credit totals for a date range, built
 * on the posted ledger. Extracted from
 * App\Http\Livewire\Reports\TrialBalance so the live report and the new
 * Print/PDF export share one implementation instead of two.
 */
class TrialBalanceCalculator
{
    public function __construct(
        private int $companyId,
        private string $dateFrom,
        private string $dateTo,
        private string $search = '',
        private bool $hideZeroBalances = true
    ) {
    }

    /**
     * Every line is summed in the company's reporting currency: its native
     * debit/credit when it was posted directly in that currency, otherwise
     * the exchange_debit/exchange_credit already converted at posting time
     * (see InvoiceJournalService et al.) - mirrors the same rule
     * IncomeStatementCalculator::reportingAmount() applies for the P&L.
     */
    private function reportingCurrencyId(): ?int
    {
        return Company::find($this->companyId)?->currency_id;
    }

    public function lines(): Collection
    {
        $base = (int) $this->reportingCurrencyId();

        // Reversed originals whose reversal also falls in this date range
        // are fully-cancelling noise for gross Debit/Credit totals (net
        // balance is unaffected either way) - see JournalEntryPairFilter.
        $cancelledIds = JournalEntryPairFilter::cancelledEntryIds($this->companyId, $this->dateFrom, $this->dateTo);

        $lines = JournalEntryLine::query()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->join('accounts', 'accounts.id', '=', 'journal_entry_lines.account_id')
            ->join('account_types', 'account_types.id', '=', 'accounts.account_type_id')
            ->join('account_type_groups', 'account_type_groups.id', '=', 'account_types.account_type_group_id')
            ->where('journal_entries.company_id', $this->companyId)
            ->where('journal_entries.status', '!=', 'draft')
            ->whereBetween('journal_entries.date', [$this->dateFrom, $this->dateTo])
            ->when(!empty($cancelledIds), fn ($q) => $q->whereNotIn('journal_entries.id', $cancelledIds))
            ->when($this->search, fn ($q) => $q->where('accounts.name', 'like', "%{$this->search}%"))
            ->selectRaw("
                accounts.id as account_id,
                accounts.name as account_name,
                account_types.name as account_type_name,
                account_type_groups.name as account_type_group_name,
                account_type_groups.id as account_type_group_id,
                SUM(CASE WHEN journal_entry_lines.currency_id IS NULL OR journal_entry_lines.currency_id = {$base} THEN journal_entry_lines.debit ELSE journal_entry_lines.exchange_debit END) as total_debit,
                SUM(CASE WHEN journal_entry_lines.currency_id IS NULL OR journal_entry_lines.currency_id = {$base} THEN journal_entry_lines.credit ELSE journal_entry_lines.exchange_credit END) as total_credit,
                SUM(CASE WHEN journal_entry_lines.currency_id IS NULL OR journal_entry_lines.currency_id = {$base} THEN journal_entry_lines.debit ELSE journal_entry_lines.exchange_debit END)
                    - SUM(CASE WHEN journal_entry_lines.currency_id IS NULL OR journal_entry_lines.currency_id = {$base} THEN journal_entry_lines.credit ELSE journal_entry_lines.exchange_credit END) as balance
            ")
            ->groupBy(
                'accounts.id',
                'accounts.name',
                'account_types.name',
                'account_type_groups.name',
                'account_type_groups.id',
            )
            ->get();

        if ($this->hideZeroBalances) {
            $lines = $lines->filter(fn ($l) => abs($l->balance) > 0.001);
        }

        return $lines;
    }

    public function totals(Collection $lines): array
    {
        return [
            'debit' => $lines->sum('total_debit'),
            'credit' => $lines->sum('total_credit'),
        ];
    }

    public function isBalanced(array $totals): bool
    {
        return abs($totals['debit'] - $totals['credit']) < 0.01;
    }
}
