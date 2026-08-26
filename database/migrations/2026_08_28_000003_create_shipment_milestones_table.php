<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShipmentMilestonesTable extends Migration
{
    public function up()
    {
        Schema::create('shipment_milestones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shipment_id');
            $table->foreign('shipment_id')->references('id')->on('shipments')->onDelete('cascade');

            $table->unsignedBigInteger('shipment_leg_id')->nullable();
            $table->foreign('shipment_leg_id')->references('id')->on('shipment_legs')->onDelete('set null');

            $table->unsignedBigInteger('shipping_container_id')->nullable();
            $table->foreign('shipping_container_id')->references('id')->on('shipping_containers')->onDelete('set null');

            $table->unsignedInteger('sequence')->nullable();
            $table->string('milestone_code')->index();
            $table->string('milestone_name');

            $table->dateTime('planned_at')->nullable();
            $table->dateTime('estimated_at')->nullable();
            $table->dateTime('actual_at')->nullable();

            $table->unsignedBigInteger('location_id')->nullable();
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('set null');

            $table->string('status')->default('pending')->index();
            $table->string('source')->nullable();
            $table->string('source_system')->nullable();
            $table->string('external_reference')->nullable();
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('shipment_milestones');
    }
}
