<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tracked maintenance items (THERMOSTATS @ 15000 HRS, COOLANT @ 4000 HRS...):
 * the "set constants" a maintenance schedule tracks per horse/vehicle/trailer.
 * A schedule may override the item's default interval for one unit.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('maintenance_items')) {
            Schema::create('maintenance_items', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('category')->nullable();          // Engine, Drivetrain, Brakes...
                $table->string('interval_unit', 10)->default('hours'); // hours | km | days
                $table->decimal('interval_value', 12, 2);
                $table->decimal('warning_value', 12, 2)->nullable(); // "due soon" when remaining <= this
                $table->string('applies_to', 20)->default('all');    // all | horse | vehicle | trailer | asset
                $table->boolean('is_monitoring')->default(false);    // inspect/monitor rather than replace
                $table->text('description')->nullable();
                $table->boolean('status')->default(true);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        Schema::table('inspection_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('inspection_schedules', 'maintenance_item_id')) {
                $table->unsignedBigInteger('maintenance_item_id')->nullable()->after('service_type_id')->index();
            }
            if (!Schema::hasColumn('inspection_schedules', 'interval_hours')) {
                $table->unsignedInteger('interval_hours')->nullable()->after('interval_days');
            }
            if (!Schema::hasColumn('inspection_schedules', 'last_inspection_hours')) {
                $table->decimal('last_inspection_hours', 12, 1)->nullable()->after('last_inspection_km');
            }
            if (!Schema::hasColumn('inspection_schedules', 'next_due_hours')) {
                $table->decimal('next_due_hours', 12, 1)->nullable()->after('next_due_km');
            }
        });

        // The enums didn't allow an hour-meter trigger, and the form posts
        // "Active"/"InActive" - plain strings from here on.
        DB::statement("ALTER TABLE inspection_schedules MODIFY trigger_type VARCHAR(20) NULL DEFAULT 'both'");
        DB::statement("ALTER TABLE inspection_schedules MODIFY status VARCHAR(20) NOT NULL DEFAULT 'active'");
        DB::statement("UPDATE inspection_schedules SET trigger_type = LOWER(trigger_type), status = LOWER(status)");
    }

    public function down(): void
    {
        Schema::table('inspection_schedules', function (Blueprint $table) {
            foreach (['maintenance_item_id', 'interval_hours', 'last_inspection_hours', 'next_due_hours'] as $column) {
                if (Schema::hasColumn('inspection_schedules', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::dropIfExists('maintenance_items');
    }
};
