<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCalculatedMileageToShiftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->string('actual_mileage')->nullable();
            $table->string('calculated_mileage')->nullable();
            $table->string('total_fuel')->nullable();
            $table->string('total_loads')->nullable();
            $table->string('open_mileage')->nullable();
            $table->string('close_mileage')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn('actual_mileage');
            $table->dropColumn('calculated_mileage');
            $table->dropColumn('total_fuel');
            $table->dropColumn('total_loads');
            $table->dropColumn('open_mileage');
            $table->dropColumn('close_mileage');
        });
    }
}
