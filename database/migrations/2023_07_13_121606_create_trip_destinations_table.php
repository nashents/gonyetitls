<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTripDestinationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trip_destinations', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('trip_id')->unsigned()->nullable();
            $table->foreign('trip_id')->references('id')->on('trips')->onDelete('cascade');
            $table->bigInteger('destination_id')->unsigned()->nullable();
            $table->foreign('destination_id')->references('id')->on('destinations')->onDelete('cascade');
            $table->bigInteger('offloading_point_id')->unsigned()->nullable();
            $table->foreign('offloading_point_id')->references('id')->on('offloading_points')->onDelete('cascade');
            $table->bigInteger('measurement_id')->unsigned()->nullable();
            $table->foreign('measurement_id')->references('id')->on('measurements')->onDelete('cascade');
            $table->string('offloading_date')->nullable();
            $table->string('weight')->nullable();
            $table->string('quantity')->nullable();
            $table->string('litreage')->nullable();
            $table->string('litreage_at_20')->nullable();
            $table->string('rate')->nullable();
            $table->string('freight')->nullable();
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
        Schema::dropIfExists('trip_destinations');
    }
}
