<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * payroll_runs — the proper lifecycle table for a single payroll batch.
 *
 * The existing `payrolls` table is preserved and linked here via
 * payrolls.payroll_run_id (added in a separate migration).
 *
 * Status flow:
 *   draft → calculating → validated → approved → locked → exported → posted → archived
 *   Any approved/locked/posted state can be reversed (creates a new reversal run).
 */
class CreatePayrollRunsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('payroll_runs')) {
            return;
        }


        Schema::create('payroll_runs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('company_id')->unsigned();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');

            $table->bigInteger('payroll_frequency_id')->unsigned()->nullable();
            $table->foreign('payroll_frequency_id')->references('id')->on('payroll_frequencies')->onDelete('set null');

            $table->bigInteger('currency_id')->unsigned()->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');

            $table->string('name')->comment('e.g. "June 2026 Monthly Payroll"');
            $table->date('period_start');
            $table->date('period_end');
            $table->date('payroll_date')
                ->comment('Date used to resolve which statutory rates apply');
            $table->date('payment_date')->nullable();

            $table->enum('status', [
                'draft',
                'calculating',
                'validated',
                'approved',
                'locked',
                'exported',
                'posted',
                'archived',
                'reversed',
            ])->default('draft');

            // Proration override for this specific run
            $table->enum('proration_method', [
                'calendar_days', 'working_days', 'fixed_30', 'scheduled_days',
            ])->nullable()->comment('NULL = inherit from company config');

            // Headcount snapshot
            $table->unsignedInteger('employee_count')->default(0);
            $table->decimal('total_gross', 15, 4)->default(0);
            $table->decimal('total_net', 15, 4)->default(0);
            $table->decimal('total_deductions', 15, 4)->default(0);
            $table->decimal('total_employer_contributions', 15, 4)->default(0);

            // Lifecycle timestamps & users
            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->bigInteger('calculated_by')->unsigned()->nullable();
            $table->bigInteger('validated_by')->unsigned()->nullable();
            $table->bigInteger('approved_by')->unsigned()->nullable();
            $table->bigInteger('locked_by')->unsigned()->nullable();
            $table->bigInteger('posted_by')->unsigned()->nullable();
            $table->bigInteger('reversed_by')->unsigned()->nullable();

            $table->timestamp('calculated_at')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->timestamp('exported_at')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->timestamp('reversed_at')->nullable();

            // For reversal runs — points back to the run being reversed
            $table->bigInteger('reversal_of_payroll_run_id')->unsigned()->nullable();
            $table->foreign('reversal_of_payroll_run_id')
                ->references('id')->on('payroll_runs')->onDelete('set null');

            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'period_start', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('payroll_runs');
    }
}
