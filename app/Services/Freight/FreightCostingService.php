<?php

namespace App\Services\Freight;

use App\Models\FreightCharge;
use App\Models\FreightCost;
use App\Models\FreightJob;
use Illuminate\Support\Facades\DB;

class FreightCostingService
{
    const COST_ACTUAL_STATUSES = ['verified', 'approved', 'posted'];
    const COST_ACCRUED_STATUSES = ['received', 'pending_verification', 'disputed'];
    const CHARGE_ACTUAL_STATUSES = ['approved', 'invoiced'];

    /**
     * Recomputes FreightJob.actual_revenue/actual_cost/actual_margin from
     * the job's confirmed cost/charge lines, summing exchange_amount (each
     * line's amount already converted into the job's currency at save time)
     * rather than raw amount, which would mix currencies meaninglessly.
     */
    public function recalculateJobTotals(FreightJob $job): FreightJob
    {
        return DB::transaction(function () use ($job) {
            $actualCost = FreightCost::where('freight_job_id', $job->id)
                ->whereIn('verification_status', self::COST_ACTUAL_STATUSES)
                ->sum('exchange_amount');

            $actualRevenue = FreightCharge::where('freight_job_id', $job->id)
                ->whereIn('status', self::CHARGE_ACTUAL_STATUSES)
                ->sum('exchange_amount');

            $job->actual_cost = $actualCost;
            $job->actual_revenue = $actualRevenue;
            $job->actual_margin = $actualRevenue - $actualCost;

            if ($job->estimated_revenue !== null && $job->estimated_cost !== null) {
                $job->estimated_margin = $job->estimated_revenue - $job->estimated_cost;
            }

            $job->save();

            return $job;
        });
    }

    /**
     * Live query only — not persisted. Unverified/disputed cost lines are
     * a real financial exposure a job could still incur, shown in the
     * Costing tab summary alongside the persisted actual_cost figure.
     */
    public function accruedCost(FreightJob $job): float
    {
        return (float) FreightCost::where('freight_job_id', $job->id)
            ->whereIn('verification_status', self::COST_ACCRUED_STATUSES)
            ->sum('exchange_amount');
    }

    public function marginPercent(?float $revenue, ?float $margin): ?float
    {
        if (!$revenue) {
            return null;
        }

        return round(($margin / $revenue) * 100, 2);
    }

    public function saveEstimates(FreightJob $job, ?float $revenue, ?float $cost): FreightJob
    {
        $job->estimated_revenue = $revenue;
        $job->estimated_cost = $cost;
        $job->estimated_margin = ($revenue !== null && $cost !== null) ? $revenue - $cost : null;
        $job->save();

        return $job;
    }
}
