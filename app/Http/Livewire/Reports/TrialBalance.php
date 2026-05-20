<?php

namespace App\Http\Livewire\Reports;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TrialBalance extends Component
{
    public string $date_from = '';
    public string $date_to = '';
    public string $search = '';
    public bool $hide_zero_balances = true;

    public function mount(): void
    {
        $this->date_from = now()->startOfYear()->toDateString();
        $this->date_to   = now()->toDateString();
    }

    public function getTrialBalanceProperty(): \Illuminate\Support\Collection
    {
        $companyId = Auth::user()->employee->company_id;

        $lines = JournalEntryLine::query()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->join('accounts', 'accounts.id', '=', 'journal_entry_lines.account_id')
            ->join('account_types', 'account_types.id', '=', 'accounts.account_type_id')
            ->join('account_type_groups', 'account_type_groups.id', '=', 'account_types.account_type_group_id')
            ->where('journal_entries.company_id', $companyId)
            ->where('journal_entries.status', 'posted')
            ->whereBetween('journal_entries.date', [$this->date_from, $this->date_to])
            ->when($this->search, fn($q) => $q->where('accounts.name', 'like', "%{$this->search}%"))
            ->selectRaw('
                accounts.id as account_id,
                accounts.name as account_name,
                account_types.name as account_type_name,
                account_type_groups.name as account_type_group_name,
                account_type_groups.id as account_type_group_id,
                SUM(journal_entry_lines.debit) as total_debit,
                SUM(journal_entry_lines.credit) as total_credit,
                SUM(journal_entry_lines.debit) - SUM(journal_entry_lines.credit) as balance
            ')
            ->groupBy(
                'accounts.id',
                'accounts.name',
                'account_types.name',
                'account_type_groups.name',
                'account_type_groups.id',
            )
            ->get();

        if ($this->hide_zero_balances) {
            $lines = $lines->filter(fn($l) => abs($l->balance) > 0.001);
        }

        return $lines;
    }

    public function getTotalsProperty(): array
    {
        return [
            'debit'  => $this->trialBalance->sum('total_debit'),
            'credit' => $this->trialBalance->sum('total_credit'),
        ];
    }

    public function getIsBalancedProperty(): bool
    {
        return abs($this->totals['debit'] - $this->totals['credit']) < 0.01;
    }

    public function render()
    {
        return view('livewire.reports.trial-balance', [
            'trialBalance'  => $this->trialBalance,
            'totals'        => $this->totals,
            'isBalanced'    => $this->isBalanced,
            'groupedLines'  => $this->trialBalance->groupBy('account_type_group_name'),
        ]);
    }
}