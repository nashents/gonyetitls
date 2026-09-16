<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds "Service Types" as a sub_module under the existing "Master" menu
 * module (alongside Charge Config, Charge Types, Rate Cards, Shipping
 * Lines), same inFreight/companyIsFreightForwarder/isSuperAdmin gate
 * inherited via visibility => null.
 */
class SeedFreightServiceTypesMenu extends Migration
{
    public function up()
    {
        $masterId = DB::table('modules')->where('slug', 'freight-master')->value('id');

        if (!$masterId) {
            return;
        }

        DB::table('sub_modules')->insert([
            'module_id' => $masterId,
            'slug' => 'freight-service-types',
            'name' => 'Service Types',
            'icon' => 'fas fa-tags',
            'route_name' => 'freight.settings.service-types',
            'sort_order' => 50,
            'is_active' => true,
            'visibility' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        DB::table('sub_modules')->where('slug', 'freight-service-types')->delete();
    }
}
