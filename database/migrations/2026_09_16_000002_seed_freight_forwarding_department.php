<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the "Freight Forwarding & Clearing" department, locked so it
 * can't be renamed (Departments::update()) or deleted
 * (DepartmentController::destroy()) - both already guard on is_locked,
 * this just marks the row. Staff assigned to this department, at a
 * company with the "Freight Forwarding & Clearing" company type, is one
 * of the two conditions (`inFreight`) the freight-forwarding menu
 * group's visibility JSON requires (the other being
 * `companyIsFreightForwarder`) - without this department existing at
 * all, only Super Admins could ever see the freight menu.
 */
class SeedFreightForwardingDepartment extends Migration
{
    const NAME = 'Freight Forwarding & Clearing';

    public function up()
    {
        $exists = DB::table('departments')->where('name', self::NAME)->exists();

        if ($exists) {
            DB::table('departments')->where('name', self::NAME)->update(['is_locked' => true]);
            return;
        }

        DB::table('departments')->insert([
            'name' => self::NAME,
            'department_code' => null,
            'description' => 'Staff who operate the Freight Forwarding & Clearing module.',
            'is_locked' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        DB::table('departments')->where('name', self::NAME)->delete();
    }
}
