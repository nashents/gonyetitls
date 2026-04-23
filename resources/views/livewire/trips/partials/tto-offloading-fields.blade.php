{{-- resources/views/livewire/trips/partials/tto-offloading-fields.blade.php --}}
{{-- Expected: $ttoId, $cargo_type, $units_of_measures, $canEditRates, $hasTransporter --}}

<h6 class="font-weight-bold mt-4 mb-2 border-bottom pb-1">Offloading Details</h6>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Date <span class="text-danger">*</span></label>
            <input type="datetime-local" class="form-control"
                   wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.offloaded_date"
                   required>
            @error("deliveryNotes.$ttoId.offloaded_date")
                <span class="text-danger small">{{ $message }}</span>
            @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Distance</label>
            <input type="number" step="any" class="form-control"
                   wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.offloaded_distance"
                   placeholder="Trip distance">
            @error("deliveryNotes.$ttoId.offloaded_distance")
                <span class="text-danger small">{{ $message }}</span>
            @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Weight</label>
            <input type="number" step="any" class="form-control"
                   wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.offloaded_weight"
                   placeholder="Offloaded weight">
            @error("deliveryNotes.$ttoId.offloaded_weight")
                <span class="text-danger small">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>

@if ($cargo_type === 'Solid')
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Quantity</label>
                <input type="number" step="any" class="form-control"
                       wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.offloaded_quantity"
                       placeholder="Offloaded quantity">
                @error("deliveryNotes.$ttoId.offloaded_quantity")
                    <span class="text-danger small">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Unit of Measure</label>
                <select class="form-control"
                        wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.units_of_measure_id"
                        disabled>
                    <option value="">Select UOM</option>
                    @foreach ($units_of_measures as $uom)
                        <option value="{{ $uom->id }}">{{ $uom->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
@elseif ($cargo_type === 'Liquid')
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label>Litreage @ Ambient</label>
                <input type="number" step="any" class="form-control"
                       wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.offloaded_litreage"
                       placeholder="Litreage @ ambient">
                @error("deliveryNotes.$ttoId.offloaded_litreage")
                    <span class="text-danger small">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>Litreage @ 20°C</label>
                <input type="number" step="any" class="form-control"
                       wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.offloaded_litreage_at_20"
                       placeholder="Litreage @ 20°C">
                @error("deliveryNotes.$ttoId.offloaded_litreage_at_20")
                    <span class="text-danger small">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>Unit of Measure</label>
                <select class="form-control"
                        wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.units_of_measure_id"
                        disabled>
                    <option value="">Select UOM</option>
                    @foreach ($units_of_measures as $uom)
                        <option value="{{ $uom->id }}">{{ $uom->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
@endif

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Currency</label>
            <select class="form-control" wire:model.debounce.300ms="currency_id" disabled>
                <option value="">Select Currency</option>
                @foreach ($currencies ?? [] as $currency)
                    <option value="{{ $currency->id }}">
                        {{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Customer Rate</label>
            <input type="number" step="any" class="form-control"
                   wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.offloaded_rate"
                   placeholder="Offloading rate"
                   {{ ! $canEditRates ? 'disabled' : '' }}>
            @error("deliveryNotes.$ttoId.offloaded_rate")
                <span class="text-danger small">{{ $message }}</span>
            @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Customer Freight</label>
            <input type="number" step="any" class="form-control"
                   wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.offloaded_freight"
                   placeholder="Offloading freight"
                   {{ ! $canEditRates ? 'disabled' : '' }}>
            @error("deliveryNotes.$ttoId.offloaded_freight")
                <span class="text-danger small">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>

@if ($hasTransporter)
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Transporter Rate</label>
                <input type="number" step="any" class="form-control"
                       wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.transporter_offloaded_rate"
                       placeholder="Transporter offloading rate"
                       {{ ! $canEditRates ? 'disabled' : '' }}>
                @error("deliveryNotes.$ttoId.transporter_offloaded_rate")
                    <span class="text-danger small">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Transporter Freight</label>
                <input type="number" step="any" class="form-control"
                       wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.transporter_offloaded_freight"
                       placeholder="Transporter offloading freight"
                       {{ ! $canEditRates ? 'disabled' : '' }}>
                @error("deliveryNotes.$ttoId.transporter_offloaded_freight")
                    <span class="text-danger small">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>
@endif

<div class="form-group mt-2">
    <label>Comments</label>
    <textarea class="form-control"
              wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.comments"
              placeholder="Additional offloading notes..."
              rows="3"></textarea>
    @error("deliveryNotes.$ttoId.comments")
        <span class="text-danger small">{{ $message }}</span>
    @enderror
</div>