<?php

namespace App\Http\Livewire\Concerns;

use App\Models\Trailer;
use App\Models\TrailerLink;
use Illuminate\Support\Facades\Auth;

/**
 * Trailer-link awareness for the trip forms:
 *  - picking a trailer that belongs to a link pulls its partner in too;
 *  - picking two unlinked trailers offers a "link these trailers" checkbox
 *    ($create_trailer_link), honoured on save via saveTrailerLinkIfRequested().
 *
 * The host component owns $trailer_id, $trailers, $selectedTransporter and
 * $selectedStatus, and decides where a partner goes (addLinkedTrailer()).
 */
trait HandlesTripTrailerLinks
{
    public $create_trailer_link = false;

    /** Selected trailer ids, de-duplicated, empties dropped. */
    protected function selectedTrailerIds(): array
    {
        $ids = is_array($this->trailer_id) ? $this->trailer_id : [$this->trailer_id];
        return array_values(array_unique(array_filter(array_map('intval', $ids))));
    }

    /**
     * Link state for the current selection, used by the view:
     *  null       – nothing to show (not exactly two trailers);
     *  'linked'   – the two trailers are already linked;
     *  'conflict' – one of them is linked to a different trailer;
     *  'can_link' – neither is linked; offer the checkbox.
     */
    public function getTrailerLinkStateProperty(): ?string
    {
        $ids = $this->selectedTrailerIds();
        if (!$this->with_trailer || count($ids) !== 2) {
            return null;
        }
        if (TrailerLink::between($ids[0], $ids[1])) {
            return 'linked';
        }
        if (TrailerLink::forTrailer($ids[0])->exists() || TrailerLink::forTrailer($ids[1])->exists()) {
            return 'conflict';
        }
        return 'can_link';
    }

    /**
     * The linked partner of $trailerId when it should be auto-added, or null.
     * Archived partners are skipped; partners busy on another trip are skipped
     * (with a warning) unless the trip status lets unavailable trailers be used.
     */
    protected function linkedPartnerToAdd($trailerId): ?Trailer
    {
        $partnerId = TrailerLink::partnerOf($trailerId);
        if (!$partnerId || in_array($partnerId, $this->selectedTrailerIds())) {
            return null;
        }
        $partner = Trailer::find($partnerId);
        if (!$partner || $partner->archive) {
            return null;
        }
        $specialStatuses = ['Scheduled', 'Offloaded', 'Cancelled'];
        if (!in_array($this->selectedStatus ?? null, $specialStatuses, true) && ((int) $partner->status !== 1 || $partner->service)) {
            $this->dispatchBrowserEvent('alert', [
                'type' => 'warning',
                'message' => "Linked trailer {$partner->registration_number} is not available, so it was not added.",
            ]);
            return null;
        }
        return $partner;
    }

    /** Make sure every selected trailer is present in the dropdown list. */
    protected function ensureSelectedTrailersListed(): void
    {
        $ids = $this->selectedTrailerIds();
        if (empty($ids)) {
            return;
        }
        $list = collect($this->trailers ?? []);
        $missing = array_diff($ids, $list->pluck('id')->map(fn ($id) => (int) $id)->all());
        if (!empty($missing)) {
            $this->trailers = $list->merge(Trailer::whereIn('id', $missing)->get())->values();
        }
    }

    /** Create the link for the two selected trailers when the user ticked the box. */
    protected function saveTrailerLinkIfRequested(): void
    {
        if (!$this->create_trailer_link || $this->trailerLinkState !== 'can_link') {
            return;
        }
        [$a, $b] = $this->selectedTrailerIds();
        $link = new TrailerLink;
        $link->user_id = Auth::id();
        $link->transporter_id = $this->selectedTransporter ?: Trailer::find($a)?->transporter_id;
        $link->trailer_a = $a;
        $link->trailer_b = $b;
        $link->save();
        $this->create_trailer_link = false;
    }
}
