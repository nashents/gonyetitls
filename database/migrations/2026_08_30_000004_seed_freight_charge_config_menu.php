<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds a "Charge Config" module under the existing "Freight Forwarding &
 * Clearing" menu group (seeded in 2026_08_27_000010_seed_freight_menu_registry.php),
 * same inFreight/isSuperAdmin visibility gate.
 */
class SeedFreightChargeConfigMenu extends Migration
{
    public function up()
    {
        $groupId = DB::table('module_groups')->where('slug', 'freight-forwarding')->value('id');

        if (!$groupId) {
            return;
        }

        DB::table('modules')->insert([
            'module_group_id' => $groupId,
            'slug' => 'freight-charge-config',
            'name' => 'Charge Config',
            'icon' => 'fas fa-sliders-h',
            'route_name' => 'freight.settings.charge-config',
            'sort_order' => 30,
            'is_active' => true,
            'visibility' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        DB::table('modules')->where('slug', 'freight-charge-config')->delete();
    }
}
