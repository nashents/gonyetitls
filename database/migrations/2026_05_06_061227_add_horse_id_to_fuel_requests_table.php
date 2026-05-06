<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHorseIdToFuelRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fuel_requests', function (Blueprint $table) {
            $table->bigInteger('horse_id')->unsigned()->nullable();
            $table->foreign('horse_id')->references('id')->on('horses')->onDelete('cascade');
            $table->bigInteger('asset_id')->unsigned()->nullable();
            $table->foreign('asset_id')->references('id')->on('assets')->onDelete('cascade');
            $table->boolean('from_allocation')->default(False);
            $table->text('reason')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fuel_requests', function (Blueprint $table) {
            $table->dropForeign(['horse_id']);
            $table->dropForeign(['asset_id']);
            $table->dropColumn(
                [
                   'horse_id',
                   'asset_id',
                   'from_allocation',
                   'reason',
                ]);
           
        });
    }
}
