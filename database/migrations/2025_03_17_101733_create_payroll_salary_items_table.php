<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollSalaryItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payroll_salary_items', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('payroll_salary_id')->unsigned()->nullable();
            $table->foreign('payroll_salary_id')->references('id')->on('payroll_salaries')->onDelete('cascade');
            $table->bigInteger('salary_item_id')->unsigned()->nullable();
            $table->bigInteger('allowance_id')->unsigned()->nullable();
            $table->bigInteger('deduction_id')->unsigned()->nullable();
            $table->bigInteger('loan_id')->unsigned()->nullable();
            $table->bigInteger('currency_id')->unsigned()->nullable();
            $table->string('amount')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payroll_salary_items');
    }
}
