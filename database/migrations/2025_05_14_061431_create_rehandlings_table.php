<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRehandlingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rehandlings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->bigInteger('currency_id')->unsigned()->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade');
            $table->bigInteger('shift_id')->nullable();
            $table->bigInteger('location_id')->unsigned()->nullable();
            $table->bigInteger('work_id')->unsigned()->nullable();
            $table->string('weight')->nullable();
            $table->string('start_time')->nullable();
            $table->string('open_mileage')->nullable();
            $table->string('open_hours')->nullable();
            $table->string('stop_time')->nullable();
            $table->string('close_mileage')->nullable();
            $table->string('close_hours')->nullable();
            $table->string('freight')->nullable();
            $table->string('exchange_amount')->nullable();
            $table->string('exchange_rate')->nullable();
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
        Schema::dropIfExists('rehandlings');
    }
}
