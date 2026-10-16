<?php

namespace App\Http\Livewire\Concerns;

use App\Models\Assignment;
use App\Models\Trailer;
use App\Models\TrailerAssignment;
use App\Models\TrailerLink;
use App\Services\TripAssetAssignmentService;
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
    public $assign_to_horse = false;

    // Trailer lines: row 0 is the fixed first select; each extra row (one
    // entry in $trailer_inputs) binds trailer_id.{index}. New rows take the
    // next free index so pre-filled ids always have a visible row.
    public function trailerAdd($t = null)
    {
        $used = array_merge([0], array_keys((array) $this->trailer_id), $this->trailer_inputs);
        $t = max(array_map('intval', $used)) + 1;
        $this->t = $t;
        array_push($this->trailer_inputs, $t);
        return $t;
    }

    public function trailerRemove($t)
    {
        $index = $this->trailer_inputs[$t] ?? null;
        unset($this->trailer_inputs[$t]);
        if ($index !== null && is_array($this->trailer_id)) {
            unset($this->trailer_id[$index]);
        }
        $this->create_trailer_link = false;
    }

    /**
     * Replace the trailer selection with $ids, one visible line per trailer.
     * $withPartners also pulls in each trailer's linked partner (new picks,
     * not when loading a saved trip as-is).
     */
    protected function setTrailerRows(array $ids, bool $withPartners = true)
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if ($withPartners) {
            foreach ($ids as $id) {
                $partnerId = TrailerLink::partnerOf($id);
                if ($partnerId && !in_array($partnerId, $ids)) {
                    $ids[] = $partnerId;
                }
            }
        }
        $this->trailer_id = $ids;
        $this->trailer_inputs = count($ids) > 1 ? range(1, count($ids) - 1) : [];
        $this->t = max(1, count($ids) - 1);
        $this->create_trailer_link = false;
        $this->ensureSelectedTrailersListed();
    }

    /** Picking a linked trailer adds its partner on an empty line (or a new one). */
    public function updatedTrailerId($value, $key)
    {
        $this->create_trailer_link = false;
        if (empty($value)) {
            return;
        }
        $partner = $this->linkedPartnerToAdd($value);
        if (!$partner) {
            return;
        }
        $index = null;
        if ((string) $key !== '0' && empty($this->trailer_id[0] ?? null)) {
            $index = 0;
        }
        foreach ($this->trailer_inputs as $row) {
            if ($index === null && (string) $row !== (string) $key && empty($this->trailer_id[$row] ?? null)) {
                $index = $row;
            }
        }
        if ($index === null) {
            $index = $this->trailerAdd();
        }
        $this->trailer_id[$index] = $partner->id;
        $this->ensureSelectedTrailersListed();
        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => "Linked trailer {$partner->registration_number} added automatically.",
        ]);
    }

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
        $listed = collect($this->trailers ?? [])->map(fn ($t) => (int) data_get($t, 'id'))->filter()->all();
        if (empty(array_diff($ids, $listed))) {
            return;
        }
        // Re-query instead of merging: a merged collect() is a plain Collection,
        // which Livewire dehydrates to arrays ("Attempt to read property id on array").
        $this->trailers = Trailer::whereIn('id', array_unique(array_merge($listed, $ids)))
            ->orderByIdentifier('asc')->get();
    }

    /**
     * "Assign to horse" checkbox: null = nothing to assign (no horse, or no
     * driver/trailers), 'assigned' = the horse already has exactly this
     * driver and these trailers, 'can_assign' = offer the checkbox.
     */
    public function getHorseAssignmentStateProperty(): ?string
    {
        $horseId = ($this->mode_of_transport ?? null) === 'Horse' ? $this->selectedHorse : null;
        $trailerIds = $this->with_trailer ? $this->selectedTrailerIds() : [];
        if (!$horseId || (!$this->driver_id && empty($trailerIds))) {
            return null;
        }

        $driverOk = !$this->driver_id || Assignment::where('status', 1)
            ->where('horse_id', $horseId)->where('driver_id', $this->driver_id)->exists();
        $current = TrailerAssignment::where('status', 1)->where('horse_id', $horseId)
            ->pluck('trailer_id')->map(fn ($id) => (int) $id)->sort()->values()->all();
        $wanted = collect($trailerIds)->sort()->values()->all();
        $trailersOk = empty($trailerIds) || $current === $wanted;

        return $driverOk && $trailersOk ? 'assigned' : 'can_assign';
    }

    /** Make the trip's driver/trailers the horse's active assignments when ticked. */
    protected function saveHorseAssignmentsIfRequested($trip): void
    {
        if (!$this->assign_to_horse || !$trip->horse_id) {
            return;
        }
        app(TripAssetAssignmentService::class)->assign(
            $trip,
            $this->with_trailer ? $this->selectedTrailerIds() : []
        );
        $this->assign_to_horse = false;
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
