<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Removes the legacy "Payroll" menu module (Manage / Pending / Approved /
 * Rejected Payrolls) from the "Salaries & Payroll" group. Its payrolls.*
 * routes were dropped when the Payroll Runs module replaced it, so
 * Menu::href() fell back to javascript:void(0) and every link opened a
 * "blocked" page. MenuRegistrySeeder no longer seeds it either, but the
 * seeder only upserts, so the old rows were left behind.
 */
class RemoveLegacyPayrollMenu extends Migration
{
    public function up()
    {
        $moduleIds = DB::table('modules')
            ->where('slug', 'payroll')
            ->where('route_name', 'payrolls.*')
            ->pluck('id');

        if ($moduleIds->isEmpty()) {
            return;
        }

        DB::table('sub_modules')->whereIn('module_id', $moduleIds)->delete();
        DB::table('modules')->whereIn('id', $moduleIds)->delete();
    }

    public function down()
    {
        // Not restored: the payrolls.* routes these links pointed at no longer exist.
    }
}
