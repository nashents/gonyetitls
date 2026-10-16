{{-- Link status for exactly two selected trailers (HandlesTripTrailerLinks). --}}
@php($trailerLinkState = $this->trailerLinkState)
@if ($trailerLinkState === 'can_link')
    <div class="row">
        <div class="col-md-12">
            <input type="checkbox" id="create_trailer_link" wire:model="create_trailer_link" class="line-style" />
            <label for="create_trailer_link" class="radio-label">Link these 2 trailers (saved with the trip)</label>
        </div>
    </div>
@elseif ($trailerLinkState === 'linked')
    <small class="text-success"><i class="fa fa-link"></i> These trailers are linked.</small>
@elseif ($trailerLinkState === 'conflict')
    <small class="text-warning"><i class="fa fa-chain-broken"></i> One of these trailers is already linked to another trailer. Update it under <a href="{{ route('trailer_links.index') }}" target="_blank">Trailer Links</a> to link them.</small>
@endif
