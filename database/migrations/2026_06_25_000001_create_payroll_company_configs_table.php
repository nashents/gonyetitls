<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollCompanyConfigsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('payroll_company_configs')) {
            return;
        }


        Schema::create('payroll_company_configs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('company_id')->unsigned();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');

            // Country & legal context
            $table->string('country', 10)->default('ZW')->comment('ISO 3166-1 alpha-2');
            $table->bigInteger('currency_id')->unsigned()->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');
            $table->string('tax_authority_name')->nullable()->comment('ZIMRA, SARS, ZRA, etc.');
            $table->string('social_security_authority_name')->nullable()->comment('NSSA, NAPSA, etc.');

            // Proration
            $table->enum('proration_method', [
                'calendar_days',
                'working_days',
                'fixed_30',
                'scheduled_days',
            ])->default('calendar_days');
            $table->json('work_week_days')->nullable()->comment('Array of ISO weekday numbers 1=Mon … 7=Sun');
            $table->boolean('exclude_public_holidays_from_proration')->default(false);

            // Payroll controls
            $table->boolean('allow_negative_net_pay')->default(false);
            $table->boolean('require_approval_before_lock')->default(true);
            $table->boolean('require_approval_for_reversal')->default(true);
            $table->boolean('auto_deduct_loans')->default(true);
            $table->boolean('auto_deduct_salary_advances')->default(true);

            // GL defaults
            $table->string('gl_wages_expense_account')->nullable();
            $table->string('gl_nssa_liability_account')->nullable();
            $table->string('gl_paye_liability_account')->nullable();
            $table->string('gl_pension_liability_account')->nullable();
            $table->string('gl_nec_liability_account')->nullable();

            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->boolean('active')->default(true);

            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->bigInteger('updated_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payroll_company_configs');
    }
}
