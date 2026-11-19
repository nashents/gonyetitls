<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Shared-premium deductions (Medical Aid, Funeral Cover ...): the company pays
 * a % of a fixed premium, the employee the rest.
 *
 * deductions:
 *   contribution_type       employee (default, as before) | shared
 *   premium                 full premium per pay period
 *   employer_percentage     company's share of the premium (employee = 100 - this)
 *   payable_account_id      CR: employee + company share owed to the provider
 *   expense_account_id      DR: company share (employer cost)
 *   employer_share_taxable  company share is a taxable benefit (added to PAYE base)
 *   paye_credit_percentage  % of the employee's share credited against PAYE
 *
 * salary_items / payroll_salary_items.employer_amount: the company share on
 * that line (amount stays the employee share, which is what reduces net).
 */
class AddSharedPremiumToDeductions extends Migration
{
    public function up()
    {
        Schema::table('deductions', function (Blueprint $table) {
            $table->string('contribution_type')->default('employee')->after('calculate_on');
            $table->decimal('premium', 18, 2)->nullable()->after('contribution_type');
            $table->decimal('employer_percentage', 8, 4)->nullable()->after('premium');
            $table->bigInteger('payable_account_id')->unsigned()->nullable()->after('employer_percentage');
            $table->bigInteger('expense_account_id')->unsigned()->nullable()->after('payable_account_id');
            $table->boolean('employer_share_taxable')->default(true)->after('expense_account_id');
            $table->decimal('paye_credit_percentage', 8, 4)->nullable()->after('employer_share_taxable');
        });

        Schema::table('salary_items', function (Blueprint $table) {
            $table->string('employer_amount')->nullable()->after('amount');
        });

        Schema::table('payroll_salary_items', function (Blueprint $table) {
            $table->string('employer_amount')->nullable()->after('amount');
        });
    }

    public function down()
    {
        Schema::table('payroll_salary_items', fn (Blueprint $t) => $t->dropColumn('employer_amount'));
        Schema::table('salary_items', fn (Blueprint $t) => $t->dropColumn('employer_amount'));
        Schema::table('deductions', function (Blueprint $table) {
            $table->dropColumn([
                'contribution_type', 'premium', 'employer_percentage', 'payable_account_id',
                'expense_account_id', 'employer_share_taxable', 'paye_credit_percentage',
            ]);
        });
    }
}
