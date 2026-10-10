<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds "Department Costs" under the Payroll Runs module. Visibility is null so
 * it inherits the module's HR/Admin/Super gate.
 */
class SeedPayrollDepartmentCostsMenu extends Migration
{
    public function up()
    {
        $moduleId = DB::table('modules')->where('slug', 'payroll-runs')->value('id');

        if (! $moduleId) {
            return;
        }

        DB::table('sub_modules')->updateOrInsert(
            ['module_id' => $moduleId, 'slug' => 'payroll-department-costs'],
            [
                'module_id' => $moduleId,
                'slug' => 'payroll-department-costs',
                'name' => 'Department Costs',
                'icon' => 'fas fa-sitemap',
                'route_name' => 'payroll-runs.department-costs',
                'sort_order' => 20,
                'is_active' => true,
                'badge_key' => null,
                'visibility' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down()
    {
        DB::table('sub_modules')->where('slug', 'payroll-department-costs')->delete();
    }
}
