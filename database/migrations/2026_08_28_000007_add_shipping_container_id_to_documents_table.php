<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShippingContainerIdToDocumentsTable extends Migration
{
    public function up()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->unsignedBigInteger('shipping_container_id')->nullable()->after('shipment_id');
            $table->foreign('shipping_container_id')->references('id')->on('shipping_containers')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['shipping_container_id']);
            $table->dropColumn('shipping_container_id');
        });
    }
}
