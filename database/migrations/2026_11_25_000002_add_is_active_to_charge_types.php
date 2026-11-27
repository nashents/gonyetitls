<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Freight Charge Types get an Active/Inactive status: inactive types are not
 * offered on new cost/charge lines or rate cards (and an inactive free-time
 * type starts no new container clocks), but stay on existing records.
 * Every existing type is active.
 */
class AddIsActiveToChargeTypes extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('charge_types', 'is_active')) {
            Schema::table('charge_types', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('description');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('charge_types', 'is_active')) {
            Schema::table('charge_types', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }
    }
}
