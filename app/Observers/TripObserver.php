<?php

namespace App\Observers;

use App\Jobs\Sage\SyncTripToSageJob;
use App\Models\Trip;
use Illuminate\Support\Facades\Log;

/**
 * Keeps a Trip and its Sage Project in step:
 *   • on AUTHORISATION (approved) the trip is pushed to Sage as an OPEN project
 *     (Finance workflow — no cost/posting at this point);
 *   • on any subsequent UPDATE of an approved trip it RE-SYNCS, so the Sage
 *     project mirrors the Gonyeti trip (route, dates, customer, driver, etc.).
 * The push is QUEUED (SyncTripToSageJob) so authorising/editing one or many trips
 * never blocks the UI; the job is idempotent (create then update) and retries on
 * failure. The manual sync button on the trips list is the same sync run inline.
 */
class TripObserver
{
    public function created(Trip $trip): void
    {
        if (strcasecmp((string) $trip->authorization, 'approved') === 0) {
            $this->queueSync($trip);
        }
    }

    /** Trip columns that map to the Sage project — a change to any re-syncs. */
    protected const MAPPED_FIELDS = [
        'authorization', 'manifest_number', 'trip_number', 'start_date', 'end_date',
        'customer_id', 'driver_id', 'horse_id', 'currency_id',
    ];

    public function updated(Trip $trip): void
    {
        // Only re-sync an APPROVED trip when a Sage-mapped field actually changed
        // (not on every save — e.g. trip-status transitions don't touch Sage).
        // NOTE: route legs (trip_origins/destinations) and trailers are relations,
        // so a route/trailer-only edit won't trip this — use the manual Sync button.
        if (strcasecmp((string) $trip->authorization, 'approved') === 0
            && $trip->wasChanged(self::MAPPED_FIELDS)) {
            $this->queueSync($trip);
        }
    }

    protected function queueSync(Trip $trip): void
    {
        try {
            SyncTripToSageJob::dispatch((int) $trip->id);
        } catch (\Throwable $e) {
            Log::warning('Queueing Sage trip sync on authorisation failed: ' . $e->getMessage());
        }
    }
}
