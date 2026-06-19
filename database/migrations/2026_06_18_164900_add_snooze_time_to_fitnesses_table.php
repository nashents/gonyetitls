<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSnoozeTimeToFitnessesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fitnesses', function (Blueprint $table) {
            $table->timestamp('snooze_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fitnesses', function (Blueprint $table) {
           $table->dropColumn('snooze_time')->nullable();
        });
    }
}
