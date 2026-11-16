<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Menu entries for subscription billing (mirrors MenuRegistrySeeder):
 *  - Companies > Client Billing          (system admins: all clients)
 *  - Business Settings > Billing & Subscription (each client's own account)
 * Both inherit their parent's visibility.
 */
class SeedSubscriptionBillingMenu extends Migration
{
    public function up()
    {
        $companiesModuleId = DB::table('modules')->where('slug', 'companies')->whereNull('deleted_at')->value('id');
        if ($companiesModuleId && ! DB::table('sub_modules')->where('module_id', $companiesModuleId)->where('slug', 'client-billing')->exists()) {
            DB::table('sub_modules')->insert([
                'module_id' => $companiesModuleId,
                'slug' => 'client-billing',
                'name' => 'Client Billing',
                'icon' => 'fas fa-file-invoice-dollar',
                'route_name' => 'billing.index',
                'sort_order' => 20,
                'is_active' => true,
                'visibility' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $settingsGroupId = DB::table('module_groups')->where('slug', 'business-settings')->value('id');
        if ($settingsGroupId && ! DB::table('modules')->where('module_group_id', $settingsGroupId)->where('slug', 'billing-subscription')->exists()) {
            DB::table('modules')->insert([
                'module_group_id' => $settingsGroupId,
                'slug' => 'billing-subscription',
                'name' => 'Billing & Subscription',
                'icon' => 'fas fa-file-invoice-dollar',
                'route_name' => 'billing.account',
                'sort_order' => 25,
                'is_active' => true,
                'visibility' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        DB::table('sub_modules')->where('slug', 'client-billing')->delete();
        DB::table('modules')->where('slug', 'billing-subscription')->delete();
    }
}
