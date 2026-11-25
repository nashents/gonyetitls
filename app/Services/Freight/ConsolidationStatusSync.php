<?php

namespace App\Services\Freight;

use App\Models\Consolidation;
use App\Models\FreightJob;

/**
 * LCL: while the cargo is still together in the container, client (house)
 * jobs move with the master job. When a master job's status changes, every
 * approved house job that was AT the master's old status follows it to the
 * new one; a client already moved on its own (e.g. stuck in customs,
 * delivered) keeps its status.
 *
 * Only the shared, container-level stages propagate. Delivered / invoiced /
 * closed / cancelled are per client - the container arriving does not mean
 * each client has their goods or has been billed.
 *
 * Triggered from FreightJob::booted() on any status change (Edit form,
 * FreightJobService::transitionStatus, authorization, imports).
 */
class ConsolidationStatusSync
{
    const FOLLOW_STATUSES = ['confirmed', 'in_progress', 'customs_clearance', 'port_storage', 'transport_arranged'];

    /** Job ids being synced in this request - guards against cycles. */
    private static array $inProgress = [];

    /** House jobs moved by the most recent follow() - for UI feedback. */
    public static array $lastFollowed = [];

    public static function follow(FreightJob $master, ?string $from, string $to): array
    {
        self::$lastFollowed = [];

        // Both ends must be shared stages: moving the master back out of
        // "delivered" etc. must never drag a genuinely delivered client back.
        if (!$from || $from === $to
            || !in_array($from, self::FOLLOW_STATUSES, true)
            || !in_array($to, self::FOLLOW_STATUSES, true)
            || isset(self::$inProgress[$master->id])) {
            return [];
        }

        $consolidations = Consolidation::with('house_shipments.freight_job')
            ->whereHas('master_shipment', fn ($q) => $q->where('freight_job_id', $master->id))
            ->get();

        $houseJobs = $consolidations->flatMap->house_shipments
            ->pluck('freight_job')->filter()
            ->unique('id')
            ->reject(fn (FreightJob $job) => $job->id === $master->id);

        self::$inProgress[$master->id] = true;
        $moved = [];

        try {
            foreach ($houseJobs as $job) {
                if ($job->status !== $from || $job->authorization !== 'approved') {
                    continue;
                }

                $job->status = $to;
                $job->save();   // audited; cascades if this job is itself a master
                $moved[] = $job->job_number;
            }
        } finally {
            unset(self::$inProgress[$master->id]);
        }

        return self::$lastFollowed = $moved;
    }
}
