<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUnitsOfMeasureIdToTripDestinationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trip_destinations', function (Blueprint $table) {
            $table->foreignId('units_of_measure_id')->nullable()->constrained();
            $table->bigInteger('trip_transport_order_id')->unsigned()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trip_destinations', function (Blueprint $table) {
             $table->dropForeign(['units_of_measure_id']);
             $table->dropColumn('units_of_measure_id');
             $table->dropColumn('trip_transport_order_id');
        });
    }
}
