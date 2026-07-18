<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRemainingGlAccountsToPayrollCompanyConfigsTable extends Migration
{
    public function up()
    {
        Schema::table('payroll_company_configs', function (Blueprint $table) {
            // Employer-side statutory contribution expense accounts (DR lines)
            $table->string('gl_nssa_employer_expense_account')->nullable()->after('gl_wages_expense_account');
            $table->string('gl_nec_employer_expense_account')->nullable()->after('gl_nssa_employer_expense_account');
            $table->string('gl_pension_employer_expense_account')->nullable()->after('gl_nec_employer_expense_account');

            // gl_nssa_liability_account (existing) holds the NSSA employer contribution payable;
            // this adds the employee-withheld counterpart so both CR lines are configurable.
            $table->string('gl_nssa_employee_liability_account')->nullable()->after('gl_nssa_liability_account');

            $table->string('gl_aids_levy_liability_account')->nullable()->after('gl_paye_liability_account');
            $table->string('gl_payroll_suspense_account')->nullable()->after('gl_nec_liability_account');
            $table->string('gl_wages_payable_account')->nullable()->after('gl_payroll_suspense_account');
        });
    }

    public function down()
    {
        Schema::table('payroll_company_configs', function (Blueprint $table) {
            $table->dropColumn([
                'gl_nssa_employer_expense_account',
                'gl_nec_employer_expense_account',
                'gl_pension_employer_expense_account',
                'gl_nssa_employee_liability_account',
                'gl_aids_levy_liability_account',
                'gl_payroll_suspense_account',
                'gl_wages_payable_account',
            ]);
        });
    }
}
