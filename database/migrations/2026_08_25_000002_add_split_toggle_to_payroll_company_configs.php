<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSplitToggleToPayrollCompanyConfigs extends Migration
{
    public function up()
    {
        Schema::table('payroll_company_configs', function (Blueprint $table) {
            // Off by default (lean): every payroll line posts to the *_admin
            // expense accounts regardless of employee type. On: driver lines
            // also post to the *_drivers (COGS) accounts, per PayrollJournalService.
            $table->boolean('split_payroll_expenses_by_employee_type')->default(false)->after('gl_wages_expense_account_drivers');
        });
    }

    public function down()
    {
        Schema::table('payroll_company_configs', function (Blueprint $table) {
            $table->dropColumn('split_payroll_expenses_by_employee_type');
        });
    }
}
