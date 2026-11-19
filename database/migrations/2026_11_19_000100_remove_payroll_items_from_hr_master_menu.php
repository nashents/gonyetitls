<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Allowances, Deductions and Earnings were listed under both
 * Human Resource > Master and Salaries & Payroll > Master. They now live only
 * under Salaries & Payroll. MenuRegistrySeeder no longer seeds them under
 * HR, but the seeder only upserts, so the existing rows are removed here.
 */
class RemovePayrollItemsFromHrMasterMenu extends Migration
{
    private const ROUTES = ['allowances.index', 'deductions.index', 'earnings.index'];

    public function up()
    {
        $hrMasterIds = DB::table('modules')->where('slug', 'hr-master')->pluck('id');

        if ($hrMasterIds->isEmpty()) {
            return;
        }

        DB::table('sub_modules')
            ->whereIn('module_id', $hrMasterIds)
            ->whereIn('route_name', self::ROUTES)
            ->delete();
    }

    public function down()
    {
        // Not restored: the same links remain under Salaries & Payroll > Master.
    }
}
