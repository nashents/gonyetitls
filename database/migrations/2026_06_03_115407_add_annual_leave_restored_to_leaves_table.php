<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAnnualLeaveRestoredToLeavesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('leaves', function (Blueprint $table) {
            $table->boolean('annual_leave_restored')->default(false);
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
            $table->dropColumn('annual_leave_restored');
        });
    }
}
