<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Freight Forwarding & Clearing should not appear for anyone by default,
 * regardless of department/company-type/SuperAdmin flags — `is_active`
 * is a hard gate the sidebar checks before any visibility JSON is even
 * evaluated. An is_admin (isSystemAdmin) user can turn it back on via the
 * Modules admin screen (app/Http/Livewire/Modules/Index.php), which
 * already cascades group->modules->sub_modules exactly like this does.
 */
class DeactivateFreightModuleByDefault extends Migration
{
    public function up()
    {
        $groupId = DB::table('module_groups')->where('slug', 'freight-forwarding')->value('id');

        if (!$groupId) {
            return;
        }

        DB::table('module_groups')->where('id', $groupId)->update([
            'is_active' => false,
            'is_customized' => true,
            'customized_at' => now(),
            'updated_at' => now(),
        ]);

        $moduleIds = DB::table('modules')->where('module_group_id', $groupId)->pluck('id');

        DB::table('modules')->where('module_group_id', $groupId)->update([
            'is_active' => false,
            'is_customized' => true,
            'customized_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('sub_modules')->whereIn('module_id', $moduleIds)->update([
            'is_active' => false,
            'is_customized' => true,
            'customized_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        $groupId = DB::table('module_groups')->where('slug', 'freight-forwarding')->value('id');

        if (!$groupId) {
            return;
        }

        DB::table('module_groups')->where('id', $groupId)->update(['is_active' => true, 'updated_at' => now()]);

        $moduleIds = DB::table('modules')->where('module_group_id', $groupId)->pluck('id');

        DB::table('modules')->where('module_group_id', $groupId)->update(['is_active' => true, 'updated_at' => now()]);
        DB::table('sub_modules')->whereIn('module_id', $moduleIds)->update(['is_active' => true, 'updated_at' => now()]);
    }
}
