<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// The create migration was edited after it had already run in some environments,
// leaving those tables without these columns / with NOT NULL equipment ids.
return new class extends Migration
{
    public function up(): void
    {
        $columns = [
            'service_type_id' => fn (Blueprint $t) => $t->bigInteger('service_type_id')->unsigned()->nullable()->after('asset_id'),
            'problem_category_id' => fn (Blueprint $t) => $t->bigInteger('problem_category_id')->unsigned()->nullable()->after('asset_id'),
            'inspection_schedule_number' => fn (Blueprint $t) => $t->string('inspection_schedule_number')->nullable()->after('asset_id'),
            'type' => fn (Blueprint $t) => $t->string('type')->nullable()->after('asset_id'),
        ];

        foreach ($columns as $name => $add) {
            if (!Schema::hasColumn('inspection_schedules', $name)) {
                Schema::table('inspection_schedules', $add);
            }
        }

        // Older versions had these NOT NULL; a schedule targets exactly one of them.
        foreach (['horse_id', 'vehicle_id', 'trailer_id', 'asset_id'] as $column) {
            DB::statement("ALTER TABLE inspection_schedules MODIFY {$column} BIGINT UNSIGNED NULL");
        }
    }

    public function down(): void
    {
        // No-op: these columns belong to the create migration.
    }
};
