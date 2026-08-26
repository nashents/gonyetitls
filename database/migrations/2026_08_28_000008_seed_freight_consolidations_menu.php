<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds a "Consolidations" module under the existing "Freight Forwarding &
 * Clearing" menu group (seeded in 2026_08_27_000010_seed_freight_menu_registry.php),
 * same inFreight/isSuperAdmin visibility gate.
 */
class SeedFreightConsolidationsMenu extends Migration
{
    public function up()
    {
        $groupId = DB::table('module_groups')->where('slug', 'freight-forwarding')->value('id');

        if (!$groupId) {
            return;
        }

        $moduleId = DB::table('modules')->insertGetId([
            'module_group_id' => $groupId,
            'slug' => 'freight-consolidations',
            'name' => 'Consolidations',
            'icon' => 'fas fa-layer-group',
            'route_name' => 'freight.consolidations.*',
            'sort_order' => 20,
            'is_active' => true,
            'visibility' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('sub_modules')->insert([
            [
                'module_id' => $moduleId,
                'slug' => 'create-consolidation',
                'name' => 'Create Consolidation',
                'icon' => 'fas fa-plus',
                'route_name' => 'freight.consolidations.create',
                'sort_order' => 10,
                'is_active' => true,
                'visibility' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'module_id' => $moduleId,
                'slug' => 'manage-consolidations',
                'name' => 'Manage Consolidations',
                'icon' => 'fas fa-list',
                'route_name' => 'freight.consolidations.index',
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
        $moduleId = DB::table('modules')->where('slug', 'freight-consolidations')->value('id');

        if ($moduleId) {
            DB::table('sub_modules')->where('module_id', $moduleId)->delete();
            DB::table('modules')->where('id', $moduleId)->delete();
        }
    }
}
