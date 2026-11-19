<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cherry-picked copy-list recipients per reminder. A reminder with cc = true
 * and NO rows here goes to everyone active on the owner's copy list (the
 * original behaviour); with rows, only to those people.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('fitness_reminder_copy')) {
            return;
        }

        Schema::create('fitness_reminder_copy', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('fitness_id')->unsigned();
            $table->foreign('fitness_id')->references('id')->on('fitnesses')->onDelete('cascade');
            $table->bigInteger('reminder_copy_id')->unsigned();
            $table->foreign('reminder_copy_id')->references('id')->on('reminder_copies')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['fitness_id', 'reminder_copy_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fitness_reminder_copy');
    }
};
