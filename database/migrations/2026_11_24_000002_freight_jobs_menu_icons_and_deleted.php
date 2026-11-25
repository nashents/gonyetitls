<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Freight Jobs menu matches the other modules (Invoices, Bills, Requisitions,
 * Purchase Orders...): Pending = fa-clock, Approved = fa-check,
 * Rejected = fa-ban, plus "Deleted Jobs" (fa-trash) with Restore.
 */
class FreightJobsMenuIconsAndDeleted extends Migration
{
    private const ICONS = [
        'freight.jobs.pending' => 'fas fa-clock',
        'freight.jobs.approved' => 'fas fa-check',
        'freight.jobs.rejected' => 'fas fa-ban',
    ];

    public function up()
    {
        foreach (self::ICONS as $route => $icon) {
            DB::table('sub_modules')->where('route_name', $route)->update(['icon' => $icon, 'updated_at' => now()]);
        }

        $moduleId = DB::table('modules')->where('slug', 'freight-jobs')->value('id');

        if ($moduleId && !DB::table('sub_modules')->where('route_name', 'freight.jobs.deleted')->exists()) {
            DB::table('sub_modules')->insert([
                'module_id' => $moduleId,
                'slug' => 'deleted-freight-jobs',
                'name' => 'Deleted Jobs',
                'icon' => 'fas fa-trash',
                'route_name' => 'freight.jobs.deleted',
                'sort_order' => 60,
                'is_active' => true,
                'visibility' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        DB::table('sub_modules')->where('route_name', 'freight.jobs.deleted')->delete();
        DB::table('sub_modules')->where('route_name', 'freight.jobs.pending')->update(['icon' => 'fas fa-hourglass-half']);
        DB::table('sub_modules')->where('route_name', 'freight.jobs.approved')->update(['icon' => 'fas fa-check']);
        DB::table('sub_modules')->where('route_name', 'freight.jobs.rejected')->update(['icon' => 'fas fa-times']);
    }
}
