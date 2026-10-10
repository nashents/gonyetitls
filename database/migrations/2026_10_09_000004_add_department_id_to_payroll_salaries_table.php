<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Snapshot of the employee's default department at the time the payroll run
 * was built, so department cost reports don't shift when an employee later
 * moves department. Existing lines are backfilled from the current default.
 */
class AddDepartmentIdToPayrollSalariesTable extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('payroll_salaries', 'department_id')) {
            Schema::table('payroll_salaries', function (Blueprint $table) {
                $table->unsignedBigInteger('department_id')->nullable()->after('employee_id');
                $table->index('department_id');
            });
        }

        $defaults = DB::table('department_employee')
            ->where('is_default', true)
            ->whereNull('deleted_at')
            ->pluck('department_id', 'employee_id');

        foreach ($defaults as $employeeId => $departmentId) {
            DB::table('payroll_salaries')
                ->whereNull('department_id')
                ->where('employee_id', $employeeId)
                ->update(['department_id' => $departmentId]);
        }
    }

    public function down()
    {
        if (Schema::hasColumn('payroll_salaries', 'department_id')) {
            Schema::table('payroll_salaries', function (Blueprint $table) {
                $table->dropIndex(['department_id']);
                $table->dropColumn('department_id');
            });
        }
    }
}
