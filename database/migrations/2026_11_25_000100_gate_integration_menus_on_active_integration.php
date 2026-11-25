<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Menu items tied to an integration must disappear when that integration is
 * inactive — for everyone, Super Admins included:
 *  - Sage Reconciliation: was inheriting Business Settings (null), so every
 *    admin saw it with or without Sage.
 *  - Live Fleet Map / Asset Positions: Super Admins bypassed the tracking check.
 * Mirrors MenuRegistrySeeder (which rewrites these on its next run anyway).
 */
class GateIntegrationMenusOnActiveIntegration extends Migration
{
    public function up()
    {
        $sage = ['any' => [
            ['all_flags' => ['isSystemAdmin', 'hasSageIntegration']],
            ['all_flags' => ['isSuperAdmin', 'hasSageIntegration']],
        ]];

        $tracking = ['any' => [
            ['all_flags' => ['isNotDriver', 'inTransport', 'hasTrackingIntegration']],
            ['all_flags' => ['isNotDriver', 'inWorkshop', 'hasTrackingIntegration']],
            ['all_flags' => ['isSuperAdmin', 'hasTrackingIntegration']],
        ]];

        DB::table('modules')->where('slug', 'sage-reconciliation')
            ->update(['visibility' => json_encode($sage), 'updated_at' => now()]);

        DB::table('modules')->whereIn('slug', ['live-fleet-map', 'asset-positions'])
            ->update(['visibility' => json_encode($tracking), 'updated_at' => now()]);
    }

    public function down()
    {
        $tracking = ['any' => [
            ['all_flags' => ['isNotDriver', 'inTransport', 'hasTrackingIntegration']],
            ['all_flags' => ['isNotDriver', 'inWorkshop', 'hasTrackingIntegration']],
            ['all_flags' => ['isSuperAdmin']],
        ]];

        DB::table('modules')->where('slug', 'sage-reconciliation')
            ->update(['visibility' => null, 'updated_at' => now()]);

        DB::table('modules')->whereIn('slug', ['live-fleet-map', 'asset-positions'])
            ->update(['visibility' => json_encode($tracking), 'updated_at' => now()]);
    }
}
