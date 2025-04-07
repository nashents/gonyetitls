<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRetreadTyresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('retread_tyres', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('retread_id')->unsigned()->nullable();
            $table->foreign('retread_id')->references('id')->on('retreads')->onDelete('cascade');
            $table->bigInteger('tyre_id')->unsigned()->nullable();
            $table->bigInteger('horse_id')->unsigned()->nullable();
            $table->foreign('horse_id')->references('id')->on('horses')->onDelete('cascade');
            $table->bigInteger('vehicle_id')->unsigned()->nullable();
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->bigInteger('trailer_id')->unsigned()->nullable();
            $table->foreign('trailer_id')->references('id')->on('trailers')->onDelete('cascade');
            $table->bigInteger('store_id')->unsigned()->nullable();
            $table->bigInteger('tax_id')->unsigned()->nullable();
            $table->bigInteger('date')->unsigned()->nullable();
            $table->string('tax_rate')->nullable();
            $table->string('tax_amount')->nullable();
            $table->string('qty')->nullable();
            $table->string('amount')->nullable();
            $table->string('subtotal')->nullable();
            $table->string('subtotal_incl')->nullable();
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
        Schema::dropIfExists('retread_tyres');
    }
}
