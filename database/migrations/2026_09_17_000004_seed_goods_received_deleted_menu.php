<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds a "Deleted GRVs" sub-module (recycle bin for soft-deleted goods
 * received vouchers) under each of the three GRV modules (Assets, Inventory,
 * Tyres), mirroring the existing Pending/Approved/Rejected GRV entries —
 * same visibility gate as that module's "Purchase Orders" group.
 */
class SeedGoodsReceivedDeletedMenu extends Migration
{
    public function up()
    {
        $entries = [
            [
                'module_slug' => 'grv-assets',
                'slug' => 'deleted-assets-grvs',
                'visibility' => [
                    'any' => [
                        ['all_flags' => ['hasFinanceDeptHead']],
                        ['all_flags' => ['isAdmin', 'inFinance']],
                        ['all_flags' => ['isAdmin', 'inStores']],
                        ['all_flags' => ['isSuperAdmin']],
                    ],
                ],
            ],
            [
                'module_slug' => 'grv-inventory',
                'slug' => 'deleted-inventory-grvs',
                'visibility' => [
                    'any' => [
                        ['all_flags' => ['isAdmin']],
                        ['all_flags' => ['hasStoresDeptHead']],
                        ['all_flags' => ['isSuperAdmin']],
                    ],
                ],
            ],
            [
                'module_slug' => 'grv-tyres',
                'slug' => 'deleted-tyre-grvs',
                'visibility' => [
                    'any' => [
                        ['all_flags' => ['isAdmin']],
                        ['all_flags' => ['hasStoresDeptHead']],
                        ['all_flags' => ['isSuperAdmin']],
                    ],
                ],
            ],
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
                    'name' => 'Deleted GRVs',
                    'icon' => 'fas fa-trash',
                    'route_name' => 'goods_receiveds.deleted',
                    'sort_order' => 50,
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
            'deleted-assets-grvs',
            'deleted-inventory-grvs',
            'deleted-tyre-grvs',
        ])->delete();
    }
}
