<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Leave, payroll and attendance now use ONLY the department flagged
 * department_employee.is_default. Make sure every employee who belongs to a
 * department has exactly one default: none flagged -> earliest assignment;
 * several flagged -> keep the earliest flagged one.
 */
return new class extends Migration
{
    public function up(): void
    {
        $rows = DB::table('department_employee')
            ->whereNotNull('employee_id')
            ->orderBy('id')
            ->get(['id', 'employee_id', 'is_default'])
            ->groupBy('employee_id');

        foreach ($rows as $employeeId => $assignments) {
            if ($assignments->where('is_default', true)->count() === 1) {
                continue;
            }

            $keep = $assignments->firstWhere('is_default', true) ?? $assignments->first();

            DB::table('department_employee')->where('employee_id', $employeeId)->update(['is_default' => false]);
            DB::table('department_employee')->where('id', $keep->id)->update(['is_default' => true]);
        }
    }

    public function down(): void
    {
        // Data repair only.
    }
};
