<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Seeded core expenses (is_locked: Fuel - COGS, Transporter Payment) drive
 * the fuel/trip billing flows and must always be billable. Restore any that
 * were switched off before the lock was enforced (Expense::booted keeps
 * them that way from now on).
 */
class ForceLockedExpensesBillable extends Migration
{
    public function up()
    {
        DB::table('expenses')->where('is_locked', true)->update(['billable' => true]);
    }

    public function down()
    {
        //
    }
}
