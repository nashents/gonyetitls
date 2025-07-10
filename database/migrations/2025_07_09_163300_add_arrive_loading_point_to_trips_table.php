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
            $table->text('arrive_loading_point')->nullable();
            $table->text('loading_time')->nullable();
            $table->text('depart_loading_point')->nullable();
            $table->text('arrive_offloading_point')->nullable();
            $table->text('offloading_time')->nullable();
            $table->text('depart_offloading_point')->nullable();
            $table->text('drive_time_empty')->nullable();
            $table->text('drive_time_loaded')->nullable();
            $table->text('actual_mileage')->nullable();
            $table->text('calculated_mileage')->nullable();
            $table->text('fuel_consumption')->nullable();
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
