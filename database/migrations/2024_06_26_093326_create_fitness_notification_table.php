<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFitnessNotificationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fitness_notification', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('fitness_id')->unsigned()->nullable();
            $table->foreign('fitness_id')->references('id')->on('fitnesses')->onDelete('cascade');
            $table->bigInteger('notification_id')->unsigned()->nullable();
            $table->foreign('notification_id')->references('id')->on('notifications')->onDelete('cascade');
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
        Schema::dropIfExists('fitness_notification');
    }
}
