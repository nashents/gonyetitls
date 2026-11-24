<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Attendance registers no longer go through authorization. MenuRegistrySeeder
 * no longer seeds the Pending/Approved/Rejected links, but the seeder only
 * upserts, so the existing sidebar rows are removed here.
 */
class RemoveAttendanceAuthorizationMenu extends Migration
{
    private const ROUTES = ['attendances.pending', 'attendances.approved', 'attendances.rejected'];

    public function up()
    {
        DB::table('sub_modules')->whereIn('route_name', self::ROUTES)->delete();
    }

    public function down()
    {
        // Not restored: the authorization screens were removed.
    }
}
