<?php

namespace App\Http\Livewire\Concerns;

use App\Models\Booking;
use App\Services\Sage\SageIntegration;
use App\Services\Sage\SageSyncService;

/**
 * Shared "sync this booking's job card to Sage" action + gate for the Bookings
 * index / approved list components. Syncing is locked to AUTHORIZED bookings
 * (it auto-syncs at authorization; this button is a manual re-sync/retry) and
 * the job card is built from the ticket's dispatch items; otherwise this reports
 * why nothing happened.
 */
trait SyncsBookingJobCard
{
    /** Sage integration gate — controls the job-card badge + sync button. */
    public function getSageEnabledProperty()
    {
        return SageIntegration::enabledForUser();
    }

    public function syncJobCardToSage($bookingId)
    {
        if (! $this->sageEnabled) {
            return;
        }

        $booking = Booking::with('ticket')->find($bookingId);

        // Syncing is locked to authorized bookings — no push while pending/rejected.
        if (! $booking || strcasecmp((string) $booking->authorization, 'approved') !== 0) {
            $this->dispatchBrowserEvent('alert', ['type' => 'warning', 'message' => 'The booking must be authorized before its job card can sync to Sage.']);
            return;
        }

        $ticket = $booking->ticket;
        if (! $ticket) {
            $this->dispatchBrowserEvent('alert', ['type' => 'warning', 'message' => 'This booking has no job card ticket to sync.']);
            return;
        }

        $result = app(SageSyncService::class)->syncJobCard($ticket);

        $synced  = ! empty($result['success']) && empty($result['skipped']) && ! empty($result['external_id']);
        $skipped = ! empty($result['skipped']) || (empty($result['external_id']) && empty($result['error']));

        $this->dispatchBrowserEvent('alert', [
            'type'    => $synced ? 'success' : ($skipped ? 'warning' : 'error'),
            'message' => $synced
                ? 'Job card synced to Sage (' . $result['external_id'] . ').'
                : ($skipped
                    ? 'Nothing to sync yet — the job card needs dispatched items on its ticket. Dispatch items, then re-sync.'
                    : 'Sage sync: ' . ($result['error'] ?? 'unknown error')),
        ]);
    }
}
