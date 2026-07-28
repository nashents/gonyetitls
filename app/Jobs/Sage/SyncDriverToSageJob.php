<?php

namespace App\Jobs\Sage;

use App\Models\Driver;
use App\Services\Sage\SageSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Queued single-driver → Sage Employee sync (used by bulk actions).
 */
class SyncDriverToSageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [30, 120, 300];

    protected int $driverId;

    public function __construct(int $driverId)
    {
        $this->driverId = $driverId;
    }

    public function handle(SageSyncService $sync): void
    {
        $driver = Driver::find($this->driverId);
        if ($driver) {
            $sync->syncDriver($driver);
        }
    }
}
