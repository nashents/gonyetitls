<?php

namespace App\Services\Freight;

use App\Models\Consolidation;
use App\Models\FreightCost;
use App\Models\FreightJob;
use App\Models\Shipment;

/**
 * Splits a consolidation's master costs (the shared container: ocean freight,
 * origin charges...) across its house shipments' jobs. The master job's
 * verified/approved/posted cost lines (in the master job's currency) form
 * the pool; each house job takes a share on the consolidation's basis and the
 * master job gives the same total up. Result is persisted per job in
 * freight_jobs.consolidation_cost by FreightCostingService.
 *
 * Management split only - nothing is billed or journalled here.
 */
class ConsolidationCostAllocator
{
    const BASES = [
        'cbm' => 'CBM',
        'weight' => 'Gross Weight',
        'chargeable_weight' => 'Chargeable Weight',
        'container' => 'Equal Split',
        'fixed_percentage' => 'Fixed Percentage',
        'manual' => 'Manual Ratio',
    ];

    const DEFAULT_BASIS = 'cbm';

    /** Hint shown next to the per-house "allocation value" input. */
    const VALUE_HINTS = [
        'fixed_percentage' => '% of master cost',
        'manual' => 'Ratio (e.g. 2 = twice a 1)',
    ];

    public static function basisOf(Consolidation $consolidation): string
    {
        return array_key_exists($consolidation->cost_allocation_basis, self::BASES)
            ? $consolidation->cost_allocation_basis
            : self::DEFAULT_BASIS;
    }

    public static function usesAllocationValue(string $basis): bool
    {
        return in_array($basis, ['fixed_percentage', 'manual'], true);
    }

    /**
     * @return array{
     *   basis: string, pool: float, allocated: float, unallocated: float,
     *   master_job: ?FreightJob, warnings: string[],
     *   rows: array<int, array{shipment: Shipment, job: ?FreightJob, basis_value: float, percent: float, amount: float, excluded: ?string}>
     * }
     */
    public function breakdown(Consolidation $consolidation): array
    {
        $consolidation->loadMissing([
            'master_shipment.freight_job.currency',
            'house_shipments.freight_job.currency',
            'house_shipments.cargo_items',
        ]);

        $basis = self::basisOf($consolidation);
        $masterShipment = $consolidation->master_shipment;
        $masterJob = $masterShipment?->freight_job;
        $pool = $masterJob ? $this->pool($masterJob, $masterShipment) : 0.0;

        $rows = [];
        $warnings = [];

        foreach ($consolidation->house_shipments as $shipment) {
            $job = $shipment->freight_job;
            $excluded = null;

            if (!$job) {
                $excluded = 'Shipment has no job';
            } elseif ($masterJob && $job->id === $masterJob->id) {
                $excluded = 'Same job as the master';
            } elseif ($masterJob && $job->currency_id && $masterJob->currency_id && $job->currency_id != $masterJob->currency_id) {
                $excluded = 'Job currency (' . ($job->currency?->name ?? '?') . ') differs from master (' . ($masterJob->currency?->name ?? '?') . ')';
            }

            $rows[$shipment->id] = [
                'shipment' => $shipment,
                'job' => $job,
                'basis_value' => $excluded ? 0.0 : $this->basisValue($basis, $shipment),
                'percent' => 0.0,
                'amount' => 0.0,
                'excluded' => $excluded,
            ];
        }

        $included = array_filter($rows, fn ($r) => !$r['excluded']);

        if ($basis === 'fixed_percentage') {
            $totalPct = array_sum(array_column($included, 'basis_value'));
            if ($totalPct > 100) {
                $warnings[] = 'Fixed percentages add up to ' . round($totalPct, 2) . '% - more than 100% of the master cost is being allocated.';
            }
            foreach ($included as $id => $row) {
                $rows[$id]['percent'] = round($row['basis_value'], 4);
                $rows[$id]['amount'] = round($pool * $row['basis_value'] / 100, 2);
            }
        } else {
            $total = array_sum(array_column($included, 'basis_value'));

            if ($included && $total <= 0) {
                $warnings[] = match ($basis) {
                    'cbm' => 'No CBM captured on the house shipments\' cargo - nothing allocated. Capture CBM on each client job\'s cargo.',
                    'weight' => 'No gross weight captured on the house shipments\' cargo - nothing allocated.',
                    'chargeable_weight' => 'No chargeable or gross weight captured on the house shipments\' cargo - nothing allocated.',
                    default => 'No allocation values entered - nothing allocated.',
                };
            } elseif ($total > 0) {
                $remaining = round($pool, 2);
                $ids = array_keys($included);
                $lastId = end($ids);

                foreach ($included as $id => $row) {
                    $share = $row['basis_value'] / $total;
                    $rows[$id]['percent'] = round($share * 100, 4);
                    // Last row takes the rounding remainder so shares sum to the pool exactly.
                    $rows[$id]['amount'] = $id === $lastId ? round($remaining, 2) : round($pool * $share, 2);
                    $remaining -= $rows[$id]['amount'];
                }
            }
        }

        foreach ($rows as $row) {
            if ($row['excluded'] && $row['job']) {
                $warnings[] = "{$row['job']->job_number}: not allocated - {$row['excluded']}.";
            }
        }

        $allocated = round(array_sum(array_column($rows, 'amount')), 2);

        return [
            'basis' => $basis,
            'pool' => round($pool, 2),
            'allocated' => $allocated,
            'unallocated' => round($pool - $allocated, 2),
            'master_job' => $masterJob,
            'warnings' => $warnings,
            'rows' => $rows,
        ];
    }

