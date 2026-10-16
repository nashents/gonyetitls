{{-- Trailer lines for the trip create/edit forms (HandlesTripTrailerLinks). --}}
<div class="row">  
    <div class="col-md-12">
    <div class="form-group">
        <label for="trailers"><a href="{{ route('trailers.index') }}" target="_blank" style="color: blue">Trailer(s)</a></label>
        <input type="checkbox" wire:model.debounce.300ms="all_trailers"   class="line-style" />
        <label for="one" class="radio-label">Select from all trailers</label>
            <input type="text" wire:model.debounce.300ms="searchTrailer" placeholder="Search with reg..." class="form-control">
            <select class="form-control" wire:model.debounce.300ms="trailer_id.0" size="4" >
              <option value="" disabled >Select Trailer </option>
              @if (!is_null($selectedTransporter) || !is_null($selectedBroker))
                @foreach ($trailers as $trailer)
                    <option value="{{$trailer->id}}"
                        @if(in_array($trailer->id, $trailer_id ?? []) && ($trailer_id[0] ?? null) != $trailer->id) 
                            disabled 
                        @endif
                    >{{$trailer->registration_number}} {{$trailer->make}} {{$trailer->model}} </option>
                @endforeach
                @endif
            </select>
            <small> <a href="{{ route('trailers.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Trailer</a></small> <a href="#" wire:click.prevent="refresh('trailers')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
            @error('trailer_id.0') <span class="text-danger error">{{ $message }}</span>@enderror
    </div>
</div>
</div>
{{-- <br> --}}

    @foreach ($trailer_inputs as $key => $value)
    <div class="row" wire:key="trip-trailer-row-{{ $value }}">
        <div class="col-md-10">
            <select class="form-control" wire:model.debounce.300ms="trailer_id.{{ $value }}" size="4" >
                <option value="" disabled>Select Trailer {{ $value }}</option>
                @if (!is_null($selectedTransporter) || !is_null($selectedBroker))
                    @foreach ($trailers as $trailer)
                        <option value="{{$trailer->id}}"
                            @if(in_array($trailer->id, $trailer_id ?? []) && ($trailer_id[$value] ?? null) != $trailer->id) 
                                disabled 
                            @endif
                            >{{$trailer->registration_number}} {{$trailer->make}} {{$trailer->model}} </option>
                    @endforeach
                  @endif
              </select>
            @error('trailer_id.'. $value) <span class="text-danger error">{{ $message }}</span>@enderror
        </div>
        <div class="col-md-1">
            <div class="form-group" style="margin-top:37px;">
                <label for=""></label>
                <button class="btn btn-danger btn-rounded btn-xs" style="margin-left:-25px;"   wire:click.prevent="trailerRemove({{$key}})"> <i class="fa fa-times"></i></button>
            </div>
        </div>
   
    </div>
    <br>
    @endforeach


<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <button class="btn btn-success btn-rounded btn-xs" style="float: right" wire:click.prevent="trailerAdd({{$t}})"> <i class="fa fa-plus"></i>Trailer</button>
        </div>
    </div>
</div>
@include('livewire.trips.partials.trailer-link-state')
