<?php

namespace App\Http\Livewire\Concerns;

use App\Models\Account;
use App\Models\Payment;
use App\Services\Accounting\CashTransactionAccounts;
use App\Services\Accounting\PaymentJournalService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Manual Deposit / Withdrawal against a cash/bank account (Transactions and
 * Chart of Accounts screens). The component provides account_id,
 * contra_account_id, amount, date, exchange_rate, customer(_id),
 * vendor(_id) and receipt.
 */
trait RecordsCashTransactions
{
    // Picking Customer Deposits opens the customer field it needs
    public function updatedContraAccountId($id)
    {
        if (app(CashTransactionAccounts::class)->requiresCustomer($id)) {
            $this->customer = true;
        }
    }

    protected function validateCashTransaction(string $direction): void
    {
        $accounts = app(CashTransactionAccounts::class);

        $this->validate([
            'date'              => 'required|date',
            'account_id'        => 'required|exists:accounts,id',
            'contra_account_id' => [
                'required',
                'different:account_id',
                function ($attribute, $value, $fail) use ($accounts, $direction) {
                    if (!$accounts->allows($direction, $value)) {
                        $fail("This account cannot be used for a {$direction}. It is posted by its own documents or belongs to the other direction.");
                    }
                },
            ],
            'amount'            => 'required|numeric|gt:0',
            'exchange_rate'     => 'nullable|numeric|gt:0',
            'customer_id'       => ($this->customer == TRUE || $accounts->requiresCustomer($this->contra_account_id)) ? 'required' : 'nullable',
            'vendor_id'         => $this->vendor == TRUE ? 'required' : 'nullable',
            'receipt'           => 'nullable|file',
        ], [
            'contra_account_id.required'  => 'Select a category.',
            'contra_account_id.different' => 'The category cannot be the same account as the cash/bank account.',
            'customer_id.required'        => 'Select the customer.',
        ]);
    }

    /**
     * Saves the payment, posts its journal and moves the account balance as
     * one unit. The PaymentObserver posts on create but only logs a failure,
     * so post() runs again here: it hands back the observer's entry when that
     * worked and re-throws the real error when it didn't, rolling it all back.
     */
    protected function saveCashTransaction(Payment $payment, Account $account, float $balanceChange, string $label): bool
    {
        try {
            DB::transaction(function () use ($payment, $account, $balanceChange) {
                $payment->save();

                app(PaymentJournalService::class)->post($payment);

                $account->balance = (float) $account->balance + $balanceChange;
                $account->update();
            });
        } catch (\Throwable $e) {
            Log::error("{$label} transaction not saved - ledger posting failed: " . $e->getMessage());
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"{$label} not saved - it could not be posted to the ledger: " . $e->getMessage()
            ]);
            return false;
        }

        return true;
    }
}
