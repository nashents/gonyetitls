<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-user HR restriction: when on, the user's HR access is limited to drivers
 * (driver records, driver leave, driver loans, driver payslips) — no admin staff
 * records, admin leave or payroll. See App\Support\HrAccess.
 */
return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('users', 'hr_drivers_only')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('hr_drivers_only')->default(false);
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('users', 'hr_drivers_only')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('hr_drivers_only');
            });
        }
    }
};
