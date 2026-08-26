<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShipmentsTable extends Migration
{
    public function up()
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('freight_job_id');
            $table->foreign('freight_job_id')->references('id')->on('freight_jobs')->onDelete('cascade');

            $table->string('shipment_number')->unique();

            $table->string('mode')->nullable();
            $table->string('shipment_type')->nullable();

            $table->unsignedBigInteger('port_of_loading_id')->nullable();
            $table->foreign('port_of_loading_id')->references('id')->on('locations')->onDelete('set null');
            $table->unsignedBigInteger('port_of_discharge_id')->nullable();
            $table->foreign('port_of_discharge_id')->references('id')->on('locations')->onDelete('set null');
            $table->unsignedBigInteger('place_of_receipt_id')->nullable();
            $table->foreign('place_of_receipt_id')->references('id')->on('locations')->onDelete('set null');
            $table->unsignedBigInteger('place_of_delivery_id')->nullable();
            $table->foreign('place_of_delivery_id')->references('id')->on('locations')->onDelete('set null');

            $table->dateTime('etd')->nullable();
            $table->dateTime('eta')->nullable();
            $table->dateTime('actual_departure')->nullable();
            $table->dateTime('actual_arrival')->nullable();

            $table->string('booking_reference')->nullable();
            $table->string('freight_terms')->nullable();
            $table->string('incoterm')->nullable();

            $table->text('cargo_description')->nullable();
            $table->decimal('gross_weight', 15, 3)->nullable();
            $table->decimal('volume_cbm', 15, 3)->nullable();
            $table->integer('package_count')->nullable();

            $table->string('status')->default('draft')->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('shipments');
    }
}
