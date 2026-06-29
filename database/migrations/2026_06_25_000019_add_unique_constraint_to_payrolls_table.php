<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Prevent duplicate payroll runs for the same user, month and year.
 * Deduplicates existing rows (keeping the highest id) before adding the constraint.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Delete duplicate rows, keeping the latest (highest id) per user/month/year
        DB::statement('
            DELETE p1 FROM payrolls p1
            INNER JOIN payrolls p2
                ON  p1.user_id = p2.user_id
                AND p1.month   = p2.month
                AND p1.year    = p2.year
                AND p1.id      < p2.id
        ');

        Schema::table('payrolls', function (Blueprint $table) {
            $table->unique(
                ['user_id', 'month', 'year'],
                'payrolls_user_month_year_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropUnique('payrolls_user_month_year_unique');
        });
    }
};
