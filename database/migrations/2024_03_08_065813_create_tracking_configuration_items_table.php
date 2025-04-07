<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrackingConfigurationItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tracking_configuration_items', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('tracking_configuration_id')->unsigned()->nullable();
            $table->foreign('tracking_configuration_id')->references('id')->on('tracking_configurations')->onDelete('cascade');
            $table->bigInteger('tracking_variable_id')->unsigned()->nullable();
            $table->string('header')->nullable();
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
        Schema::dropIfExists('tracking_configuration_items');
    }
}
