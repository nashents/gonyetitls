<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Closes a gap in 2026_09_01_000005_gate_freight_menu_by_company_type.php:
 * its rule was `(inFreight AND companyIsFreightForwarder) OR isSuperAdmin`,
 * so the isSuperAdmin branch skipped the company-type check entirely and
 * Super Admins at Transporter-only companies still saw the whole Freight
 * Forwarding & Clearing sidebar group.
 *
 * The company type is now required for everyone:
 * `companyIsFreightForwarder AND (inFreight OR isSuperAdmin)`.
 * Every module/sub-module under the group inherits (visibility=null), so
 * this one row gates the entire section.
 */
class RequireFreightCompanyTypeForSuperAdmins extends Migration
{
    public function up()
    {
        DB::table('module_groups')
            ->where('slug', 'freight-forwarding')
            ->update([
                'visibility' => json_encode([
                    'all_flags' => ['companyIsFreightForwarder'],
                    'any_flags' => ['inFreight', 'isSuperAdmin'],
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
                        ['all_flags' => ['inFreight', 'companyIsFreightForwarder']],
                        ['all_flags' => ['isSuperAdmin']],
                    ],
                ]),
                'updated_at' => now(),
            ]);
    }
}
