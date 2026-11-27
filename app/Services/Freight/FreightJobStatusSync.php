<?php

namespace App\Services\Freight;

use App\Models\ContainerStage;
use App\Models\FreightJob;
use App\Models\ShippingContainer;

/**
 * Keeps a freight job's status in step with its containers. Each container
 * stage maps to a job status (container_stages.job_status, editable on
 * Freight > Master > Container Stages); a job follows its LEAST advanced
 * container, so it is only "Delivered" once every container is.
 *
 * Left alone: jobs not yet approved (they stay Draft until authorized),
 * jobs already Invoiced / Closed / Cancelled, and jobs with no container on
 * a mapped stage (e.g. air/road jobs - status stays manual).
 */
class FreightJobStatusSync
{
    /** Job statuses in operational order (subset that containers can drive). */
    public const FLOW = ['confirmed', 'in_progress', 'customs_clearance', 'port_storage', 'transport_arranged', 'delivered'];

    /** Never overwritten by container updates. */
    public const FINAL = ['invoiced', 'closed', 'cancelled'];

    /** Default stage -> job status links for the system stages. */
    public const DEFAULT_MAP = [
        'booked' => 'confirmed',
        'empty_released' => 'in_progress',
        'stuffed' => 'in_progress',
        'gate_in' => 'in_progress',
        'vessel_loaded' => 'in_progress',
        'discharged' => 'customs_clearance',
        'customs_cleared' => 'port_storage',
        'port_released' => 'transport_arranged',
        'collected_from_port' => 'transport_arranged',
        'customer_delivery' => 'delivered',
        'empty_returned' => 'delivered',
    ];

    /** The status the job's containers point to, or null when they don't decide it. */
    public function derivedStatus(FreightJob $job): ?string
    {
        $map = ContainerStage::allOrdered()->pluck('job_status', 'code')->filter()->all();

        $containerStatuses = ShippingContainer::whereIn('shipment_id', $job->shipments()->pluck('id'))
            ->pluck('status');

        $ranks = $containerStatuses
            ->map(fn ($stage) => $map[$stage] ?? null)
            ->filter(fn ($status) => in_array($status, self::FLOW, true))
            ->map(fn ($status) => array_search($status, self::FLOW, true));

        return $ranks->isEmpty() ? null : self::FLOW[$ranks->min()];
    }

    /** Updates the job's status if its containers say so. Returns the new status, or null if unchanged. */
    public function sync(?FreightJob $job): ?string
    {
        if (!$job || !$job->isApproved() || in_array($job->status, self::FINAL, true)) {
            return null;
        }

        $status = $this->derivedStatus($job);

        if ($status === null || $status === $job->status) {
            return null;
        }

        $job->status = $status;
        $job->save();

        return $status;
    }
}
