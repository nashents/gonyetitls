<?php

namespace App\Jobs\Sage;

use App\Models\Trailer;
use App\Services\Sage\SageSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Queued single-trailer → Sage Class sync (used by bulk actions).
 */
class SyncTrailerToSageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [30, 120, 300];

    protected int $trailerId;

    public function __construct(int $trailerId)
    {
        $this->trailerId = $trailerId;
    }

    public function handle(SageSyncService $sync): void
    {
        $trailer = Trailer::find($this->trailerId);
        if ($trailer) {
            $sync->syncTrailer($trailer);
        }
    }
}
