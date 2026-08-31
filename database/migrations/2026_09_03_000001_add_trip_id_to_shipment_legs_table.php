<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTripIdToShipmentLegsTable extends Migration
{
    public function up()
    {
        Schema::table('shipment_legs', function (Blueprint $table) {
            $table->unsignedBigInteger('trip_id')->nullable()->after('carrier_reference');
            $table->foreign('trip_id')->references('id')->on('trips')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('shipment_legs', function (Blueprint $table) {
            $table->dropForeign(['trip_id']);
            $table->dropColumn('trip_id');
        });
    }
}
