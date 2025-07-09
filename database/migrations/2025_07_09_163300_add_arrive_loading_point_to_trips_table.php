<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddArriveLoadingPointToTripsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->string('arrive_loading_point')->nullable();
            $table->string('loading_time')->nullable();
            $table->string('depart_loading_point')->nullable();
            $table->string('arrive_offloading_point')->nullable();
            $table->string('offloading_time')->nullable();
            $table->string('depart_offloading_point')->nullable();
            $table->string('drive_time_empty')->nullable();
            $table->string('drive_time_loaded')->nullable();
            $table->string('actual_mileage')->nullable();
            $table->string('calculated_mileage')->nullable();
            $table->string('fuel_consumption')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn('arrive_loading_point');
            $table->dropColumn('loading_time');
            $table->dropColumn('depart_loading_point');
            $table->dropColumn('arrive_offloading_point');
            $table->dropColumn('offloading_time');
            $table->dropColumn('depart_offloading_point');
            $table->dropColumn('drive_time_empty');
            $table->dropColumn('drive_time_loaded');
            $table->dropColumn('actual_mileage');
            $table->dropColumn('calculated_mileage');
            $table->dropColumn('fuel_consumption');
        });
    }
}
