<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tracked items can count down in several units at once (e.g. TURBO at
 * 10000 HRS or 150000 KM, whichever comes first): one interval and one
 * "due soon" value per unit, and interval_unit becomes a comma list
 * ("hours,km"). Existing single-unit values are copied across.
 */
return new class extends Migration
{
    protected const UNITS = ['hours', 'km', 'days'];

    public function up(): void
    {
        Schema::table('maintenance_items', function (Blueprint $table) {
            foreach (self::UNITS as $unit) {
                if (!Schema::hasColumn('maintenance_items', "interval_{$unit}")) {
                    $table->decimal("interval_{$unit}", 12, 2)->nullable();
                }
                if (!Schema::hasColumn('maintenance_items', "warning_{$unit}")) {
                    $table->decimal("warning_{$unit}", 12, 2)->nullable();
                }
            }
        });

        DB::statement("ALTER TABLE maintenance_items MODIFY interval_unit VARCHAR(30) NOT NULL DEFAULT 'hours'");

        if (Schema::hasColumn('maintenance_items', 'interval_value')) {
            foreach (self::UNITS as $unit) {
                DB::table('maintenance_items')->where('interval_unit', $unit)->update([
                    "interval_{$unit}" => DB::raw('interval_value'),
                    "warning_{$unit}" => DB::raw('warning_value'),
                ]);
            }

            Schema::table('maintenance_items', function (Blueprint $table) {
                $table->dropColumn(['interval_value', 'warning_value']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('maintenance_items', function (Blueprint $table) {
            $table->decimal('interval_value', 12, 2)->nullable();
            $table->decimal('warning_value', 12, 2)->nullable();
        });

        // Keep the first unit of each item.
        foreach (self::UNITS as $unit) {
            DB::table('maintenance_items')->where('interval_unit', 'like', "{$unit}%")->update([
                'interval_value' => DB::raw("interval_{$unit}"),
                'warning_value' => DB::raw("warning_{$unit}"),
                'interval_unit' => $unit,
            ]);
        }

        Schema::table('maintenance_items', function (Blueprint $table) {
            $table->dropColumn(['interval_hours', 'interval_km', 'interval_days', 'warning_hours', 'warning_km', 'warning_days']);
        });
    }
};
