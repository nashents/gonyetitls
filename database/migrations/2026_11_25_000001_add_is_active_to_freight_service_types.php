<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Freight Service Types get an Active/Inactive status: inactive types are no
 * longer offered when creating jobs / rate cards / consolidation clients, but
 * stay on existing records and in list filters. Every existing type is active.
 */
class AddIsActiveToFreightServiceTypes extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('freight_service_types', 'is_active')) {
            Schema::table('freight_service_types', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('description');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('freight_service_types', 'is_active')) {
            Schema::table('freight_service_types', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }
    }
}
