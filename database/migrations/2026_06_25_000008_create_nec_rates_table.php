<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNecRatesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('nec_rates')) {
            return;
        }


        Schema::create('nec_rates', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('nec_category_id')->unsigned();
            $table->foreign('nec_category_id')->references('id')->on('nec_categories')->onDelete('cascade');

            $table->bigInteger('currency_id')->unsigned()->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');

            $table->date('effective_from');
            $table->date('effective_to')->nullable();

            $table->decimal('employee_percentage', 8, 4)->default(0);
            $table->decimal('employer_percentage', 8, 4)->default(0);
            $table->decimal('employee_fixed_amount', 15, 4)->default(0);
            $table->decimal('employer_fixed_amount', 15, 4)->default(0);
            $table->decimal('earnings_ceiling', 15, 4)->nullable();

            $table->enum('calculation_basis', ['basic_salary', 'gross_salary', 'pensionable_earnings'])
                ->default('basic_salary');

            $table->text('notes')->nullable();
            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['nec_category_id', 'effective_from']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('nec_rates');
    }
}
