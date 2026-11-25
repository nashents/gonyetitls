<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Maintenance Schedules leaves Workshop Management > Bookings and becomes its
 * own sidebar link, "Maintenance Planning", directly above Bookings.
 * MenuRegistrySeeder seeds it that way too, but the seeder only upserts, so the
 * old Bookings sub-item is removed and the module row created here.
 */
class MoveMaintenanceSchedulesMenuOutOfBookings extends Migration
{
    public function up()
    {
        $groupId = DB::table('module_groups')->where('slug', 'workshop-management')->value('id');
        if (!$groupId) {
            return;
        }

        $bookingModuleIds = DB::table('modules')
            ->where('module_group_id', $groupId)
            ->where('slug', 'bookings')
            ->pluck('id');

        DB::table('sub_modules')
            ->whereIn('module_id', $bookingModuleIds)
            ->where('route_name', 'maintenance_schedules.index')
            ->delete();

        $existing = DB::table('modules')
            ->where('module_group_id', $groupId)
            ->where('slug', 'maintenance-planning')
            ->first();

        $values = [
            'name'       => 'Maintenance Planning',
            'icon'       => 'fas fa-calendar-check',
            'route_name' => 'maintenance_schedules.index',
            'sort_order' => 15, // between Master (10) and Bookings (20)
            'updated_at' => now(),
        ];

        if ($existing) {
            DB::table('modules')->where('id', $existing->id)->update($values);
            return;
        }

        DB::table('modules')->insert($values + [
            'module_group_id' => $groupId,
            'slug'            => 'maintenance-planning',
            'is_active'       => true,
            'visibility'      => null, // inherit Workshop Management's
            'created_at'      => now(),
        ]);
    }

    public function down()
    {
        $groupId = DB::table('module_groups')->where('slug', 'workshop-management')->value('id');
        if (!$groupId) {
            return;
        }

        DB::table('modules')
            ->where('module_group_id', $groupId)
            ->where('slug', 'maintenance-planning')
            ->delete();

        $bookingsId = DB::table('modules')
            ->where('module_group_id', $groupId)
            ->where('slug', 'bookings')
            ->value('id');

        if ($bookingsId) {
            DB::table('sub_modules')->insert([
                'module_id'  => $bookingsId,
                'name'       => 'Maintenance Schedules',
                'slug'       => 'maintenance-schedules',
                'icon'       => 'fas fa-tasks',
                'route_name' => 'maintenance_schedules.index',
                'sort_order' => 25,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
