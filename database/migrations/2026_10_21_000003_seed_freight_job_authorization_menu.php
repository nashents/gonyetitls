<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Pending / Approved / Rejected Freight Jobs under Freight Forwarding &
 * Clearing > Freight Jobs, matching every other authorized module (Trips,
 * GRVs, ...). Badge counts come from SidebarComposer's menuBadges.
 */
class SeedFreightJobAuthorizationMenu extends Migration
{
    private const ITEMS = [
        ['slug' => 'pending-freight-jobs', 'name' => 'Pending Jobs', 'icon' => 'fas fa-hourglass-half', 'route_name' => 'freight.jobs.pending', 'sort_order' => 30, 'badge_key' => 'freight_jobs_pending_count'],
        ['slug' => 'approved-freight-jobs', 'name' => 'Approved Jobs', 'icon' => 'fas fa-check', 'route_name' => 'freight.jobs.approved', 'sort_order' => 40, 'badge_key' => 'freight_jobs_approved_count'],
        ['slug' => 'rejected-freight-jobs', 'name' => 'Rejected Jobs', 'icon' => 'fas fa-times', 'route_name' => 'freight.jobs.rejected', 'sort_order' => 50, 'badge_key' => 'freight_jobs_rejected_count'],
    ];

    public function up()
    {
        $moduleId = DB::table('modules')->where('slug', 'freight-jobs')->value('id');
        if (! $moduleId) {
            return;
        }

        foreach (self::ITEMS as $item) {
            if (DB::table('sub_modules')->where('module_id', $moduleId)->where('route_name', $item['route_name'])->exists()) {
                continue;
            }

            DB::table('sub_modules')->insert($item + [
                'module_id' => $moduleId,
                'is_active' => true,
                'visibility' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        DB::table('sub_modules')->whereIn('route_name', array_column(self::ITEMS, 'route_name'))->delete();
    }
}
