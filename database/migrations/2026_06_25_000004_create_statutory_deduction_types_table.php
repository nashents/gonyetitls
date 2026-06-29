<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatutoryDeductionTypesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('statutory_deduction_types')) {
            return;
        }


        Schema::create('statutory_deduction_types', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('company_id')->unsigned()->nullable()
                ->comment('NULL = system-level / applies to all companies in country');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');

            $table->string('country', 10)->default('ZW');
            $table->string('name');
            $table->string('code', 50)
                ->comment('PAYE, NSSA, NEC, PENSION, AIDS_LEVY, WORKERS_COMP, MEDICAL_AID, UNION, OTHER');

            $table->enum('calculation_type', ['percentage', 'fixed', 'bracket'])
                ->default('percentage');

            $table->enum('applies_to', ['employee', 'employer', 'both'])->default('both');

            $table->boolean('is_tax_deductible')->default(false)
                ->comment('Can employee contribution reduce taxable income?');
            $table->boolean('is_pre_tax')->default(false)
                ->comment('Deducted before PAYE calculation (reduces taxable gross)?');

            // GL accounts
            $table->string('gl_employee_debit_account')->nullable();
            $table->string('gl_employee_credit_account')->nullable();
            $table->string('gl_employer_debit_account')->nullable();
            $table->string('gl_employer_credit_account')->nullable();

            $table->unsignedSmallInteger('sort_order')->default(0)
                ->comment('Determines deduction processing sequence');

            $table->boolean('active')->default(true);
            $table->text('notes')->nullable();

            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['country', 'code']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('statutory_deduction_types');
    }
}
