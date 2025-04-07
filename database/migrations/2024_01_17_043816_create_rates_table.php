<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rates', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable()->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('from')->nullable();
            $table->string('to')->nullable();
            $table->string('category')->nullable();
            $table->bigInteger('loading_point_id')->nullable()->unsigned();
            $table->foreign('loading_point_id')->references('id')->on('loading_points')->onDelete('cascade');
            $table->bigInteger('offloading_point_id')->nullable()->unsigned();
            $table->foreign('offloading_point_id')->references('id')->on('offloading_points')->onDelete('cascade');
            $table->bigInteger('currency_id')->nullable()->unsigned();
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade');
            $table->bigInteger('cargo_id')->nullable()->unsigned();
            $table->foreign('cargo_id')->references('id')->on('cargos')->onDelete('cascade');
            $table->string('freight_calculation')->nullable();
            $table->string('weight')->nullable();
            $table->string('litreage')->nullable();
            $table->string('distance')->nullable();
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
        Schema::dropIfExists('rates');
    }
}
