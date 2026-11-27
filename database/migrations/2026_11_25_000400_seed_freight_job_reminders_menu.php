<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Freight Jobs > Job Reminders (all jobs' follow-ups, set on each job's Reminders tab). */
return new class extends Migration
{
    public function up()
    {
        $moduleId = DB::table('modules')->where('slug', 'freight-jobs')->value('id');

        if ($moduleId && !DB::table('sub_modules')->where('route_name', 'freight.job-reminders.index')->exists()) {
            DB::table('sub_modules')->insert([
                'module_id' => $moduleId,
                'slug' => 'freight-job-reminders',
                'name' => 'Job Reminders',
                'icon' => 'fas fa-bell',
                'route_name' => 'freight.job-reminders.index',
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
        DB::table('sub_modules')->where('route_name', 'freight.job-reminders.index')->delete();
    }
};
