<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShiftsSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shifts_settings', function (Blueprint $table) {
            $table->id();
            $table->string('default_weight')->nullable();
            $table->string('default_rate')->nullable();
            $table->bigInteger('default_loading_point_id')->unsigned()->nullable();
            $table->bigInteger('default_offloading_point_id')->unsigned()->nullable();
            $table->bigInteger('default_transporter_id')->unsigned()->nullable();
            $table->bigInteger('default_from')->unsigned()->nullable();
            $table->bigInteger('default_to')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shifts_settings');
    }
}
