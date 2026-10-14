<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * - dispatch_items.fit_type/axle/position: optional fitting position chosen at dispatch time.
 * - Data fix: a tyre with an active assignment is on a unit, not in stores (balance 0, status 0).
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('dispatch_items', function (Blueprint $table) {
            if (!Schema::hasColumn('dispatch_items', 'fit_type')) {
                $table->string('fit_type')->nullable()->after('tyre_id');
            }
            if (!Schema::hasColumn('dispatch_items', 'axle')) {
                $table->string('axle')->nullable()->after('fit_type');
            }
            if (!Schema::hasColumn('dispatch_items', 'position')) {
                $table->string('position')->nullable()->after('axle');
            }
        });

        $fitted = DB::table('tyre_assignments')
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->whereNotNull('tyre_id')
            ->pluck('tyre_id');

        DB::table('tyres')->whereIn('id', $fitted)->update(['balance' => 0, 'status' => 0]);
    }

    public function down()
    {
        Schema::table('dispatch_items', function (Blueprint $table) {
            foreach (['position', 'axle', 'fit_type'] as $column) {
                if (Schema::hasColumn('dispatch_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
