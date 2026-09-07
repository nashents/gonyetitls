<?php

namespace App\Observers;

use App\Models\Booking;
use App\Services\Sage\SageSyncService;
use Illuminate\Support\Facades\Log;

/**
 * Job-card sync is driven by the booking's AUTHORIZATION:
 *   • approved → push/refresh the Sage job card (with whatever items are
 *     dispatched so far; later dispatches append idempotently).
 *   • rejected → reverse the job card (internal job cards only).
 * Both are guarded + idempotent (a missing/inactive integration no-ops; nothing
 * to reverse if not yet synced). Syncing never happens while a booking is still
 * pending — the service itself refuses any non-approved booking.
 */
class BookingObserver
{
    public function updated(Booking $booking): void
    {
        if (! $booking->isDirty('authorization')) {
            return;
        }

        $ticket = $booking->ticket;
        if (! $ticket) {
            return;
        }

        if ($booking->authorization === 'approved') {
            try {
                app(SageSyncService::class)->syncJobCard($ticket);
            } catch (\Throwable $e) {
                Log::warning('Sage job-card sync on authorization failed: ' . $e->getMessage());
            }
            return;
        }

        if ($booking->authorization === 'rejected') {
            try {
                app(SageSyncService::class)->reverseJobCard($ticket);
            } catch (\Throwable $e) {
                Log::warning('Sage job-card reversal failed: ' . $e->getMessage());
            }
        }
    }
}
