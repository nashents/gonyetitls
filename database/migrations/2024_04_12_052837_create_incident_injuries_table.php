<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIncidentInjuriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('incident_injuries', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('incident_id')->unsigned()->nullable();
            $table->foreign('incident_id')->references('id')->on('incidents')->onDelete('cascade');
            $table->string('name')->nullable();
            $table->string('taken_to')->nullable();
            $table->string('body_part')->nullable();
            $table->string('days_lost')->nullable();
            $table->string('nature_of_injury')->nullable();
            $table->string('object')->nullable();
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
        Schema::dropIfExists('incident_injuries');
    }
}
