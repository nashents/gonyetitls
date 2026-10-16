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

    // Report results (balances, transaction rows, currency) are built in
    // render() and passed to the view, never stored as public properties:
    // whole-number floats like 100.0 come back from the browser as 100,
    // which breaks Livewire's payload checksum (CorruptComponentPayloadException)
    // on the next request, and the rows bloat every round-trip.

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
        $default_currency = $company?->currency;
        $opening_balance = 0;
        $closing_balance = 0;
        $total_debit = 0;
        $total_credit = 0;
        $transactions = [];

        if ($this->account_id && isset($this->from) && isset($this->to) && $company) {
            $account = Account::with('account_type.account_type_group')->find($this->account_id);
            $groupName = $account?->account_type?->account_type_group?->name;
            $creditNormal = in_array($groupName, AccountBalanceCalculator::CREDIT_NORMAL_GROUPS, true);

            $calculator = new AccountTransactionsCalculator($company->id, $this->account_id, $this->from, $this->to, $creditNormal);

            $opening_balance = $calculator->openingBalance();
            $transactions = $calculator->transactions($opening_balance);

            $total_debit = array_sum(array_column($transactions, 'debit'));
            $total_credit = array_sum(array_column($transactions, 'credit'));
            $closing_balance = $transactions
                ? end($transactions)['running_balance']
                : $opening_balance;
        }

        return view('livewire.reports.account-transactions.index', [
            'accounts' => $this->accounts,
            'default_currency' => $default_currency,
            'opening_balance' => $opening_balance,
            'closing_balance' => $closing_balance,
            'total_debit' => $total_debit,
            'total_credit' => $total_credit,
            'transactions' => $transactions,
        ]);
    }
}
