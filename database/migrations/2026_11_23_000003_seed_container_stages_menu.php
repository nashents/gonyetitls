<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds "Container Stages" under the Freight "Master" menu module, inheriting
 * the freight group's inFreight/companyIsFreightForwarder/isSuperAdmin gate.
 */
class SeedContainerStagesMenu extends Migration
{
    public function up()
    {
        $masterId = DB::table('modules')->where('slug', 'freight-master')->value('id');

        if (!$masterId || DB::table('sub_modules')->where('slug', 'freight-container-stages')->exists()) {
            return;
        }

        DB::table('sub_modules')->insert([
            'module_id' => $masterId,
            'slug' => 'freight-container-stages',
            'name' => 'Container Stages',
            'icon' => 'fas fa-stream',
            'route_name' => 'freight.settings.container-stages',
            'sort_order' => 25,
            'is_active' => true,
            'visibility' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        DB::table('sub_modules')->where('slug', 'freight-container-stages')->delete();
    }
}
