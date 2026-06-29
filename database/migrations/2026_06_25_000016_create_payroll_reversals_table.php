<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollReversalsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('payroll_reversals')) {
            return;
        }


        Schema::create('payroll_reversals', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('company_id')->unsigned()->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');

            $table->bigInteger('original_payroll_run_id')->unsigned();
            $table->foreign('original_payroll_run_id')
                ->references('id')->on('payroll_runs')->onDelete('cascade');

            // The new payroll_run created to hold reversing entries
            $table->bigInteger('reversal_payroll_run_id')->unsigned()->nullable();
            $table->foreign('reversal_payroll_run_id')
                ->references('id')->on('payroll_runs')->onDelete('set null');

            $table->enum('reversal_type', ['full', 'partial'])->default('full');
            $table->json('employee_ids')->nullable()
                ->comment('For partial reversals — which employee salary lines to reverse');

            $table->text('reason')->comment('Mandatory — why is this payroll being reversed?');

            $table->enum('status', [
                'pending_approval',
                'approved',
                'rejected',
                'processing',
                'completed',
                'failed',
            ])->default('pending_approval');

            $table->bigInteger('requested_by')->unsigned()->nullable();
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('set null');

            $table->bigInteger('approved_by')->unsigned()->nullable();
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');

            $table->text('approval_comments')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            // Flags to track what has been reversed
            $table->boolean('earnings_reversed')->default(false);
            $table->boolean('deductions_reversed')->default(false);
            $table->boolean('loans_reversed')->default(false);
            $table->boolean('advances_reversed')->default(false);
            $table->boolean('leave_accruals_reversed')->default(false);
            $table->boolean('gl_postings_reversed')->default(false);
            $table->boolean('statutory_liabilities_reversed')->default(false);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payroll_reversals');
    }
}
