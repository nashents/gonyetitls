{{-- Link status for exactly two selected trailers (HandlesTripTrailerLinks). --}}
@php($trailerLinkState = $this->trailerLinkState)
@if ($trailerLinkState === 'can_link')
    <div class="row">
        <div class="col-md-12" title="These 2 trailers aren't linked yet. Tick to save them as a trailer link when the trip is saved - picking either one on a future trip will then add the other automatically.">
            <input type="checkbox" id="create_trailer_link" wire:model="create_trailer_link" class="line-style" />
            <label for="create_trailer_link" class="radio-label">Link trailers <i class="fa fa-info-circle text-muted"></i></label>
        </div>
    </div>
@elseif ($trailerLinkState === 'linked')
    <small class="text-success"><i class="fa fa-link"></i> These trailers are linked.</small>
@elseif ($trailerLinkState === 'conflict')
    <small class="text-warning"><i class="fa fa-chain-broken"></i> One of these trailers is already linked to another trailer. Update it under <a href="{{ route('trailer_links.index') }}" target="_blank">Trailer Links</a> to link them.</small>
@endif
