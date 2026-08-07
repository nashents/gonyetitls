<?php

namespace App\Observers;

use App\Models\Booking;
use App\Services\Sage\SageSyncService;
use Illuminate\Support\Facades\Log;

/**
 * A rejected booking reverses its job card in Sage (internal job cards only).
 * Creation is NOT done here — the job card is synced when the ticket is CLOSED
 * (see TicketObserver), so it's never pushed while items are still being
 * dispatched. Guarded + idempotent (nothing to reverse if not yet synced).
 */
class BookingObserver
{
    public function updated(Booking $booking): void
    {
        if ($booking->isDirty('authorization') && $booking->authorization === 'rejected') {
            $ticket = $booking->ticket;
            if (! $ticket) {
                return;
            }

            try {
                app(SageSyncService::class)->reverseJobCard($ticket);
            } catch (\Throwable $e) {
                Log::warning('Sage job-card reversal failed: ' . $e->getMessage());
            }
        }
    }
}
