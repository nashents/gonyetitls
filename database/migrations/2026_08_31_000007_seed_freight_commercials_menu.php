<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds "Charge Types" and "Rate Cards" modules under the existing "Freight
 * Forwarding & Clearing" menu group (seeded in
 * 2026_08_27_000010_seed_freight_menu_registry.php), same
 * inFreight/isSuperAdmin visibility gate as every prior freight menu entry.
 */
class SeedFreightCommercialsMenu extends Migration
{
    public function up()
    {
        $groupId = DB::table('module_groups')->where('slug', 'freight-forwarding')->value('id');

        if (!$groupId) {
            return;
        }

        DB::table('modules')->insert([
            [
                'module_group_id' => $groupId,
                'slug' => 'freight-charge-types',
                'name' => 'Charge Types',
                'icon' => 'fas fa-tags',
                'route_name' => 'freight.settings.charge-types',
                'sort_order' => 40,
                'is_active' => true,
                'visibility' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'module_group_id' => $groupId,
                'slug' => 'freight-rate-cards',
                'name' => 'Rate Cards',
                'icon' => 'fas fa-file-invoice-dollar',
                'route_name' => 'freight.settings.rate-cards',
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
        DB::table('modules')->whereIn('slug', ['freight-charge-types', 'freight-rate-cards'])->delete();
    }
}
