<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConsumptionLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('consumption_logs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('consumption_id')->unsigned()->nullable();
            $table->foreign('consumption_id')->references('id')->on('consumptions')->onDelete('cascade');
            $table->string('load_date')->nullable();
            $table->string('starting_fuel_level')->nullable();
            $table->string('starting_mileage')->nullable();
            $table->string('load_quantity')->nullable();
            $table->string('amount')->nullable();
            $table->string('refill_date')->nullable();
            $table->string('ending_fuel_level')->nullable();
            $table->string('ending_mileage')->nullable();
            $table->string('fuel_consumption')->nullable();
            $table->string('fuel_per_kilometer')->nullable();
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
        Schema::dropIfExists('consumption_logs');
    }
}
