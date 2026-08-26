<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFreightJobIdAndShipmentIdToDocumentsTable extends Migration
{
    public function up()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->unsignedBigInteger('freight_job_id')->nullable()->after('id');
            $table->foreign('freight_job_id')->references('id')->on('freight_jobs')->onDelete('cascade');

            $table->unsignedBigInteger('shipment_id')->nullable()->after('freight_job_id');
            $table->foreign('shipment_id')->references('id')->on('shipments')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['freight_job_id']);
            $table->dropForeign(['shipment_id']);
            $table->dropColumn(['freight_job_id', 'shipment_id']);
        });
    }
}
