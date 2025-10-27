<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFuelConsumptionEmptyStandardToHorsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('horses', function (Blueprint $table) {
            $table->float('fuel_consumption_empty_standard')->nullable();
            $table->float('fuel_consumption_loaded_standard')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('horses', function (Blueprint $table) {
            $table->dropColumn('fuel_consumption_empty_standard');
            $table->dropColumn('fuel_consumption_loaded_standard');
        });
    }
}
