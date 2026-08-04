<?php

namespace App\Observers;

use App\Models\Booking;
use App\Services\Sage\SageSyncService;
use Illuminate\Support\Facades\Log;

/**
 * Drives the Sage job-card lifecycle off the booking's authorization:
 *   • approved → create the job card (Internal Job Card / Job Card Invoice)
 *   • rejected → reverse it (internal job cards only)
 * The job card itself is the booking's Ticket. Guarded + idempotent.
 */
class BookingObserver
{
    public function created(Booking $booking): void
    {
        if ($booking->authorization === 'approved') {
            $this->run($booking, 'approved');
        }
    }

    public function updated(Booking $booking): void
    {
        if ($booking->isDirty('authorization')) {
            $this->run($booking, $booking->authorization);
        }
    }

    protected function run(Booking $booking, ?string $authorization): void
    {
        $ticket = $booking->ticket;
        if (! $ticket) {
            return;
        }

        try {
            if ($authorization === 'approved') {
                app(SageSyncService::class)->syncJobCard($ticket);
            } elseif ($authorization === 'rejected') {
                app(SageSyncService::class)->reverseJobCard($ticket);
            }
        } catch (\Throwable $e) {
            Log::warning('Sage job-card (booking) sync failed: ' . $e->getMessage());
        }
    }
}
