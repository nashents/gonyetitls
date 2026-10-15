<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Second pass of 2026_10_14_000003, which resolved the expense through
 * bills.trip_expense_id and only the first "Trip Expense" account, so it
 * missed bills. See RepointTripExpenseBillAccounts - safe to re-run by hand:
 * php artisan bills:repoint-trip-expense-accounts --bill=TEB00350 --dry-run
 */
class RepointTripExpenseBillLinesByLineExpense extends Migration
{
    public function up()
    {
        Artisan::call('bills:repoint-trip-expense-accounts', ['--force' => true]);
        echo Artisan::output();
    }

    public function down()
    {
        // Data repair - nothing to roll back.
    }
}
