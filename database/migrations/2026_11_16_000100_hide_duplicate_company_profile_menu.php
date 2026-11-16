<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Business Settings had two "Company Profile" entries for system admins: the
 * dropdown listing every company (system admins only) and the plain link to
 * the user's own company, which inherited the group's visibility and so
 * showed to system admins as well. The plain link is now for everyone except
 * system admins, leaving each user exactly one.
 */
class HideDuplicateCompanyProfileMenu extends Migration
{
    public function up()
    {
        DB::table('modules')->where('slug', 'company-profile')->update([
            'visibility' => json_encode([
                'any' => [
                    ['none_flags' => ['isSystemAdmin']],
                ],
            ]),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        DB::table('modules')->where('slug', 'company-profile')->update([
            'visibility' => null,
            'updated_at' => now(),
        ]);
    }
}
