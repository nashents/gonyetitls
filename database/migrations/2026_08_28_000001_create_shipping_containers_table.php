<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShippingContainersTable extends Migration
{
    public function up()
    {
        Schema::create('shipping_containers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shipment_id');
            $table->foreign('shipment_id')->references('id')->on('shipments')->onDelete('cascade');

            $table->string('container_number')->nullable()->index();
            $table->string('container_type')->nullable();
            $table->string('seal_number')->nullable();

            $table->unsignedBigInteger('shipping_line_vendor_id')->nullable();
            $table->foreign('shipping_line_vendor_id')->references('id')->on('vendors')->onDelete('set null');
            $table->string('shipping_line_name')->nullable();

            $table->decimal('tare_weight', 15, 3)->nullable();
            $table->decimal('gross_weight', 15, 3)->nullable();
            $table->decimal('cargo_weight', 15, 3)->nullable();
            $table->decimal('vgm', 15, 3)->nullable();
            $table->string('temperature')->nullable();

            $table->string('status')->default('booked')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('shipping_containers');
    }
}
