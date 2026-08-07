<?php

namespace App\Observers;

use App\Models\Ticket;
use App\Services\Sage\SageSyncService;
use Illuminate\Support\Facades\Log;

/**
 * Syncs a workshop Ticket to Sage as a job card when the ticket is CLOSED
 * (closed_by_id gets set) — with all its dispatched items. Syncing is deferred to
 * closure so we never push a job card mid-dispatch. Guarded + idempotent (a
 * missing/inactive integration no-ops; each dispatch item is placed once).
 */
class TicketObserver
{
    public function created(Ticket $ticket): void
    {
        if ($ticket->closed_by_id) {
            $this->sync($ticket);
        }
    }

    public function updated(Ticket $ticket): void
    {
        if ($ticket->isDirty('closed_by_id') && $ticket->closed_by_id) {
            $this->sync($ticket);
        }
    }

    protected function sync(Ticket $ticket): void
    {
        try {
            app(SageSyncService::class)->syncJobCard($ticket);
        } catch (\Throwable $e) {
            Log::warning('Sage job-card sync on close failed: ' . $e->getMessage());
        }
    }
}
