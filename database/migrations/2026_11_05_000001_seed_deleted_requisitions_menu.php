<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Deleted Requisitions under Requisitions (Super Admin only — the page itself
 * 403s for anyone else). Badge = requisitions deleted today, from
 * SidebarComposer's menuBadges.
 */
class SeedDeletedRequisitionsMenu extends Migration
{
    private const ROUTE = 'requisitions.deleted';

    public function up()
    {
        $moduleId = DB::table('modules')->where('slug', 'requisitions')->value('id');
        if (! $moduleId) {
            return;
        }

        if (DB::table('sub_modules')->where('module_id', $moduleId)->where('route_name', self::ROUTE)->exists()) {
            return;
        }

        DB::table('sub_modules')->insert([
            'module_id' => $moduleId,
            'slug' => 'deleted-requisitions',
            'name' => 'Deleted Requisitions',
            'icon' => 'fas fa-trash',
            'route_name' => self::ROUTE,
            'sort_order' => 50,
            'badge_key' => 'requisitions_deleted_count',
            'is_active' => true,
            'visibility' => json_encode(['any' => [['all_flags' => ['isSuperAdmin']]]]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        DB::table('sub_modules')->where('route_name', self::ROUTE)->delete();
    }
}