    /**
     * Net consolidation cost for a job across every consolidation it sits in:
     * + its share as a house job, - what it hands out as a master job.
     */
    public function consolidationCostFor(FreightJob $job): float
    {
        $net = 0.0;

        foreach ($this->consolidationsTouching([$job->id]) as $consolidation) {
            $breakdown = $this->breakdown($consolidation);

            if ($breakdown['master_job']?->id === $job->id) {
                $net -= $breakdown['allocated'];
            }
            foreach ($breakdown['rows'] as $row) {
                if ($row['job']?->id === $job->id && !$row['excluded']) {
                    $net += $row['amount'];
                }
            }
        }

        return round($net, 2);
    }

    /**
     * Every job sharing a consolidation with any of $jobIds (including them).
     */
    public function relatedJobIds(array $jobIds): array
    {
        $ids = $jobIds;

        foreach ($this->consolidationsTouching($jobIds) as $consolidation) {
            $ids[] = $consolidation->master_shipment?->freight_job_id;
            foreach ($consolidation->house_shipments as $shipment) {
                $ids[] = $shipment->freight_job_id;
            }
        }

        return array_values(array_unique(array_filter($ids)));
    }

    private function consolidationsTouching(array $jobIds)
    {
        $shipmentIds = Shipment::whereIn('freight_job_id', $jobIds)->pluck('id');

        if ($shipmentIds->isEmpty()) {
            return collect();
        }

        return Consolidation::with(['master_shipment', 'house_shipments'])
            ->where(function ($q) use ($shipmentIds) {
                $q->whereIn('master_shipment_id', $shipmentIds)
                    ->orWhereHas('house_shipments', fn ($h) => $h->whereIn('shipments.id', $shipmentIds));
            })
            ->get();
    }

    /**
     * Master job's confirmed costs that belong to the shared shipment (lines
     * tagged to another of the master job's shipments are not shared).
     */
    private function pool(FreightJob $masterJob, Shipment $masterShipment): float
    {
        return (float) FreightCost::where('freight_job_id', $masterJob->id)
            ->whereIn('verification_status', FreightCostingService::COST_ACTUAL_STATUSES)
            ->where(fn ($q) => $q->whereNull('shipment_id')->orWhere('shipment_id', $masterShipment->id))
            ->sum('exchange_amount');
    }

    private function basisValue(string $basis, Shipment $shipment): float
    {
        $cargo = $shipment->cargo_items;

        return (float) match ($basis) {
            'cbm' => $cargo->sum(fn ($c) => (float) $c->cbm),
            'weight' => $cargo->sum(fn ($c) => (float) $c->gross_weight),
            'chargeable_weight' => $cargo->sum(fn ($c) => (float) ($c->chargeable_weight ?: $c->gross_weight)),
            'container' => 1,
            'fixed_percentage', 'manual' => (float) $shipment->pivot->allocation_value,
            default => 0,
        };
    }
}
