<?php

namespace App\Observers;

use App\Models\DispatchItem;
use App\Services\Sage\SageSyncService;
use Illuminate\Support\Facades\Log;

/**
 * When an item is dispatched to a workshop ticket, add it to that ticket's Sage
 * job card — creating the job card if it doesn't exist yet (once the booking is
 * authorized), or appending the line otherwise. Guarded + idempotent (each
 * dispatch item is placed on the job card exactly once).
 */
class DispatchItemObserver
{
    public function created(DispatchItem $item): void
    {
        $this->sync($item);
    }

    protected function sync(DispatchItem $item): void
    {
        $ticket = optional($item->dispatch)->ticket;
        if (! $ticket) {
            return;
        }

        try {
            app(SageSyncService::class)->syncJobCard($ticket);
        } catch (\Throwable $e) {
            Log::warning('Sage job-card dispatch-line sync failed: ' . $e->getMessage());
        }
    }
}
