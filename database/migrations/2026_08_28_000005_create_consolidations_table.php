<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConsolidationsTable extends Migration
{
    public function up()
    {
        Schema::create('consolidations', function (Blueprint $table) {
            $table->id();
            $table->string('consolidation_number')->unique();

            $table->unsignedBigInteger('master_shipment_id');
            $table->foreign('master_shipment_id')->references('id')->on('shipments')->onDelete('cascade');

            $table->unsignedBigInteger('master_transport_document_id')->nullable();
            $table->foreign('master_transport_document_id')->references('id')->on('transport_documents')->onDelete('set null');

            $table->string('cost_allocation_basis')->nullable();
            $table->string('status')->default('draft')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('consolidations');
    }
}
