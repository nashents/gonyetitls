<?php

namespace App\Jobs\Sage;

use App\Models\Horse;
use App\Services\Sage\SageSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Queued single-horse → Sage Class sync (used by bulk actions).
 */
class SyncHorseToSageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Retry recoverable errors (timeouts/network) with a short backoff. */
    public int $tries = 3;
    public array $backoff = [30, 120, 300];

    protected int $horseId;

    public function __construct(int $horseId)
    {
        $this->horseId = $horseId;
    }

    public function handle(SageSyncService $sync): void
    {
        $horse = Horse::find($this->horseId);
        if ($horse) {
            $sync->syncHorse($horse);
        }
    }
}
