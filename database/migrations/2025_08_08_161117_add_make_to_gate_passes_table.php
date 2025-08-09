<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMakeToGatePassesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gate_passes', function (Blueprint $table) {
             $table->string('vrn')->nullable();
             $table->string('make')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('gate_passes', function (Blueprint $table) {
             $table->dropColumn('vrn');
             $table->dropColumn('make');
        });
    }
}
