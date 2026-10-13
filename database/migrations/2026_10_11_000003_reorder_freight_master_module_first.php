<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Moves "Master" ahead of Freight Jobs/Consolidations/Reports/Import in the
 * Freight Forwarding & Clearing sidebar group, since master data (Charge
 * Types, Shipping Lines, Service Types, Charge Config, Rate Cards) is set up
 * before any job is ever created.
 */
class ReorderFreightMasterModuleFirst extends Migration
{
    public function up()
    {
        DB::table('modules')->where('slug', 'freight-master')->update([
            'sort_order' => 5,
            'is_customized' => true,
            'customized_at' => now(),
        ]);
    }

    public function down()
    {
        DB::table('modules')->where('slug', 'freight-master')->update([
            'sort_order' => 30,
        ]);
    }
}
