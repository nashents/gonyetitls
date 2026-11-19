<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * "Document Hand-overs" under Main Category, visible to everyone (each user
 * only sees hand-overs to/from themselves). Badge = documents waiting for
 * the user to confirm receipt (SidebarComposer menuBadges).
 */
class SeedDocumentHandoversMenu extends Migration
{
    private const ROUTE = 'documents.custody.mine';

    public function up()
    {
        $groupId = DB::table('module_groups')->where('slug', 'main-category')->value('id');
        if (! $groupId || DB::table('modules')->where('route_name', self::ROUTE)->exists()) {
            return;
        }

        DB::table('modules')->insert([
            'module_group_id' => $groupId,
            'name' => 'Document Hand-overs',
            'slug' => 'document-handovers',
            'icon' => 'fas fa-exchange-alt',
            'route_name' => self::ROUTE,
            'sort_order' => 33,
            'is_active' => true,
            'visibility' => null,
            'badge_key' => 'documents_awaiting_receipt',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        DB::table('modules')->where('route_name', self::ROUTE)->delete();
    }
}
