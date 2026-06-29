<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePensionSchemeRatesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('pension_scheme_rates')) {
            return;
        }


        Schema::create('pension_scheme_rates', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('pension_scheme_id')->unsigned();
            $table->foreign('pension_scheme_id')->references('id')->on('pension_schemes')->onDelete('cascade');

            $table->bigInteger('currency_id')->unsigned()->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');

            $table->date('effective_from');
            $table->date('effective_to')->nullable();

            $table->decimal('employee_percentage', 8, 4)->default(0);
            $table->decimal('employer_percentage', 8, 4)->default(0);
            $table->decimal('employee_fixed_amount', 15, 4)->default(0);
            $table->decimal('employer_fixed_amount', 15, 4)->default(0);

            // Employer match: employer matches up to X% of what employee contributes
            $table->decimal('employer_match_percentage', 8, 4)->nullable()
                ->comment('Employer matches employee contribution up to this %');
            $table->decimal('employer_match_cap', 15, 4)->nullable()
                ->comment('Max employer match in currency amount');

            // Floors and ceilings
            $table->decimal('earnings_ceiling', 15, 4)->nullable();
            $table->decimal('minimum_employee_contribution', 15, 4)->nullable();
            $table->decimal('maximum_employee_contribution', 15, 4)->nullable();
            $table->decimal('minimum_employer_contribution', 15, 4)->nullable();
            $table->decimal('maximum_employer_contribution', 15, 4)->nullable();

            $table->enum('calculation_basis', ['basic_salary', 'gross_salary', 'pensionable_earnings'])
                ->default('basic_salary');

            $table->text('notes')->nullable();
            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['pension_scheme_id', 'effective_from']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('pension_scheme_rates');
    }
}
