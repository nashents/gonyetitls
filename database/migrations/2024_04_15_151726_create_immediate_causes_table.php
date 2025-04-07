<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImmediateCausesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('immediate_causes', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('loss_id')->unsigned()->nullable();
            $table->foreign('loss_id')->references('id')->on('losses')->onDelete('cascade');
            $table->bigInteger('incident_id')->unsigned()->nullable();
            $table->foreign('incident_id')->references('id')->on('incidents')->onDelete('cascade');
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
        Schema::dropIfExists('immediate_causes');
    }
}
