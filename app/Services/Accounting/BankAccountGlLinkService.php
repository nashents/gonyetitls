<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\BankAccount;

class BankAccountGlLinkService
{
    /**
     * Every BankAccount (the physical bank record) needs exactly one Chart of
     * Accounts entry under the "Cash & Bank" account type so payments and bank
     * reconciliation can post/match against it. Returns the existing link if
     * one is already there.
     */
    public function ensureLinkedAccount(BankAccount $bankAccount): Account
    {
        if ($bankAccount->employee_id) {
            throw new \RuntimeException('This bank account belongs to an employee/driver payroll record, not a company cash & bank account - it must not get a Chart of Accounts entry.');
        }

        $existing = Account::where('bank_account_id', $bankAccount->id)->first();
        if ($existing) {
            return $existing;
        }

        $cashBankType = AccountType::where('name', 'Cash & Bank')->first();
        if (!$cashBankType) {
            throw new \RuntimeException('The "Cash & Bank" account type is not configured.');
        }

        $account = new Account();
        $account->bank_account_id = $bankAccount->id;
        $account->account_type_id = $cashBankType->id;
        $account->account_type_group_id = $cashBankType->account_type_group_id;
        $account->currency_id = $bankAccount->currency_id;
        $account->account_number = $bankAccount->account_number;
        $account->name = $this->displayName($bankAccount);
        $account->code = $this->nextCode();
        $account->balance = 0;
        $account->save();

        return $account;
    }

    /**
     * Keeps the linked GL account's display name/currency in sync when the
     * bank account itself is edited.
     */
    public function syncLinkedAccount(BankAccount $bankAccount): void
    {
        $account = $this->ensureLinkedAccount($bankAccount);

        $account->name = $this->displayName($bankAccount);
        $account->account_number = $bankAccount->account_number;
        $account->currency_id = $bankAccount->currency_id;
        $account->save();
    }

    /** "FBC - Fahrenheit Pvt Ltd", falling back to whichever of the two is set. */
    protected function displayName(BankAccount $bankAccount): string
    {
        if ($bankAccount->name && $bankAccount->account_name) {
            return "{$bankAccount->name} - {$bankAccount->account_name}";
        }

        return $bankAccount->account_name ?: ($bankAccount->name ?: 'Bank Account');
    }

    protected function nextCode(): string
    {
        $last = Account::where('code', 'like', 'BANK-%')->orderByDesc('id')->value('code');
        $next = $last ? ((int) substr($last, 5)) + 1 : 1;
        return 'BANK-' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
