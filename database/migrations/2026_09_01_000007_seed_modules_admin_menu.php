<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds a "Modules" entry under the existing "Business Settings" menu
 * group so an is_admin user can reach the module on/off toggles
 * (app/Http/Livewire/Modules/Index.php) — previously wired up but
 * unreachable (no sidebar link, and ModuleController::index() was a
 * stub). Gated strictly to isSystemAdmin (maps to User::is_admin()),
 * deliberately NOT OR'd with isSuperAdmin like most other menu entries,
 * per explicit instruction that this screen is is_admin-only.
 */
class SeedModulesAdminMenu extends Migration
{
    public function up()
    {
        $groupId = DB::table('module_groups')->where('slug', 'business-settings')->value('id');

        if (!$groupId) {
            return;
        }

        DB::table('modules')->insert([
            'module_group_id' => $groupId,
            'slug' => 'modules-admin',
            'name' => 'Modules',
            'icon' => 'fas fa-th-list',
            'route_name' => 'modules.index',
            'sort_order' => 30,
            'is_active' => true,
            'visibility' => json_encode(['all_flags' => ['isSystemAdmin']]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        DB::table('modules')->where('slug', 'modules-admin')->delete();
    }
}
