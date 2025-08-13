<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOpenHoursToShiftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->string('open_hours')->nullable();
            $table->string('close_hours')->nullable();
            $table->string('actual_hours')->nullable();
            $table->string('calculated_hours')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn('open_hours');
            $table->dropColumn('close_hours');
            $table->dropColumn('actual_hours');
            $table->dropColumn('calculated_hours');
        });
    }
}
