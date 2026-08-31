<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds an "Import" module (2 sub_modules) under the existing "Freight
 * Forwarding & Clearing" menu group, same pattern as
 * 2026_09_03_000001_seed_freight_reports_menu.php. Menu visibility alone
 * is not an authorization control - the real gate for these screens is
 * the abort_unless(...is_admin()) check in FreightImportController and
 * each Livewire component's mount().
 */
class SeedFreightImportMenu extends Migration
{
    public function up()
    {
        $groupId = DB::table('module_groups')->where('slug', 'freight-forwarding')->value('id');

        if (!$groupId) {
            return;
        }

        $importModuleId = DB::table('modules')->insertGetId([
            'module_group_id' => $groupId,
            'slug' => 'freight-import',
            'name' => 'Import',
            'icon' => 'fas fa-file-import',
            'route_name' => '',
            'sort_order' => 70,
            'is_active' => true,
            'visibility' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('sub_modules')->insert([
            [
                'module_id' => $importModuleId,
                'slug' => 'freight-import-rate-cards',
                'name' => 'Rate Cards',
                'icon' => 'fas fa-file-invoice-dollar',
                'route_name' => 'freight.imports.rate_cards',
                'sort_order' => 10,
                'is_active' => true,
                'visibility' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'module_id' => $importModuleId,
                'slug' => 'freight-import-jobs',
                'name' => 'Freight Jobs',
                'icon' => 'fas fa-briefcase',
                'route_name' => 'freight.imports.jobs',
                'sort_order' => 20,
                'is_active' => true,
                'visibility' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down()
    {
        $importModuleId = DB::table('modules')->where('slug', 'freight-import')->value('id');

        if (!$importModuleId) {
            return;
        }

        DB::table('sub_modules')->where('module_id', $importModuleId)->delete();
        DB::table('modules')->where('id', $importModuleId)->delete();
    }
}
