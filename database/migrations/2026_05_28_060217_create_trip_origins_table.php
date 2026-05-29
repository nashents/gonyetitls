<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTripOriginsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trip_origins', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('transport_order_id')->unsigned()->nullable();
            $table->bigInteger('trip_id')->unsigned()->nullable();
            $table->foreign('trip_id')->references('id')->on('trips')->onDelete('cascade');
            $table->bigInteger('trip_transport_order_id')->unsigned()->nullable();
            $table->bigInteger('destination_id')->unsigned()->nullable();
            $table->foreign('destination_id')->references('id')->on('destinations')->onDelete('cascade');
            $table->bigInteger('loading_point_id')->unsigned()->nullable();
            $table->foreign('loading_point_id')->references('id')->on('offloading_points')->onDelete('cascade');
            $table->bigInteger('units_of_measure_id')->unsigned()->nullable();
            $table->foreign('units_of_measure_id')->references('id')->on('units_of_measures')->onDelete('cascade');
            $table->string('loading_date')->nullable();
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
        Schema::dropIfExists('trip_origins');
    }
}
