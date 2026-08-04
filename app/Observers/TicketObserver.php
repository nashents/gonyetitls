<?php

namespace App\Observers;

use App\Models\Ticket;
use App\Services\Sage\SageSyncService;
use Illuminate\Support\Facades\Log;

/**
 * Closes off a job card in Sage when its workshop Ticket is closed (closed_by_id
 * gets set). Creation happens earlier, on booking approval (see BookingObserver).
 * Guarded + idempotent: a missing job card / inactive integration no-ops.
 */
class TicketObserver
{
    public function created(Ticket $ticket): void
    {
        if ($ticket->closed_by_id) {
            $this->closeOff($ticket);
        }
    }

    public function updated(Ticket $ticket): void
    {
        if ($ticket->isDirty('closed_by_id') && $ticket->closed_by_id) {
            $this->closeOff($ticket);
        }
    }

    protected function closeOff(Ticket $ticket): void
    {
        try {
            app(SageSyncService::class)->closeOffJobCard($ticket);
        } catch (\Throwable $e) {
            Log::warning('Sage job-card close-off failed: ' . $e->getMessage());
        }
    }
}
