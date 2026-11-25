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
            // A consolidation's master costs are shared with its house jobs,
            // so a change on any of them re-splits every job in it.
            $allocator = app(ConsolidationCostAllocator::class);
            foreach ($allocator->relatedJobIds([$job->id]) as $relatedId) {
                if ($relatedId != $job->id && ($related = FreightJob::find($relatedId))) {
                    $this->saveTotals($related, $allocator);
                }
            }

            return $this->saveTotals($job, $allocator);
        });
    }

    /**
     * Re-split after a consolidation's membership, basis or allocation values
     * change (affected job ids captured BEFORE a detach so the removed job
     * gets its share cleared too).
     */
    public function recalculateJobs(array $jobIds): void
    {
        $allocator = app(ConsolidationCostAllocator::class);

        DB::transaction(function () use ($jobIds, $allocator) {
            foreach (FreightJob::whereIn('id', $allocator->relatedJobIds($jobIds))->get() as $job) {
                $this->saveTotals($job, $allocator);
            }
        });
    }

    private function saveTotals(FreightJob $job, ConsolidationCostAllocator $allocator): FreightJob
    {
        $ownCost = (float) FreightCost::where('freight_job_id', $job->id)
            ->whereIn('verification_status', self::COST_ACTUAL_STATUSES)
            ->sum('exchange_amount');

        $consolidationCost = $allocator->consolidationCostFor($job);
        $actualCost = $ownCost + $consolidationCost;

        $actualRevenue = FreightCharge::where('freight_job_id', $job->id)
            ->whereIn('status', self::CHARGE_ACTUAL_STATUSES)
            ->sum('exchange_amount');

        $job->consolidation_cost = $consolidationCost;
        $job->actual_cost = $actualCost;
        $job->actual_revenue = $actualRevenue;
        $job->actual_margin = $actualRevenue - $actualCost;

        if ($job->estimated_revenue !== null && $job->estimated_cost !== null) {
            $job->estimated_margin = $job->estimated_revenue - $job->estimated_cost;
        }

        $job->save();

        return $job;
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
