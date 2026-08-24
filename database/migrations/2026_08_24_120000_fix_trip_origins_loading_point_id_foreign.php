<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixTripOriginsLoadingPointIdForeign extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trip_origins', function (Blueprint $table) {
            $table->dropForeign('trip_origins_loading_point_id_foreign');
            $table->foreign('loading_point_id')->references('id')->on('loading_points')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trip_origins', function (Blueprint $table) {
            $table->dropForeign('trip_origins_loading_point_id_foreign');
            $table->foreign('loading_point_id')->references('id')->on('offloading_points')->onDelete('cascade');
        });
    }
}
