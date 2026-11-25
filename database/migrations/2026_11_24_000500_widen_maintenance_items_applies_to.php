<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * maintenance_items.applies_to becomes a comma list of equipment types
 * ("horse,vehicle"); "all" still means every type.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE maintenance_items MODIFY applies_to VARCHAR(100) NOT NULL DEFAULT 'all'");
    }

    public function down(): void
    {
        // Leave the wider column: shrinking could truncate multi-type values.
    }
};
