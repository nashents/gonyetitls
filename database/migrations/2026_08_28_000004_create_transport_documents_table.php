<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransportDocumentsTable extends Migration
{
    public function up()
    {
        Schema::create('transport_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shipment_id');
            $table->foreign('shipment_id')->references('id')->on('shipments')->onDelete('cascade');

            $table->string('document_type')->index();
            $table->string('document_number')->index();
            $table->date('issue_date')->nullable();

            $table->unsignedBigInteger('carrier_vendor_id')->nullable();
            $table->foreign('carrier_vendor_id')->references('id')->on('vendors')->onDelete('set null');
            $table->string('carrier_name')->nullable();

            $table->string('place_of_issue')->nullable();
            $table->string('freight_payable_at')->nullable();
            $table->unsignedTinyInteger('number_of_originals')->nullable();

            $table->string('status')->default('draft')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('transport_documents');
    }
}
