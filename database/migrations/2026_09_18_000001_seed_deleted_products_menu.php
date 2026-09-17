<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds a "Deleted Products" sub-module (recycle bin) under each of the
 * Assets/Inventory/Tyre "Products" modules, alongside "Manage Products" —
 * lets staff check whether a currently-active product's name collides with
 * one that was previously deleted (duplicate-catalog-entry diagnosis).
 */
class SeedDeletedProductsMenu extends Migration
{
    public function up()
    {
        $entries = [
            ['module_slug' => 'asset-products', 'slug' => 'deleted-products', 'route_name' => 'products.deleted'],
            ['module_slug' => 'inventory-products', 'slug' => 'deleted-inventory-products', 'route_name' => 'inventory_products.deleted'],
            ['module_slug' => 'tyre-products', 'slug' => 'deleted-tyre-products', 'route_name' => 'tyre_products.deleted'],
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
                    'name' => 'Deleted Products',
                    'icon' => 'fas fa-trash',
                    'route_name' => $entry['route_name'],
                    'sort_order' => 30,
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
            'deleted-products',
            'deleted-inventory-products',
            'deleted-tyre-products',
        ])->delete();
    }
}
