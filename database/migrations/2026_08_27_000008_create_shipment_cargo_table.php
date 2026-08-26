<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShipmentCargoTable extends Migration
{
    public function up()
    {
        Schema::create('shipment_cargo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shipment_id');
            $table->foreign('shipment_id')->references('id')->on('shipments')->onDelete('cascade');

            $table->unsignedBigInteger('cargo_id')->nullable();
            $table->foreign('cargo_id')->references('id')->on('cargos')->onDelete('set null');

            $table->string('commodity')->nullable();
            $table->text('description')->nullable();
            $table->string('hs_code')->nullable();
            $table->decimal('quantity', 15, 3)->nullable();
            $table->string('uom')->nullable();
            $table->integer('packages')->nullable();
            $table->string('package_type')->nullable();
            $table->decimal('gross_weight', 15, 3)->nullable();
            $table->decimal('net_weight', 15, 3)->nullable();
            $table->decimal('chargeable_weight', 15, 3)->nullable();
            $table->decimal('cbm', 15, 3)->nullable();
            $table->string('dimensions')->nullable();
            $table->unsignedBigInteger('country_of_origin_id')->nullable();
            $table->foreign('country_of_origin_id')->references('id')->on('countries')->onDelete('set null');
            $table->string('marks_and_numbers')->nullable();
            $table->boolean('is_dangerous_goods')->default(false);
            $table->string('un_dg_number')->nullable();
            $table->string('temperature_control')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('shipment_cargo');
    }
}
