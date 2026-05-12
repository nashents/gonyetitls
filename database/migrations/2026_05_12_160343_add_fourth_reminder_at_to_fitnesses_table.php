<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFourthReminderAtToFitnessesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fitnesses', function (Blueprint $table) {
            $table->string('fourth_reminder_at')->nullable();
            $table->boolean('fourth_reminder_at_status')->nullable()->default(0);
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
            $table->dropColumn('fourth_reminder_at');
            $table->dropColumn('fourth_reminder_at_status');
        });
    }
}
