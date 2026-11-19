<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Fitness::$fillable was missing company_id, so reminders created through the
 * reminder screens were saved without a company (and their reminder emails
 * failed on $company->noreply). Fill it from the creator's employee company,
 * else the only/first company.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            UPDATE fitnesses f
            JOIN users u ON u.id = f.user_id
            JOIN employees e ON e.user_id = u.id
            SET f.company_id = e.company_id
            WHERE f.company_id IS NULL AND e.company_id IS NOT NULL
        ");

        $fallback = DB::table('companies')->orderBy('id')->value('id');
        if ($fallback) {
            DB::table('fitnesses')->whereNull('company_id')->update(['company_id' => $fallback]);
        }
    }

    public function down(): void
    {
        // Data repair - nothing to undo.
    }
};
