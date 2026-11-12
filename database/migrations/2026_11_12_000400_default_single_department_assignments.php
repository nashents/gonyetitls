<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * An employee or driver who belongs to exactly one department must have that
 * department flagged as their default (department_employee.is_default) -
 * leave, payroll and attendance read only the default department.
 *
 * Flags the lone assignment wherever it is not flagged yet. Employees with
 * several departments are left alone (2026_11_11_000100 already guarantees
 * they have one default, and the choice is now made in the Change Position
 * modal). Safe to re-run: it only touches unflagged single assignments.
 */
return new class extends Migration
{
    public function up(): void
    {
        $singleAssignmentIds = DB::table('department_employee')
            ->whereNotNull('employee_id')
            ->groupBy('employee_id')
            ->havingRaw('COUNT(*) = 1')
            ->selectRaw('MIN(id) as id')
            ->pluck('id');

        foreach ($singleAssignmentIds->chunk(1000) as $ids) {
            DB::table('department_employee')
                ->whereIn('id', $ids)
                ->where(function ($q) {
                    $q->where('is_default', false)->orWhereNull('is_default');
                })
                ->update(['is_default' => true]);
        }
    }

    public function down(): void
    {
        // Data repair only.
    }
};
