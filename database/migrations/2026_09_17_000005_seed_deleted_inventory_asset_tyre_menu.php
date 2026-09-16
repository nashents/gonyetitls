<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds a "Deleted" sub-module (recycle bin) under each of the Assets,
 * Inventory, and Tyres modules — mirrors the sibling "Manage"/"Disposed
 * Items" entries in those same modules; no visibility override, same as
 * those siblings (inherits from the module).
 */
class SeedDeletedInventoryAssetTyreMenu extends Migration
{
    public function up()
    {
        $entries = [
            ['module_slug' => 'assets', 'slug' => 'deleted-assets', 'name' => 'Deleted Assets', 'route_name' => 'assets.deleted', 'sort_order' => 30],
            ['module_slug' => 'inventory', 'slug' => 'deleted-inventory', 'name' => 'Deleted Inventory', 'route_name' => 'inventories.deleted', 'sort_order' => 40],
            ['module_slug' => 'tyres', 'slug' => 'deleted-tyres', 'name' => 'Deleted Tyres', 'route_name' => 'tyres.deleted', 'sort_order' => 50],
        ];

        foreach ($entries as $entry) {
            $moduleId = DB::table('modules')->where('slug', $entry['module_slug'])->value('id');

            if (! $moduleId) {
                continue;
            }

            DB::table('sub_modules')->updateOrInsert(
                ['module_id' => $moduleId, 'slug' => $entry['slug']],
                [
                    'module_id' => $moduleId,
                    'slug' => $entry['slug'],
                    'name' => $entry['name'],
                    'icon' => 'fas fa-trash',
                    'route_name' => $entry['route_name'],
                    'sort_order' => $entry['sort_order'],
                    'is_active' => true,
                    'badge_key' => null,
                    'visibility' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down()
    {
        DB::table('sub_modules')->whereIn('slug', [
            'deleted-assets',
            'deleted-inventory',
            'deleted-tyres',
        ])->delete();
    }
}
