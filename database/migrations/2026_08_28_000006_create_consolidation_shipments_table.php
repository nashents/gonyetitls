<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConsolidationShipmentsTable extends Migration
{
    public function up()
    {
        Schema::create('consolidation_shipments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('consolidation_id');
            $table->foreign('consolidation_id')->references('id')->on('consolidations')->onDelete('cascade');

            $table->unsignedBigInteger('shipment_id');
            $table->foreign('shipment_id')->references('id')->on('shipments')->onDelete('cascade');

            $table->decimal('allocation_value', 15, 4)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['consolidation_id', 'shipment_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('consolidation_shipments');
    }
}
