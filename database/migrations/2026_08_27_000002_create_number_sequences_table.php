<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNumberSequencesTable extends Migration
{
    public function up()
    {
        Schema::create('number_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->unsignedSmallInteger('year');
            $table->unsignedBigInteger('last_number')->default(0);
            $table->timestamps();

            $table->unique(['type', 'year']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('number_sequences');
    }
}
