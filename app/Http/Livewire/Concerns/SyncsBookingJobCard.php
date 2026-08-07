<?php

namespace App\Http\Livewire\Concerns;

use App\Models\Booking;
use App\Services\Sage\SageIntegration;
use App\Services\Sage\SageSyncService;

/**
 * Shared "sync this booking's job card to Sage" action + gate for the Bookings
 * index / approved list components. The job card is built from the ticket's
 * dispatch items and only syncs once the ticket is CLOSED (and the booking
 * authorized); otherwise this reports why nothing happened.
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

        $ticket = optional(Booking::with('ticket')->find($bookingId))->ticket;
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
                    ? 'Nothing to sync yet — the ticket must be closed (and its booking authorized) with dispatched items. The job card syncs on close.'
                    : 'Sage sync: ' . ($result['error'] ?? 'unknown error')),
        ]);
    }
}
