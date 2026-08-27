<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds a company-level gate on top of the existing department-level
 * (inFreight) gate for the whole Freight Forwarding & Clearing sidebar
 * group. Every module/sub-module under this group has visibility=null
 * (inherits from the group), so updating this one row gates the entire
 * freight sidebar section at once.
 */
class GateFreightMenuByCompanyType extends Migration
{
    public function up()
    {
        DB::table('module_groups')
            ->where('slug', 'freight-forwarding')
            ->update([
                'visibility' => json_encode([
                    'any' => [
                        ['all_flags' => ['inFreight', 'companyIsFreightForwarder']],
                        ['all_flags' => ['isSuperAdmin']],
                    ],
                ]),
                'updated_at' => now(),
            ]);
    }

    public function down()
    {
        DB::table('module_groups')
            ->where('slug', 'freight-forwarding')
            ->update([
                'visibility' => json_encode([
                    'any' => [
                        ['all_flags' => ['inFreight']],
                        ['all_flags' => ['isSuperAdmin']],
                    ],
                ]),
                'updated_at' => now(),
            ]);
    }
}
