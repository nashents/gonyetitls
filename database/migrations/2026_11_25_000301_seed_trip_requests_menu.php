<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Trip Management > Trips > Trip Requests (Gonyeti Instance Link, transport
 * side). Same row MenuRegistrySeeder upserts; only visible to companies with
 * an active link in role "transport" (hasTransportLinkIntegration).
 */
class SeedTripRequestsMenu extends Migration
{
    public function up()
    {
        $groupId = DB::table('module_groups')->where('slug', 'trip-management')->value('id');
        $moduleId = $groupId ? DB::table('modules')->where('module_group_id', $groupId)->where('slug', 'trips')->value('id') : null;
        if (!$moduleId) {
            return;
        }

        $values = [
            'name' => 'Trip Requests',
            'icon' => 'fas fa-inbox',
            'route_name' => 'trip_requests.index',
            'badge_key' => 'trip_requests_pending_count',
            'sort_order' => 25,
            'is_active' => true,
            'visibility' => json_encode(['any' => [['all_flags' => ['hasTransportLinkIntegration', 'isNotDriver']]]]),
            'updated_at' => now(),
        ];

        $exists = DB::table('sub_modules')->where('module_id', $moduleId)->where('slug', 'trip-requests')->exists();
        DB::table('sub_modules')->updateOrInsert(
            ['module_id' => $moduleId, 'slug' => 'trip-requests'],
            $values + ($exists ? [] : ['created_at' => now()])
        );
    }

    public function down()
    {
        DB::table('sub_modules')->where('slug', 'trip-requests')->where('route_name', 'trip_requests.index')->delete();
    }
}
