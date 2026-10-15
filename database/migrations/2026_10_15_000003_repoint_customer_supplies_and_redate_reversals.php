<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Re-runs the trip-bill account repair now that it also covers customer-
 * funded trip expense lines (CustomerFuelSupply, hardcoded to "Trip
 * Expense" too), then dates every correction reversal since the repair like
 * the entry it cancels - so the Trial Balance drops each reversed pair
 * instead of leaving the old debit in its original month. Both idempotent.
 * To see what is still on the account: php artisan ledger:explain-account "Trip Expense"
 */
class RepointCustomerSuppliesAndRedateReversals extends Migration
{
    public function up()
    {
        Artisan::call('bills:repoint-trip-expense-accounts', ['--force' => true]);
        echo Artisan::output();

        Artisan::call('journals:redate-correction-reversals', ['--since' => '2026-10-14']);
        echo Artisan::output();
    }

    public function down()
    {
        // Data repair - nothing to roll back.
    }
}
