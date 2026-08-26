<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShipmentLegsTable extends Migration
{
    public function up()
    {
        Schema::create('shipment_legs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shipment_id');
            $table->foreign('shipment_id')->references('id')->on('shipments')->onDelete('cascade');

            $table->unsignedInteger('sequence')->default(1);
            $table->string('transport_mode')->nullable();

            $table->unsignedBigInteger('carrier_vendor_id')->nullable();
            $table->foreign('carrier_vendor_id')->references('id')->on('vendors')->onDelete('set null');
            $table->string('carrier_name')->nullable();
            $table->string('carrier_reference')->nullable();

            $table->unsignedBigInteger('origin_location_id')->nullable();
            $table->foreign('origin_location_id')->references('id')->on('locations')->onDelete('set null');
            $table->unsignedBigInteger('destination_location_id')->nullable();
            $table->foreign('destination_location_id')->references('id')->on('locations')->onDelete('set null');

            $table->dateTime('planned_departure')->nullable();
            $table->dateTime('planned_arrival')->nullable();
            $table->dateTime('estimated_departure')->nullable();
            $table->dateTime('estimated_arrival')->nullable();
            $table->dateTime('actual_departure')->nullable();
            $table->dateTime('actual_arrival')->nullable();

            $table->string('status')->default('planned')->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('shipment_legs');
    }
}
