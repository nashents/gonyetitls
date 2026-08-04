<?php

namespace App\Http\Livewire\BankReconciliations;

use App\Models\BankAccount;
use App\Models\BankStatementLine;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    public $company;

    public function mount()
    {
        $this->company = Auth::user()->employee?->company ?? Auth::user()->company ?? null;
    }

    public function render()
    {
        $bankAccounts = BankAccount::where('company_id', $this->company->id ?? 0)
            ->with(['account', 'currency'])
            ->orderBy('name')
            ->get()
            ->map(function ($bankAccount) {
                $bankAccount->last_reconciliation = $bankAccount->bank_reconciliations()
                    ->orderByDesc('period_end')
                    ->first();
                $bankAccount->unmatched_count = $bankAccount->account
                    ? BankStatementLine::where('bank_account_id', $bankAccount->id)
                        ->where('status', 'unmatched')
                        ->count()
                    : 0;

                return $bankAccount;
            });

        return view('livewire.bank-reconciliations.index', compact('bankAccounts'));
    }
}
