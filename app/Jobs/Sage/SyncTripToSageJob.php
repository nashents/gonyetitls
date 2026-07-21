<?php

namespace App\Jobs\Sage;

use App\Models\Trip;
use App\Services\Sage\SageSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Queued single-trip → Sage Project sync (used by bulk actions).
 */
class SyncTripToSageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [30, 120, 300];

    protected int $tripId;

    public function __construct(int $tripId)
    {
        $this->tripId = $tripId;
    }

    public function handle(SageSyncService $sync): void
    {
        $trip = Trip::find($this->tripId);
        if ($trip) {
            $sync->syncTrip($trip);
        }
    }
}
