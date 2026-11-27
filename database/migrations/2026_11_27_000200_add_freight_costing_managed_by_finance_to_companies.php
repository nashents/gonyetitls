<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Business Settings > "Freight Costing Managed By Finance" (like Trip Rates
 * Managed By Finance): when on, only the Finance department and Super Admins
 * see freight job costing (Costing tab, Costing column, estimated values).
 */
return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('companies', 'freight_costing_managed_by_finance')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->boolean('freight_costing_managed_by_finance')->default(false);
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('companies', 'freight_costing_managed_by_finance')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->dropColumn('freight_costing_managed_by_finance');
            });
        }
    }
};
