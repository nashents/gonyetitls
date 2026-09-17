<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds Pending/Approved/Rejected Returns subs under each of the three
 * existing Goods Returned modules (gr-assets, gr-inventory, gr-tyres, which
 * today only have a single "Manage Goods Returned" sub each), mirroring the
 * equivalent GRV Pending/Approved/Rejected entries and reusing the same
 * per-department visibility gates as those.
 */
class SeedGoodsReturnedPendingApprovedRejectedMenu extends Migration
{
    public function up()
    {
        $assetVis = [
            'any' => [
                ['all_flags' => ['hasFinanceDeptHead']],
                ['all_flags' => ['isAdmin', 'inFinance']],
                ['all_flags' => ['isAdmin', 'inStores']],
                ['all_flags' => ['isSuperAdmin']],
            ],
        ];

        $storesVis = [
            'any' => [
                ['all_flags' => ['isAdmin']],
                ['all_flags' => ['hasStoresDeptHead']],
                ['all_flags' => ['isSuperAdmin']],
            ],
        ];

        $entries = [
            ['module_slug' => 'gr-assets', 'slug' => 'pending-gr-assets', 'name' => 'Pending Returns', 'icon' => 'fas fa-clock', 'route_name' => 'goods_returneds.pending', 'sort_order' => 20, 'visibility' => $assetVis],
            ['module_slug' => 'gr-assets', 'slug' => 'approved-gr-assets', 'name' => 'Approved Returns', 'icon' => 'fas fa-check', 'route_name' => 'goods_returneds.approved', 'sort_order' => 30, 'visibility' => $assetVis],
            ['module_slug' => 'gr-assets', 'slug' => 'rejected-gr-assets', 'name' => 'Rejected Returns', 'icon' => 'fas fa-ban', 'route_name' => 'goods_returneds.rejected', 'sort_order' => 40, 'visibility' => $assetVis],

            ['module_slug' => 'gr-inventory', 'slug' => 'pending-gr-inventory', 'name' => 'Pending Returns', 'icon' => 'fas fa-clock', 'route_name' => 'goods_returneds.pending', 'sort_order' => 20, 'visibility' => $storesVis],
            ['module_slug' => 'gr-inventory', 'slug' => 'approved-gr-inventory', 'name' => 'Approved Returns', 'icon' => 'fas fa-check', 'route_name' => 'goods_returneds.approved', 'sort_order' => 30, 'visibility' => $storesVis],
            ['module_slug' => 'gr-inventory', 'slug' => 'rejected-gr-inventory', 'name' => 'Rejected Returns', 'icon' => 'fas fa-ban', 'route_name' => 'goods_returneds.rejected', 'sort_order' => 40, 'visibility' => $storesVis],

            ['module_slug' => 'gr-tyres', 'slug' => 'pending-gr-tyres', 'name' => 'Pending Returns', 'icon' => 'fas fa-clock', 'route_name' => 'goods_returneds.pending', 'sort_order' => 20, 'visibility' => $storesVis],
            ['module_slug' => 'gr-tyres', 'slug' => 'approved-gr-tyres', 'name' => 'Approved Returns', 'icon' => 'fas fa-check', 'route_name' => 'goods_returneds.approved', 'sort_order' => 30, 'visibility' => $storesVis],
            ['module_slug' => 'gr-tyres', 'slug' => 'rejected-gr-tyres', 'name' => 'Rejected Returns', 'icon' => 'fas fa-ban', 'route_name' => 'goods_returneds.rejected', 'sort_order' => 40, 'visibility' => $storesVis],
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
                    'icon' => $entry['icon'],
                    'route_name' => $entry['route_name'],
                    'sort_order' => $entry['sort_order'],
                    'is_active' => true,
                    'badge_key' => null,
                    'visibility' => json_encode($entry['visibility']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down()
    {
        DB::table('sub_modules')->whereIn('slug', [
            'pending-gr-assets', 'approved-gr-assets', 'rejected-gr-assets',
            'pending-gr-inventory', 'approved-gr-inventory', 'rejected-gr-inventory',
            'pending-gr-tyres', 'approved-gr-tyres', 'rejected-gr-tyres',
        ])->delete();
    }
}
