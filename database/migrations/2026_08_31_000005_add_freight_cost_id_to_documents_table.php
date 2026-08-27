<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFreightCostIdToDocumentsTable extends Migration
{
    public function up()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->unsignedBigInteger('freight_cost_id')->nullable()->after('customs_declaration_id');
            $table->foreign('freight_cost_id')->references('id')->on('freight_costs')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['freight_cost_id']);
            $table->dropColumn('freight_cost_id');
        });
    }
}
