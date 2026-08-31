<?php

namespace App\Services\Freight;

use App\Models\FreightJob;

/**
 * Cross-job margin/P&L report. FreightJob.actual_revenue/actual_cost/
 * actual_margin are always kept fresh by FreightCostingService::
 * recalculateJobTotals() (called on every cost/charge create/update/
 * transition/delete), so this queries those persisted columns directly -
 * no re-derivation from FreightCost/FreightCharge line items needed.
 */
class JobProfitabilityCalculator
{
    const STATUSES = [
        'draft', 'confirmed', 'in_progress', 'customs_clearance', 'port_storage',
        'transport_arranged', 'delivered', 'invoiced', 'closed', 'cancelled',
    ];

    const TRANSPORT_MODES = ['sea', 'air', 'road', 'rail', 'courier', 'multimodal'];

    public function __construct(
        private string $from,
        private string $to,
        private ?int $customerId = null,
        private ?int $salespersonId = null,
        private ?int $freightServiceTypeId = null,
        private ?string $transportMode = null,
        private ?string $status = null,
    ) {
    }

    private function filteredJobs()
    {
        return FreightJob::whereBetween('opened_at', [$this->from, $this->to])
            ->when($this->customerId, fn ($q) => $q->where('customer_id', $this->customerId))
            ->when($this->salespersonId, fn ($q) => $q->where('salesperson_id', $this->salespersonId))
            ->when($this->freightServiceTypeId, fn ($q) => $q->where('freight_service_type_id', $this->freightServiceTypeId))
            ->when($this->transportMode, fn ($q) => $q->where('primary_transport_mode', $this->transportMode))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->with('customer:id,name')
            ->get(['id', 'job_number', 'customer_id', 'status', 'opened_at', 'actual_revenue', 'actual_cost', 'actual_margin']);
    }

    private function marginPercent(float $revenue, float $margin): float
    {
        return $revenue != 0 ? round(($margin / $revenue) * 100, 2) : 0.0;
    }

    /**
     * @return array{
     *     0: array<int, array{label: string, jobCount: int, revenue: float, cost: float, margin: float, marginPct: float}>,
     *     1: array{jobCount: int, revenue: float, cost: float, margin: float, marginPct: float}
     * } [rows keyed by customer_id, grand totals]
     */
    public function summaryByCustomer(): array
    {
        $rows = [];
        $grand = ['jobCount' => 0, 'revenue' => 0.0, 'cost' => 0.0, 'margin' => 0.0];

        foreach ($this->filteredJobs() as $job) {
            $customerId = $job->customer_id ?? 0;
            $revenue = (float) ($job->actual_revenue ?? 0);
            $cost = (float) ($job->actual_cost ?? 0);
            $margin = (float) ($job->actual_margin ?? ($revenue - $cost));

            $rows[$customerId] ??= [
                'label' => $job->customer->name ?? 'Unassigned Customer',
                'jobCount' => 0, 'revenue' => 0.0, 'cost' => 0.0, 'margin' => 0.0,
            ];

            $rows[$customerId]['jobCount']++;
            $rows[$customerId]['revenue'] += $revenue;
            $rows[$customerId]['cost'] += $cost;
            $rows[$customerId]['margin'] += $margin;

            $grand['jobCount']++;
            $grand['revenue'] += $revenue;
            $grand['cost'] += $cost;
            $grand['margin'] += $margin;
        }

        foreach ($rows as &$row) {
            $row['marginPct'] = $this->marginPercent($row['revenue'], $row['margin']);
        }
        unset($row);

        $grand['marginPct'] = $this->marginPercent($grand['revenue'], $grand['margin']);

        usort($rows, fn ($a, $b) => $b['margin'] <=> $a['margin']);

        return [$rows, $grand];
    }

    /**
     * @return array{
     *     0: list<array{job_number: string, customer: string, status: string, opened_at: ?string, revenue: float, cost: float, margin: float, marginPct: float}>,
     *     1: array{jobCount: int, revenue: float, cost: float, margin: float, marginPct: float}
     * }
     */
    public function details(): array
    {
        $jobs = $this->filteredJobs()->sortByDesc('opened_at');

        $grand = ['jobCount' => 0, 'revenue' => 0.0, 'cost' => 0.0, 'margin' => 0.0];
        $rows = [];

        foreach ($jobs as $job) {
            $revenue = (float) ($job->actual_revenue ?? 0);
            $cost = (float) ($job->actual_cost ?? 0);
            $margin = (float) ($job->actual_margin ?? ($revenue - $cost));

            $rows[] = [
                'job_number' => $job->job_number,
                'customer' => $job->customer->name ?? 'Unassigned Customer',
                'status' => $job->status,
                'opened_at' => optional($job->opened_at)->format('Y-m-d'),
                'revenue' => $revenue,
                'cost' => $cost,
                'margin' => $margin,
                'marginPct' => $this->marginPercent($revenue, $margin),
            ];

            $grand['jobCount']++;
            $grand['revenue'] += $revenue;
            $grand['cost'] += $cost;
            $grand['margin'] += $margin;
        }

        $grand['marginPct'] = $this->marginPercent($grand['revenue'], $grand['margin']);

        return [$rows, $grand];
    }
}
