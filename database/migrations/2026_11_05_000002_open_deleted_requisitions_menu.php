<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Deleted Requisitions is now open to everyone (rows are scoped per user:
 * own / headed departments / Super Admin all), so drop the Super Admin-only
 * visibility and inherit the Requisitions module's (everyone).
 */
class OpenDeletedRequisitionsMenu extends Migration
{
    public function up()
    {
        DB::table('sub_modules')->where('route_name', 'requisitions.deleted')->update([
            'visibility' => null,
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        DB::table('sub_modules')->where('route_name', 'requisitions.deleted')->update([
            'visibility' => json_encode(['any' => [['all_flags' => ['isSuperAdmin']]]]),
            'updated_at' => now(),
        ]);
    }
}
