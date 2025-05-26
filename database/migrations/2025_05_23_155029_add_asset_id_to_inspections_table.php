<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAssetIdToInspectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inspections', function (Blueprint $table) {
            $table->bigInteger('asset_id')->unsigned()->nullable();
            $table->foreign('asset_id')->references('id')->on('assets')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inspections', function (Blueprint $table) {
           $table->dropForeign(['asset_id']); // drops the foreign key constraint
            $table->dropColumn('asset_id');
        });
    }
}
