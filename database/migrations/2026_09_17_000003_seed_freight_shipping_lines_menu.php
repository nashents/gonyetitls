<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds "Shipping Lines" as a sub_module under the existing "Master" menu
 * module (alongside Charge Config, Charge Types, Rate Cards), same
 * inFreight/companyIsFreightForwarder/isSuperAdmin gate inherited via
 * visibility => null.
 */
class SeedFreightShippingLinesMenu extends Migration
{
    public function up()
    {
        $masterId = DB::table('modules')->where('slug', 'freight-master')->value('id');

        if (!$masterId) {
            return;
        }

        DB::table('sub_modules')->insert([
            'module_id' => $masterId,
            'slug' => 'freight-shipping-lines',
            'name' => 'Shipping Lines',
            'icon' => 'fas fa-ship',
            'route_name' => 'freight.settings.shipping-lines',
            'sort_order' => 40,
            'is_active' => true,
            'visibility' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        DB::table('sub_modules')->where('slug', 'freight-shipping-lines')->delete();
    }
}
