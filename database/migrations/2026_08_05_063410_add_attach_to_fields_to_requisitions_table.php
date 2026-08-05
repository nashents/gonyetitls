<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAttachToFieldsToRequisitionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('requisitions', function (Blueprint $table) {
            $table->string('attach_to')->nullable();

            $table->bigInteger('asset_id')->unsigned()->nullable();

            $table->bigInteger('driver_id')->unsigned()->nullable();
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');

            $table->bigInteger('horse_id')->unsigned()->nullable();
            $table->foreign('horse_id')->references('id')->on('horses')->onDelete('cascade');

            $table->bigInteger('trailer_id')->unsigned()->nullable();

            $table->bigInteger('transporter_id')->unsigned()->nullable();
            $table->foreign('transporter_id')->references('id')->on('transporters')->onDelete('cascade');

            $table->bigInteger('vehicle_id')->unsigned()->nullable();
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');

            $table->bigInteger('attached_employee_id')->unsigned()->nullable();
            $table->foreign('attached_employee_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('requisitions', function (Blueprint $table) {
            $table->dropForeign(['driver_id']);
            $table->dropForeign(['horse_id']);
            $table->dropForeign(['transporter_id']);
            $table->dropForeign(['vehicle_id']);
            $table->dropForeign(['attached_employee_id']);

            $table->dropColumn([
                'attach_to',
                'asset_id',
                'driver_id',
                'horse_id',
                'trailer_id',
                'transporter_id',
                'vehicle_id',
                'attached_employee_id',
            ]);
        });
    }
}
