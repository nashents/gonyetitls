{{-- resources/views/livewire/trips/partials/freight-calc-radios.blade.php --}}
<h6 class="font-weight-bold mt-1 mb-2 border-bottom pb-1">Freight Calculation Method</h6>
<div class="mb-3">
    @foreach ([
        'flat_rate'            => 'Flat Rate',
        'rate_weight'          => 'Rate × Weight / Litreage',
        'rate_weight_distance' => 'Rate × Distance × Weight / Litreage',
        'rate_distance'        => 'Rate × Distance',
    ] as $val => $label)
        <input type="radio"
               wire:model.debounce.300ms="freight_calculation"
               value="{{ $val }}"
               class="line-style" />
        <label class="radio-label">{{ $label }}</label>
    @endforeach
    @error('freight_calculation')
        <span class="text-danger small d-block">{{ $message }}</span>
    @enderror
</div>