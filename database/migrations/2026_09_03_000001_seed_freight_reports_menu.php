<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds a "Reports" module (5 sub_modules) under the existing "Freight
 * Forwarding & Clearing" menu group, same pattern as
 * 2026_09_02_000002_reorganize_freight_settings_under_master_menu.php:
 * a parent module with a blank route_name (non-clickable dropdown header)
 * housing screens as its sub_modules, visibility inherited (null) from
 * the group's existing inFreight/companyIsFreightForwarder/isSuperAdmin gate.
 */
class SeedFreightReportsMenu extends Migration
{
    public function up()
    {
        $groupId = DB::table('module_groups')->where('slug', 'freight-forwarding')->value('id');

        if (!$groupId) {
            return;
        }

        $reportsModuleId = DB::table('modules')->insertGetId([
            'module_group_id' => $groupId,
            'slug' => 'freight-reports',
            'name' => 'Reports',
            'icon' => 'fas fa-chart-bar',
            'route_name' => '',
            'sort_order' => 60,
            'is_active' => true,
            'visibility' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('sub_modules')->insert([
            [
                'module_id' => $reportsModuleId,
                'slug' => 'freight-report-job-profitability',
                'name' => 'Job Profitability',
                'icon' => 'fas fa-chart-line',
                'route_name' => 'freight.reports.job_profitability',
                'sort_order' => 10,
                'is_active' => true,
                'visibility' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'module_id' => $reportsModuleId,
                'slug' => 'freight-report-port-exposure',
                'name' => 'Port & Demurrage Exposure',
                'icon' => 'fas fa-warehouse',
                'route_name' => 'freight.reports.port_exposure',
                'sort_order' => 20,
                'is_active' => true,
                'visibility' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'module_id' => $reportsModuleId,
                'slug' => 'freight-report-customs-turnaround',
                'name' => 'Customs Turnaround Time',
                'icon' => 'fas fa-stopwatch',
                'route_name' => 'freight.reports.customs_turnaround',
                'sort_order' => 30,
                'is_active' => true,
                'visibility' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'module_id' => $reportsModuleId,
                'slug' => 'freight-report-unbilled-costs',
                'name' => 'Unbilled Costs Aging',
                'icon' => 'fas fa-file-invoice',
                'route_name' => 'freight.reports.unbilled_costs',
                'sort_order' => 40,
                'is_active' => true,
                'visibility' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'module_id' => $reportsModuleId,
                'slug' => 'freight-report-uninvoiced-charges',
                'name' => 'Uninvoiced Charges Aging',
                'icon' => 'fas fa-file-invoice-dollar',
                'route_name' => 'freight.reports.uninvoiced_charges',
                'sort_order' => 50,
                'is_active' => true,
                'visibility' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down()
    {
        $reportsModuleId = DB::table('modules')->where('slug', 'freight-reports')->value('id');

        if (!$reportsModuleId) {
            return;
        }

        DB::table('sub_modules')->where('module_id', $reportsModuleId)->delete();
        DB::table('modules')->where('id', $reportsModuleId)->delete();
    }
}
