<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Share of a consolidation's master costs, in the job's currency. Positive on
 * house (client) jobs, negative on the master job, so actual_cost/margin
 * reflect each client's slice of the shared container. Management split
 * only - supplier bills and the ledger stay on the master job.
 */
class AddConsolidationCostToFreightJobsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('freight_jobs', 'consolidation_cost')) {
            Schema::table('freight_jobs', function (Blueprint $table) {
                $table->decimal('consolidation_cost', 15, 2)->default(0)->after('actual_cost');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('freight_jobs', 'consolidation_cost')) {
            Schema::table('freight_jobs', function (Blueprint $table) {
                $table->dropColumn('consolidation_cost');
            });
        }
    }
}
