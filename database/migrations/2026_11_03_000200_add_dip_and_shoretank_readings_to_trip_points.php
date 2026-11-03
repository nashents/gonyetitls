<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per loading / offloading point liquid readings on the Trips Create/Edit
 * multi-point section: dips (ambient + 20°C) on both, shoretank readings
 * (ambient + 20°C) on offloading points only. litreage / litreage_at_20
 * stay the meter readings.
 */
return new class extends Migration
{
    private const COLUMNS = [
        'trip_origins'      => ['dip_litreage', 'dip_litreage_at_20'],
        'trip_destinations' => ['dip_litreage', 'dip_litreage_at_20', 'shoretank_litreage', 'shoretank_litreage_at_20'],
    ];

    public function up()
    {
        foreach (self::COLUMNS as $table => $columns) {
            Schema::table($table, function (Blueprint $t) use ($table, $columns) {
                foreach ($columns as $column) {
                    if (! Schema::hasColumn($table, $column)) {
                        $t->string($column)->nullable();
                    }
                }
            });
        }
    }

    public function down()
    {
        foreach (self::COLUMNS as $table => $columns) {
            Schema::table($table, function (Blueprint $t) use ($table, $columns) {
                foreach ($columns as $column) {
                    if (Schema::hasColumn($table, $column)) {
                        $t->dropColumn($column);
                    }
                }
            });
        }
    }
};
