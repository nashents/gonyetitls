<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Shared-premium deductions: the company share is split Admin / Drivers like
 * NSSA / NEC / Pension when Payroll Config splits payroll expenses by employee
 * type. expense_account_id = admin (and the only account when the split is
 * off); expense_account_drivers_id = drivers (blank = same as admin).
 */
class AddDriversExpenseAccountToDeductions extends Migration
{
    public function up()
    {
        Schema::table('deductions', function (Blueprint $table) {
            $table->bigInteger('expense_account_drivers_id')->unsigned()->nullable()->after('expense_account_id');
        });
    }

    public function down()
    {
        Schema::table('deductions', fn (Blueprint $t) => $t->dropColumn('expense_account_drivers_id'));
    }
}
