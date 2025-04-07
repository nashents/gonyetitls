<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTyreAssignmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tyre_assignments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable()->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('tyre_id')->nullable()->unsigned();
            $table->foreign('tyre_id')->references('id')->on('tyres')->onDelete('cascade');
            $table->bigInteger('horse_id')->nullable()->unsigned();
            $table->foreign('horse_id')->references('id')->on('horses')->onDelete('cascade');
            $table->bigInteger('vehicle_id')->nullable()->unsigned();
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->bigInteger('trailer_id')->nullable()->unsigned();
            $table->foreign('trailer_id')->references('id')->on('trailers')->onDelete('cascade');
            $table->string('type')->nullable();
            $table->string('starting_odometer')->nullable();
            $table->string('ending_odometer')->nullable();
            $table->string('axle')->nullable();
            $table->string('position')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->nullable();
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
        Schema::dropIfExists('tyre_assignments');
    }
}
