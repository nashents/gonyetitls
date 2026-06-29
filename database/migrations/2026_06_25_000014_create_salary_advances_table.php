<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Salary Advances — deliberately separate from Loans.
 *
 * Business rule: salary advances are short-term, no interest,
 * recovered against future salary automatically.
 * Loans have schedules, interest, guarantors and external financing.
 */
class CreateSalaryAdvancesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('salary_advances')) {
            return;
        }


        Schema::create('salary_advances', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('employee_id')->unsigned();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');

            $table->bigInteger('company_id')->unsigned()->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');

            $table->bigInteger('currency_id')->unsigned()->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');

            $table->string('advance_number')->nullable()
                ->comment('Auto-generated reference number');

            $table->decimal('amount', 15, 4);
            $table->decimal('balance', 15, 4)->comment('Outstanding balance remaining');
            $table->decimal('monthly_recovery_amount', 15, 4)->default(0)
                ->comment('Fixed amount to deduct each payroll run. 0 = recover in full next run.');

            $table->date('advance_date');
            $table->date('repayment_start_date')->nullable()
                ->comment('Which payroll period deductions begin');

            $table->string('reason')->nullable();
            $table->text('notes')->nullable();

            // Approval workflow
            $table->string('authorization')->default('pending')
                ->comment('pending, approved, rejected');
            $table->bigInteger('authorized_by')->unsigned()->nullable();
            $table->foreign('authorized_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamp('authorized_at')->nullable();
            $table->text('authorization_comments')->nullable();

            $table->string('status')->default('Unpaid')
                ->comment('Unpaid, Paid, Partially Paid, Cancelled');

            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['employee_id', 'status', 'authorization']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('salary_advances');
    }
}
