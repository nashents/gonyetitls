<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCommentsAndFuelAllocationToShifts extends Migration
{
    /**
     * Shift comments + fuel order "Allocation To" (Long Haul / Short Haul) used by
     * the daily production report's long/short haul fuel rows.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('shifts', 'comments')) {
            Schema::table('shifts', function (Blueprint $table) {
                $table->text('comments')->nullable();
            });
        }

        if (!Schema::hasColumn('fuels', 'allocation_to')) {
            Schema::table('fuels', function (Blueprint $table) {
                $table->string('allocation_to')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn('comments');
        });

        Schema::table('fuels', function (Blueprint $table) {
            $table->dropColumn('allocation_to');
        });
    }
}
