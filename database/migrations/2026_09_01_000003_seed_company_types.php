<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the canonical company type list, including the new "Freight
 * Forwarding & Clearing" type. The first 4 names must match the legacy
 * companies.type dropdown values exactly (Admin/Rental/Broker/Transporter)
 * so the backfill migration can match them case-insensitively.
 */
class SeedCompanyTypes extends Migration
{
    public function up()
    {
        $names = ['Admin', 'Rental', 'Broker', 'Transporter', 'Freight Forwarding & Clearing'];

        foreach ($names as $name) {
            DB::table('company_types')->insert([
                'name' => $name,
                'is_locked' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        DB::table('company_types')->whereIn('name', [
            'Admin', 'Rental', 'Broker', 'Transporter', 'Freight Forwarding & Clearing',
        ])->delete();
    }
}
