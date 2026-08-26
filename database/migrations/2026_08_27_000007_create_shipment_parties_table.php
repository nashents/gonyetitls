<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShipmentPartiesTable extends Migration
{
    public function up()
    {
        Schema::create('shipment_parties', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shipment_id');
            $table->foreign('shipment_id')->references('id')->on('shipments')->onDelete('cascade');

            $table->string('party_type');
            $table->unsignedBigInteger('party_id');
            $table->string('role');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['party_type', 'party_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('shipment_parties');
    }
}
