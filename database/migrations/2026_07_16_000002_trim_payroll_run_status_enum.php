<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Drops the never-used 'calculating', 'validated', 'exported', and 'archived'
 * lifecycle states — nothing in the app ever transitions a run/payroll into
 * them. The real, wired-up flow is draft -> approved -> locked -> posted
 * (or reversed at any approved-or-later point).
 */
class TrimPayrollRunStatusEnum extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE payroll_runs MODIFY COLUMN status ENUM('draft','approved','locked','posted','reversed') NOT NULL DEFAULT 'draft'");
        DB::statement("ALTER TABLE payrolls MODIFY COLUMN payroll_status ENUM('draft','approved','locked','posted','reversed') NOT NULL DEFAULT 'draft'");
    }

    public function down()
    {
        DB::statement("ALTER TABLE payroll_runs MODIFY COLUMN status ENUM('draft','calculating','validated','approved','locked','exported','posted','archived','reversed') NOT NULL DEFAULT 'draft'");
        DB::statement("ALTER TABLE payrolls MODIFY COLUMN payroll_status ENUM('draft','calculating','validated','approved','locked','exported','posted','archived','reversed') NOT NULL DEFAULT 'draft'");
    }
}
