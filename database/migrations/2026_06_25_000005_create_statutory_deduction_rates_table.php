<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatutoryDeductionRatesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('statutory_deduction_rates')) {
            return;
        }


        Schema::create('statutory_deduction_rates', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('statutory_deduction_type_id')->unsigned();
            $table->foreign('statutory_deduction_type_id')
                ->references('id')->on('statutory_deduction_types')->onDelete('cascade');

            $table->bigInteger('currency_id')->unsigned()->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');

            // Effective date range — payroll date determines which rate applies
            $table->date('effective_from');
            $table->date('effective_to')->nullable()->comment('NULL = currently in force');

            // Rate values (percentage or fixed — one set is used depending on calculation_type)
            $table->decimal('employee_percentage', 8, 4)->default(0);
            $table->decimal('employer_percentage', 8, 4)->default(0);
            $table->decimal('employee_fixed_amount', 15, 4)->default(0);
            $table->decimal('employer_fixed_amount', 15, 4)->default(0);

            // Caps and floors
            $table->decimal('earnings_ceiling', 15, 4)->nullable()
                ->comment('Max earnings subject to this deduction');
            $table->decimal('minimum_employee_contribution', 15, 4)->nullable();
            $table->decimal('maximum_employee_contribution', 15, 4)->nullable();
            $table->decimal('minimum_employer_contribution', 15, 4)->nullable();
            $table->decimal('maximum_employer_contribution', 15, 4)->nullable();

            $table->text('notes')->nullable();
            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['statutory_deduction_type_id', 'effective_from'], 'stat_ded_rates_type_date_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('statutory_deduction_rates');
    }
}
