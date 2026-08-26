<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContainerChargeExposuresTable extends Migration
{
    public function up()
    {
        Schema::create('container_charge_exposures', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('shipping_container_id');
            $table->foreign('shipping_container_id')->references('id')->on('shipping_containers')->onDelete('cascade');

            $table->unsignedBigInteger('shipment_id');
            $table->foreign('shipment_id')->references('id')->on('shipments')->onDelete('cascade');

            $table->string('charge_type')->index();
            $table->unsignedInteger('free_days');
            $table->date('start_date');
            $table->date('last_free_day')->nullable();
            $table->date('stop_date')->nullable();

            $table->unsignedBigInteger('currency_id')->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');

            $table->unsignedInteger('chargeable_days')->nullable();
            $table->decimal('estimated_exposure', 15, 2)->nullable()->default(0);
            $table->decimal('actual_charge', 15, 2)->nullable();

            $table->string('status')->default('within_free_period')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['shipping_container_id', 'charge_type'], 'container_charge_exposures_container_type_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('container_charge_exposures');
    }
}
