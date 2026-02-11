<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMovementToPayrollSalaryItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_salary_items', function (Blueprint $table) {
            $table->bigInteger('recovery_id')->unsigned()->nullable();
            $table->string('movement')->nullable();
            $table->decimal('exchange_rate',10,2)->nullable();
            $table->decimal('exchange_amount',10,2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payroll_salary_items', function (Blueprint $table) {
            $table->dropColumn([
                'recovery_id',
                'movement',
                'exchange_rate',
                'exchange_amount'
            ]);
        });
    }
}
