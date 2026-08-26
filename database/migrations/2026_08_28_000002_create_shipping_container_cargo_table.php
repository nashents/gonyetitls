<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShippingContainerCargoTable extends Migration
{
    public function up()
    {
        Schema::create('shipping_container_cargo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('shipping_container_id');
            $table->foreign('shipping_container_id')->references('id')->on('shipping_containers')->onDelete('cascade');

            $table->unsignedBigInteger('shipment_cargo_id');
            $table->foreign('shipment_cargo_id')->references('id')->on('shipment_cargo')->onDelete('cascade');

            $table->integer('quantity')->nullable();
            $table->decimal('weight', 15, 3)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('shipping_container_cargo');
    }
}
