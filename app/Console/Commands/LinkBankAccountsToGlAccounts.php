<?php

namespace App\Console\Commands;

use App\Models\BankAccount;
use App\Services\Accounting\BankAccountGlLinkService;
use Illuminate\Console\Command;

class LinkBankAccountsToGlAccounts extends Command
{
    /**
     * php artisan bank-accounts:link-gl-accounts
     */
    protected $signature = 'bank-accounts:link-gl-accounts';

    protected $description = 'Create the missing "Cash & Bank" Chart of Accounts entry for every existing company BankAccount that does not already have one linked';

    public function handle(BankAccountGlLinkService $linker)
    {
        // Only company-owned bank accounts get a GL entry - employee/driver payroll
        // bank details (employee_id set) are a different, unrelated use of this table.
        $bankAccounts = BankAccount::doesntHave('account')->whereNull('employee_id')->get();

        if ($bankAccounts->isEmpty()) {
            $this->info('Every bank account already has a linked GL account.');

            return self::SUCCESS;
        }

        foreach ($bankAccounts as $bankAccount) {
            $account = $linker->ensureLinkedAccount($bankAccount);
            $this->line("Linked bank account #{$bankAccount->id} ({$bankAccount->name}) -> account #{$account->id} ({$account->code})");
        }

        $this->info("Linked {$bankAccounts->count()} bank account(s) to new GL accounts.");

        return self::SUCCESS;
    }
}
