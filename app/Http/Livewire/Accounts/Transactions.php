<?php

namespace App\Http\Livewire\Accounts;

use Carbon\Carbon;
use App\Models\Account;
use Livewire\Component;
use App\Services\ReportFormatter;
use Illuminate\Support\Facades\Auth;
use App\Services\AccountBalanceCalculator;
use App\Services\AccountTransactionsCalculator;

/**
 * Transactions tab on the account view: every ledger line posted to this
 * account (any account type, not just cash/bank), with opening balance and
 * running balance. Same numbers as Reports > Account Transactions.
 *
 * Results are built in render() and never stored as public properties
 * (float round-trips break Livewire's payload checksum).
 */
class Transactions extends Component
{
    public $account_id;
    public $from;
    public $to;
    // "reporting" (company currency) or "account" (a bank/cash account's own currency)
    public $currency_mode = "reporting";

    public function mount($id)
    {
        $this->account_id = (int) $id;
        $this->from = Carbon::now()->firstOfYear()->format('Y-m-d');
        $this->to = Carbon::today()->format('Y-m-d');
    }

    public function fmt($value): string
    {
        return ReportFormatter::money($value);
    }

    public function render()
    {
        $user = Auth::user();
        $company = $user->employee?->company ?? $user->company ?? null;
        $currency = $company?->currency;
        $opening_balance = 0;
        $closing_balance = 0;
        $total_debit = 0;
        $total_credit = 0;
        $transactions = [];
        $own_currency = null;

        $account = Account::with('account_type.account_type_group', 'currency')->find($this->account_id);

        if ($account && $company && $this->from && $this->to) {
            $groupName = $account->account_type?->account_type_group?->name;
            $creditNormal = in_array($groupName, AccountBalanceCalculator::CREDIT_NORMAL_GROUPS, true);

            $own_currency = $account->currency_id && (int) $account->currency_id !== (int) $company->currency_id
                ? $account->currency : null;
            $inOwn = $own_currency && $this->currency_mode === "account";
            if ($inOwn) {
                $currency = $own_currency;
            }

            $calculator = new AccountTransactionsCalculator($company->id, $account->id, $this->from, $this->to,
                $creditNormal, $inOwn ? (int) $own_currency->id : null);

            $opening_balance = $calculator->openingBalance();
            // Newest first on screen; running balance is still computed oldest-first.
            $transactions = array_reverse($calculator->transactions($opening_balance));
            $total_debit = array_sum(array_column($transactions, 'debit'));
            $total_credit = array_sum(array_column($transactions, 'credit'));
            $closing_balance = $transactions ? $transactions[0]['running_balance'] : $opening_balance;
        }

        return view('livewire.accounts.transactions', [
            'currency' => $currency,
            'own_currency' => $own_currency,
            'opening_balance' => $opening_balance,
            'closing_balance' => $closing_balance,
            'total_debit' => $total_debit,
            'total_credit' => $total_credit,
            'transactions' => $transactions,
        ]);
    }
}
