<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SplitPayrollExpenseAccountsByDriverAdmin extends Migration
{
    public function up()
    {
        Schema::table('payroll_company_configs', function (Blueprint $table) {
            // Wages and employer-statutory expense lines are split so driver
            // payroll cost posts to COGS accounts and admin/office payroll cost
            // posts to Ops accounts, per employee, at journal-posting time.
            $table->string('gl_wages_expense_account_admin')->nullable()->after('gl_wages_expense_account');
            $table->string('gl_wages_expense_account_drivers')->nullable()->after('gl_wages_expense_account_admin');

            $table->string('gl_nssa_employer_expense_account_admin')->nullable()->after('gl_nssa_employer_expense_account');
            $table->string('gl_nssa_employer_expense_account_drivers')->nullable()->after('gl_nssa_employer_expense_account_admin');

            $table->string('gl_nec_employer_expense_account_admin')->nullable()->after('gl_nec_employer_expense_account');
            $table->string('gl_nec_employer_expense_account_drivers')->nullable()->after('gl_nec_employer_expense_account_admin');

            $table->string('gl_pension_employer_expense_account_admin')->nullable()->after('gl_pension_employer_expense_account');
            $table->string('gl_pension_employer_expense_account_drivers')->nullable()->after('gl_pension_employer_expense_account_admin');
        });

        Schema::table('payroll_company_configs', function (Blueprint $table) {
            $table->dropColumn([
                'gl_wages_expense_account',
                'gl_nssa_employer_expense_account',
                'gl_nec_employer_expense_account',
                'gl_pension_employer_expense_account',
            ]);
        });
    }

    public function down()
    {
        Schema::table('payroll_company_configs', function (Blueprint $table) {
            $table->string('gl_wages_expense_account')->nullable();
            $table->string('gl_nssa_employer_expense_account')->nullable();
            $table->string('gl_nec_employer_expense_account')->nullable();
            $table->string('gl_pension_employer_expense_account')->nullable();
        });

        Schema::table('payroll_company_configs', function (Blueprint $table) {
            $table->dropColumn([
                'gl_wages_expense_account_admin',
                'gl_wages_expense_account_drivers',
                'gl_nssa_employer_expense_account_admin',
                'gl_nssa_employer_expense_account_drivers',
                'gl_nec_employer_expense_account_admin',
                'gl_nec_employer_expense_account_drivers',
                'gl_pension_employer_expense_account_admin',
                'gl_pension_employer_expense_account_drivers',
            ]);
        });
    }
}
