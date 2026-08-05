@foreach (array_chunk(config('horse_mechanical_fields'), 3, true) as $chunk)
    <div class="row">
        @foreach ($chunk as $key => $field)
            <div class="col-md-4">
                <div class="form-group">
                    <label for="{{ $key }}">{{ $field['label'] }}</label>
                    <input type="text" class="form-control" list="{{ $key }}_options"
                        wire:model.debounce.300ms="{{ $key }}" placeholder="{{ $field['placeholder'] }}">
                    @if (!empty($field['options']))
                        <datalist id="{{ $key }}_options">
                            @foreach ($field['options'] as $option)
                                <option value="{{ $option }}">
                            @endforeach
                        </datalist>
                    @endif
                    @error($key) <span class="text-danger error">{{ $message }}</span>@enderror
                </div>
            </div>
        @endforeach
    </div>
@endforeach
