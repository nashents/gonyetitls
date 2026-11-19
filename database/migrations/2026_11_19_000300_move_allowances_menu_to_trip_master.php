<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Allowances are now Transport / driver trip expenses only (payroll uses
 * Earnings), so the menu link moves from Salaries & Payroll > Master to
 * Trip Management > Master. MenuRegistrySeeder seeds it there too, but the
 * seeder only upserts, so the existing payroll-master row is moved here.
 */
class MoveAllowancesMenuToTripMaster extends Migration
{
    public function up()
    {
        $salaryMasterIds = DB::table('modules')->where('slug', 'salary-master')->pluck('id');
        $tripMasterId = DB::table('modules')->where('slug', 'trip-master')->value('id');

        $payrollRows = DB::table('sub_modules')
            ->whereIn('module_id', $salaryMasterIds)
            ->where('route_name', 'allowances.index');

        if (!$tripMasterId) {
            // No trip master on this instance: just take it out of payroll.
            $payrollRows->delete();
            return;
        }

        $alreadyInTrip = DB::table('sub_modules')
            ->where('module_id', $tripMasterId)
            ->where('route_name', 'allowances.index')
            ->exists();

        if ($alreadyInTrip) {
            $payrollRows->delete();
            return;
        }

        // Re-home the existing row; visibility null = inherit the trip master's.
        // Sort 15 = right after Agents (10), the same place the seeder puts it.
        $payrollRows->update([
            'module_id'  => $tripMasterId,
            'slug'       => 'allowances',
            'sort_order' => 15,
            'visibility' => null,
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        $salaryMasterId = DB::table('modules')->where('slug', 'salary-master')->value('id');
        $tripMasterId = DB::table('modules')->where('slug', 'trip-master')->value('id');
        if (!$salaryMasterId || !$tripMasterId) {
            return;
        }

        DB::table('sub_modules')
            ->where('module_id', $tripMasterId)
            ->where('route_name', 'allowances.index')
            ->update(['module_id' => $salaryMasterId, 'sort_order' => 10, 'updated_at' => now()]);
    }
}
