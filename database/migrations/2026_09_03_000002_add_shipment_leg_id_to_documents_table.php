<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShipmentLegIdToDocumentsTable extends Migration
{
    public function up()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->unsignedBigInteger('shipment_leg_id')->nullable()->after('shipping_container_id');
            $table->foreign('shipment_leg_id')->references('id')->on('shipment_legs')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['shipment_leg_id']);
            $table->dropColumn('shipment_leg_id');
        });
    }
}
