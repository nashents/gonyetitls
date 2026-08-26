<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds a "Freight Forwarding & Clearing" entry to the DB-driven sidebar menu
 * registry (module_groups/modules/sub_modules, see app/Support/Menu.php),
 * gated by the same all_flags visibility convention used by every other
 * module group (e.g. trip-management -> inTransport).
 */
class SeedFreightMenuRegistry extends Migration
{
    public function up()
    {
        $groupId = DB::table('module_groups')->insertGetId([
            'slug' => 'freight-forwarding',
            'name' => 'Freight Forwarding & Clearing',
            'icon' => 'fas fa-ship',
            'sort_order' => 46,
            'is_active' => true,
            'visibility' => json_encode([
                'any' => [
                    ['all_flags' => ['inFreight']],
                    ['all_flags' => ['isSuperAdmin']],
                ],
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $moduleId = DB::table('modules')->insertGetId([
            'module_group_id' => $groupId,
            'slug' => 'freight-jobs',
            'name' => 'Freight Jobs',
            'icon' => 'fas fa-box',
            'route_name' => 'freight.jobs.*',
            'sort_order' => 10,
            'is_active' => true,
            'visibility' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('sub_modules')->insert([
            [
                'module_id' => $moduleId,
                'slug' => 'create-freight-job',
                'name' => 'Create Job',
                'icon' => 'fas fa-plus',
                'route_name' => 'freight.jobs.create',
                'sort_order' => 10,
                'is_active' => true,
                'visibility' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'module_id' => $moduleId,
                'slug' => 'manage-freight-jobs',
                'name' => 'Manage Jobs',
                'icon' => 'fas fa-list',
                'route_name' => 'freight.jobs.index',
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
        $groupId = DB::table('module_groups')->where('slug', 'freight-forwarding')->value('id');

        if ($groupId) {
            $moduleIds = DB::table('modules')->where('module_group_id', $groupId)->pluck('id');
            DB::table('sub_modules')->whereIn('module_id', $moduleIds)->delete();
            DB::table('modules')->where('module_group_id', $groupId)->delete();
            DB::table('module_groups')->where('id', $groupId)->delete();
        }
    }
}
