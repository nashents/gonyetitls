<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Marks the departments DepartmentSeeder ships as locked (core).
 *
 * The is_locked column was added (default false) after these rows were
 * already seeded, and the seeder was never re-run on existing databases, so
 * the core departments were left unlocked: they showed as "Custom" and could
 * be renamed/deleted even though the system looks them up by name.
 *
 * Only flags rows that exist - it does not recreate a core department a
 * company has removed. Keep the list in step with DepartmentSeeder.
 */
class LockSeededCoreDepartments extends Migration
{
    const NAMES = [
        'Information Technology',
        'Human Resources',
        'Finance',
        'Transport & Logistics',
        'Stores',
        'Services',
        'Workshop',
        'Security',
        'HSEQ',
        'Freight Forwarding & Clearing',
    ];

    public function up()
    {
        DB::table('departments')->whereIn('name', self::NAMES)->update(['is_locked' => true]);
    }

    public function down()
    {
        // Data fix only - nothing to undo.
    }
}
