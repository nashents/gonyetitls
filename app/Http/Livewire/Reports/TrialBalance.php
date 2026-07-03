<?php

namespace App\Http\Livewire\Reports;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Services\TrialBalanceCalculator;

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

    /**
     * Bound to the filter form's wire:submit so pressing Enter doesn't
     * error out looking for a missing action; the bound properties
     * already trigger a recalculation on change.
     */
    public function generateStatement()
    {
        //
    }

    private function calculator(): TrialBalanceCalculator
    {
        $user = Auth::user();
        $company = $user->employee?->company ?? $user->company ?? null;

        return new TrialBalanceCalculator($company->id ?? 0, $this->date_from, $this->date_to, $this->search, $this->hide_zero_balances);
    }

    public function getTrialBalanceProperty(): \Illuminate\Support\Collection
    {
        return $this->calculator()->lines();
    }

    public function getTotalsProperty(): array
    {
        return $this->calculator()->totals($this->trialBalance);
    }

    public function getIsBalancedProperty(): bool
    {
        return $this->calculator()->isBalanced($this->totals);
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
