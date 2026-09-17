<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * The (user_id, month, year) unique constraint added in
 * 2026_06_25_000019_add_unique_constraint_to_payrolls_table.php was meant to
 * stop duplicate payroll batches, but payrolls.user_id is the admin who ran
 * payroll (Auth::id() in PayrollBatchService), not the employee being paid.
 * That means the SAME admin can never create a second payroll run in the
 * same calendar month — breaking as soon as there's more than one run in a
 * month (a different pay-frequency group, a correction run, etc.), with a
 * duplicate-entry 500 on "New Payroll Run".
 *
 * PayrollBatchService::buildBatch() already guarantees exactly one Payroll
 * row per PayrollRun (it looks up $run->payrolls()->first() before deciding
 * to create vs rebuild), so the real invariant to enforce at the DB level is
 * uniqueness per payroll_run_id — not per admin/month/year. payroll_run_id is
 * nullable for legacy rows predating the run-based flow; MySQL unique indexes
 * don't collide on NULL, so those are unaffected.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Defensively dedupe any existing rows sharing a payroll_run_id
        // (keeping the latest), same approach as the migration this replaces.
        DB::statement('
            DELETE p1 FROM payrolls p1
            INNER JOIN payrolls p2
                ON  p1.payroll_run_id = p2.payroll_run_id
                AND p1.id             < p2.id
            WHERE p1.payroll_run_id IS NOT NULL
        ');

        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropUnique('payrolls_user_month_year_unique');
            $table->unique('payroll_run_id', 'payrolls_payroll_run_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropUnique('payrolls_payroll_run_id_unique');
            $table->unique(
                ['user_id', 'month', 'year'],
                'payrolls_user_month_year_unique'
            );
        });
    }
};
