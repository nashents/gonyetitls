<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTyreDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tyre_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('tyre_id')->unsigned()->nullable();
            $table->foreign('tyre_id')->references('id')->on('tyres')->onDelete('cascade');
            $table->bigInteger('product_id')->unsigned()->nullable();
            $table->string('tyre_number')->nullable();
            $table->string('type')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('rate')->nullable();
            $table->string('width')->nullable();
            $table->string('aspect_ratio')->nullable();
            $table->string('diameter')->nullable();
            $table->boolean('status')->default(1);
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
        Schema::dropIfExists('tyre_details');
    }
}
