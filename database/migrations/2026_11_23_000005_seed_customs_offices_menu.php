<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds "Customs Offices" under the Freight "Master" menu module, inheriting
 * the freight group's inFreight/companyIsFreightForwarder/isSuperAdmin gate.
 */
class SeedCustomsOfficesMenu extends Migration
{
    public function up()
    {
        $masterId = DB::table('modules')->where('slug', 'freight-master')->value('id');

        if (!$masterId || DB::table('sub_modules')->where('slug', 'freight-customs-offices')->exists()) {
            return;
        }

        DB::table('sub_modules')->insert([
            'module_id' => $masterId,
            'slug' => 'freight-customs-offices',
            'name' => 'Customs Offices',
            'icon' => 'fas fa-landmark',
            'route_name' => 'freight.settings.customs-offices',
            'sort_order' => 60,
            'is_active' => true,
            'visibility' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        DB::table('sub_modules')->where('slug', 'freight-customs-offices')->delete();
    }
}
