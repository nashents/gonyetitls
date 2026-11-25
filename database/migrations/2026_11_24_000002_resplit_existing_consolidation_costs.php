<?php

use App\Models\Consolidation;
use App\Models\Shipment;
use App\Services\Freight\FreightCostingService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * One-off: split existing consolidations' master costs onto their house jobs
 * (freight_jobs.consolidation_cost + actual_cost/margin). Afterwards
 * FreightCostingService keeps it current on every cost/cargo/consolidation change.
 */
class ResplitExistingConsolidationCosts extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('consolidations') || !Schema::hasColumn('freight_jobs', 'consolidation_cost')) {
            return;
        }

        $shipmentIds = Consolidation::pluck('master_shipment_id')
            ->merge(\DB::table('consolidation_shipments')->pluck('shipment_id'))
            ->unique();

        $jobIds = Shipment::whereIn('id', $shipmentIds)->pluck('freight_job_id')->filter()->unique()->values()->all();

        if ($jobIds) {
            app(FreightCostingService::class)->recalculateJobs($jobIds);
        }
    }

    public function down()
    {
        //
    }
}
