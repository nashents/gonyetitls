<?php

namespace App\Http\Livewire\Reports\AccountTransactions;

use Carbon\Carbon;
use App\Models\Account;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Services\ReportFormatter;
use App\Services\AccountBalanceCalculator;
use App\Services\AccountTransactionsCalculator;

class Index extends Component
{
    public $account_id;
    public $from;
    public $to;
    public $details;
    public $summary = "summary";

    public $default_currency;
    public $default_currency_id;

    public $opening_balance = 0;
    public $closing_balance = 0;
    public $total_debit = 0;
    public $total_credit = 0;
    public $transactions = [];

    public function mount()
    {
        $this->to = Carbon::today()->format('Y-m-d');
        $this->from = Carbon::now()->firstOfYear()->format('Y-m-d');

        $this->account_id = $this->accounts->first()->id ?? null;
    }

    /**
     * Computed rather than a stored public property: a plain array of
     * Eloquent models in a public property doesn't survive Livewire's
     * dehydrate/rehydrate cycle between requests (unlike a single Model or
     * a genuine Collection) - it comes back as plain arrays, breaking
     * `$account->id` in the view on every request after the first.
     */
    public function getAccountsProperty()
    {
        return Account::orderBy('name')->get(['id', 'name']);
    }

    public function set_report($value)
    {
        $this->summary = null;
        $this->details = null;

        if ($value == "details") {
            $this->details = 'details';
        } elseif ($value == "summary") {
            $this->summary = 'summary';
        }
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

    public function viewMode(): string
    {
        return $this->details ? 'details' : 'summary';
    }

    public function fmt($value): string
    {
        return ReportFormatter::money($value);
    }

    public function render()
    {
        $user = Auth::user();
        $company = $user->employee?->company ?? $user->company ?? null;
        $this->default_currency = $company?->currency;
        $this->default_currency_id = $company?->currency_id;

        if ($this->account_id && isset($this->from) && isset($this->to) && $company) {
            $account = Account::with('account_type.account_type_group')->find($this->account_id);
            $groupName = $account?->account_type?->account_type_group?->name;
            $creditNormal = in_array($groupName, AccountBalanceCalculator::CREDIT_NORMAL_GROUPS, true);

            $calculator = new AccountTransactionsCalculator($company->id, $this->account_id, $this->from, $this->to, $creditNormal);

            $this->opening_balance = $calculator->openingBalance();
            $this->transactions = $calculator->transactions($this->opening_balance);

            $this->total_debit = array_sum(array_column($this->transactions, 'debit'));
            $this->total_credit = array_sum(array_column($this->transactions, 'credit'));
            $this->closing_balance = $this->transactions
                ? end($this->transactions)['running_balance']
                : $this->opening_balance;
        }

        return view('livewire.reports.account-transactions.index', [
            'accounts' => $this->accounts,
        ]);
    }
}
