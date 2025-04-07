<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFitnessRecipientTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fitness_recipient', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('recipient_id')->unsigned()->nullable();
            $table->foreign('recipient_id')->references('id')->on('recipients')->onDelete('cascade');
            $table->bigInteger('fitness_id')->unsigned()->nullable();
            $table->foreign('fitness_id')->references('id')->on('fitnesses')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fitness_recipient');
    }
}
