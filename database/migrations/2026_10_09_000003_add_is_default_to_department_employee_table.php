<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Employees can belong to several departments; one of them is flagged as the
 * default and is the department payroll costs are reported against.
 * Backfill: each employee's earliest assignment becomes the default.
 */
class AddIsDefaultToDepartmentEmployeeTable extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('department_employee', 'is_default')) {
            Schema::table('department_employee', function (Blueprint $table) {
                $table->boolean('is_default')->default(false)->after('employee_id');
            });
        }

        $firstIds = DB::table('department_employee')
            ->whereNotNull('employee_id')
            ->whereNotNull('department_id')
            ->whereNull('deleted_at')
            ->groupBy('employee_id')
            ->selectRaw('MIN(id) as id')
            ->pluck('id');

        foreach ($firstIds->chunk(500) as $chunk) {
            DB::table('department_employee')->whereIn('id', $chunk->all())->update(['is_default' => true]);
        }
    }

    public function down()
    {
        if (Schema::hasColumn('department_employee', 'is_default')) {
            Schema::table('department_employee', function (Blueprint $table) {
                $table->dropColumn('is_default');
            });
        }
    }
}
