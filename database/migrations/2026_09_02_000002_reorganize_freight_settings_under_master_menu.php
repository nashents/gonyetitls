<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Reorganizes the 3 freight settings screens (Charge Config, Charge Types,
 * Rate Cards) under one "Master" parent module, following this app's
 * existing system-wide convention (fleet-master, trip-master,
 * workshop-master, ...): a module literally named "Master", icon
 * fas fa-cog, blank route_name (non-clickable dropdown header — NOT a
 * wildcard), housing config/type screens as its sub_modules.
 */
class ReorganizeFreightSettingsUnderMasterMenu extends Migration
{
    private array $settingsSlugs = ['freight-charge-config', 'freight-charge-types', 'freight-rate-cards'];

    public function up()
    {
        $groupId = DB::table('module_groups')->where('slug', 'freight-forwarding')->value('id');

        if (!$groupId) {
            return;
        }

        $existing = DB::table('modules')
            ->where('module_group_id', $groupId)
            ->whereIn('slug', $this->settingsSlugs)
            ->get(['id', 'slug', 'name', 'icon', 'route_name']);

        if ($existing->isEmpty()) {
            return;
        }

        $masterId = DB::table('modules')->insertGetId([
            'module_group_id' => $groupId,
            'slug' => 'freight-master',
            'name' => 'Master',
            'icon' => 'fas fa-cog',
            'route_name' => '',
            'sort_order' => 30,
            'is_active' => true,
            'visibility' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $order = ['freight-charge-config' => 10, 'freight-charge-types' => 20, 'freight-rate-cards' => 30];

        foreach ($existing as $module) {
            DB::table('sub_modules')->insert([
                'module_id' => $masterId,
                'slug' => $module->slug,
                'name' => $module->name,
                'icon' => $module->icon,
                'route_name' => $module->route_name,
                'sort_order' => $order[$module->slug] ?? 10,
                'is_active' => true,
                'visibility' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('modules')->whereIn('id', $existing->pluck('id'))->delete();
    }

    public function down()
    {
        $groupId = DB::table('module_groups')->where('slug', 'freight-forwarding')->value('id');
        $masterId = DB::table('modules')->where('slug', 'freight-master')->value('id');

        if (!$groupId || !$masterId) {
            return;
        }

        $subs = DB::table('sub_modules')
            ->where('module_id', $masterId)
            ->whereIn('slug', $this->settingsSlugs)
            ->get(['slug', 'name', 'icon', 'route_name']);

        $order = ['freight-charge-config' => 30, 'freight-charge-types' => 40, 'freight-rate-cards' => 50];

        foreach ($subs as $sub) {
            DB::table('modules')->insert([
                'module_group_id' => $groupId,
                'slug' => $sub->slug,
                'name' => $sub->name,
                'icon' => $sub->icon,
                'route_name' => $sub->route_name,
                'sort_order' => $order[$sub->slug] ?? 30,
                'is_active' => true,
                'visibility' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('sub_modules')->where('module_id', $masterId)->delete();
        DB::table('modules')->where('id', $masterId)->delete();
    }
}
