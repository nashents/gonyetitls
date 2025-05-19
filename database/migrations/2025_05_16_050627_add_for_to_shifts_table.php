<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForToShiftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shifts', function (Blueprint $table) {
             $table->string('shift_number')->nullable();
             $table->string('for')->nullable();
             $table->boolean('fuel_order')->default(0);
             $table->string('equipment')->nullable();
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
           $table->dropColumn('shift_number');
           $table->dropColumn('for');
           $table->dropColumn('fuel_order');
           $table->dropColumn('equipment');
        });
    }
}
