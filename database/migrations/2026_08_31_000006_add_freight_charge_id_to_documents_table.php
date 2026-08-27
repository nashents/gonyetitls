<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFreightChargeIdToDocumentsTable extends Migration
{
    public function up()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->unsignedBigInteger('freight_charge_id')->nullable()->after('freight_cost_id');
            $table->foreign('freight_charge_id')->references('id')->on('freight_charges')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['freight_charge_id']);
            $table->dropColumn('freight_charge_id');
        });
    }
}
