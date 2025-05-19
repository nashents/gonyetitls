<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVehicleIdToShiftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->bigInteger('transporter_id')->unsigned()->nullable();
            $table->foreign('transporter_id')->references('id')->on('transporters')->onDelete('cascade');
            $table->bigInteger('vehicle_id')->unsigned()->nullable();
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->bigInteger('horse_id')->unsigned()->nullable();
            $table->foreign('horse_id')->references('id')->on('horses')->onDelete('cascade');
            $table->bigInteger('cargo_id')->unsigned()->nullable();
            $table->foreign('cargo_id')->references('id')->on('cargos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropForeign(['transporter_id']); // drops the foreign key constraint
            $table->dropColumn('transporter_id');
             $table->dropForeign(['vehicle_id']); // drops the foreign key constraint
            $table->dropColumn('vehicle_id');
             $table->dropForeign(['horse_id']); // drops the foreign key constraint
             $table->dropColumn('horse_id');
             $table->dropForeign(['cargo_id']); // drops the foreign key constraint
             $table->dropColumn('cargo_id');
             
        });
    }
}
