<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSelectPreviousDateToLeavesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('leaves', function (Blueprint $table) {
            $table->boolean('is_backdated')->default(false);
            $table->boolean('is_emergency')->default(false);
            $table->string('flag')->nullable();
            $table->string('score')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leaves', function (Blueprint $table) {
            $table->dropColumn('is_backdated');
            $table->dropColumn('is_emergency');
            $table->dropColumn('flag');
            $table->dropColumn('score');
        });
    }
}
