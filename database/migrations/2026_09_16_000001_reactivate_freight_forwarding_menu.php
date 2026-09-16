<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Re-activates the freight-forwarding module group (and cascades to every
 * module/sub_module under it), reversing the earlier default-off
 * deactivation (2026_09_01_000006_deactivate_freight_module_by_default.php).
 *
 * At the time, company-type-based gating didn't exist yet, so is_active
 * was used as a global safety kill-switch to stop the freight menu
 * showing to companies it shouldn't. That gap is now closed - the
 * group's own `visibility` JSON already requires
 * `companyIsFreightForwarder` (Company::hasCompanyType('Freight
 * Forwarding & Clearing')) alongside `inFreight`, or `isSuperAdmin` - so
 * per-company show/hide is correctly handled dynamically per request.
 * Leaving is_active permanently false now just blocks everyone
 * regardless of company type, which is the reported bug: adding the
 * company type to a company had no effect because the group-level gate
 * is checked before visibility flags.
 *
 * Also fixes an inconsistency: modules added after the deactivation
 * (Master, Reports, Import) were seeded with is_active=true by their own
 * migrations and were never swept up by the original cascade, while the
 * two original modules (Freight Jobs, Consolidations) stayed inactive -
 * this brings every module/sub_module under the group back in sync,
 * mirroring Modules\Index::toggleAllGroupItems($groupId, true)'s exact
 * cascade shape.
 */
class ReactivateFreightForwardingMenu extends Migration
{
    public function up()
    {
        $groupId = DB::table('module_groups')->where('slug', 'freight-forwarding')->value('id');

        if (!$groupId) {
            return;
        }

        DB::table('module_groups')->where('id', $groupId)->update([
            'is_active' => true,
            'is_customized' => true,
            'customized_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('modules')->where('module_group_id', $groupId)->update([
            'is_active' => true,
            'is_customized' => true,
            'customized_at' => now(),
            'updated_at' => now(),
        ]);

        $moduleIds = DB::table('modules')->where('module_group_id', $groupId)->pluck('id');

        DB::table('sub_modules')->whereIn('module_id', $moduleIds)->update([
            'is_active' => true,
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

        DB::table('module_groups')->where('id', $groupId)->update([
            'is_active' => false,
            'updated_at' => now(),
        ]);

        DB::table('modules')->where('module_group_id', $groupId)->update([
            'is_active' => false,
            'updated_at' => now(),
        ]);

        $moduleIds = DB::table('modules')->where('module_group_id', $groupId)->pluck('id');

        DB::table('sub_modules')->whereIn('module_id', $moduleIds)->update([
            'is_active' => false,
            'updated_at' => now(),
        ]);
    }
}
