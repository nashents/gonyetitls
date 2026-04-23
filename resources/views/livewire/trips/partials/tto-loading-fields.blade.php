{{-- resources/views/livewire/trips/partials/tto-loading-fields.blade.php --}}
{{-- Expected: $ttoId, $cargo_type, $units_of_measures, $canEditRates, $hasTransporter --}}

<h6 class="font-weight-bold mt-3 mb-2 border-bottom pb-1">Loading Details</h6>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Date <span class="text-danger">*</span></label>
            <input type="datetime-local" class="form-control"
                   wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.loaded_date"
                   {{ $selectedStatus === 'Offloaded' ? 'disabled' : '' }}
                   required>
            @error("deliveryNotes.$ttoId.loaded_date")
                <span class="text-danger small">{{ $message }}</span>
            @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Distance</label>
            <input type="number" step="any" class="form-control"
                   wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.distance"
                   placeholder="Trip distance"
                   {{ $selectedStatus === 'Offloaded' ? 'disabled' : '' }}>
            @error("deliveryNotes.$ttoId.distance")
                <span class="text-danger small">{{ $message }}</span>
            @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Weight</label>
            <input type="number" step="any" class="form-control"
                   wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.loaded_weight"
                   placeholder="Loading weight"
                   {{ $selectedStatus === 'Offloaded' ? 'disabled' : '' }}>
            @error("deliveryNotes.$ttoId.loaded_weight")
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
                       wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.loaded_quantity"
                       placeholder="Loaded quantity"
                       {{ $selectedStatus === 'Offloaded' ? 'disabled' : '' }}>
                @error("deliveryNotes.$ttoId.loaded_quantity")
                    <span class="text-danger small">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Unit of Measure</label>
                <select class="form-control"
                        wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.units_of_measure_id"
                        {{ $selectedStatus === 'Offloaded' ? 'disabled' : '' }}>
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
                       wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.loaded_litreage"
                       placeholder="Litreage @ ambient"
                       {{ $selectedStatus === 'Offloaded' ? 'disabled' : '' }}>
                @error("deliveryNotes.$ttoId.loaded_litreage")
                    <span class="text-danger small">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>Litreage @ 20°C</label>
                <input type="number" step="any" class="form-control"
                       wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.loaded_litreage_at_20"
                       placeholder="Litreage @ 20°C"
                       {{ $selectedStatus === 'Offloaded' ? 'disabled' : '' }}>
                @error("deliveryNotes.$ttoId.loaded_litreage_at_20")
                    <span class="text-danger small">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>Unit of Measure</label>
                <select class="form-control"
                        wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.units_of_measure_id"
                        {{ $selectedStatus === 'Offloaded' ? 'disabled' : '' }}>
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
                   wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.loaded_rate"
                   placeholder="Customer rate"
                   {{ ($selectedStatus === 'Offloaded' || ! $canEditRates) ? 'disabled' : '' }}>
            @error("deliveryNotes.$ttoId.loaded_rate")
                <span class="text-danger small">{{ $message }}</span>
            @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Customer Freight</label>
            <input type="number" step="any" class="form-control"
                   wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.loaded_freight"
                   placeholder="Customer freight"
                   {{ ($selectedStatus === 'Offloaded' || ! $canEditRates) ? 'disabled' : '' }}>
            @error("deliveryNotes.$ttoId.loaded_freight")
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
                       wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.transporter_loaded_rate"
                       placeholder="Transporter rate"
                       {{ ($selectedStatus === 'Offloaded' || ! $canEditRates) ? 'disabled' : '' }}>
                @error("deliveryNotes.$ttoId.transporter_loaded_rate")
                    <span class="text-danger small">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Transporter Freight</label>
                <input type="number" step="any" class="form-control"
                       wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.transporter_loaded_freight"
                       placeholder="Transporter freight"
                       {{ ($selectedStatus === 'Offloaded' || ! $canEditRates) ? 'disabled' : '' }}>
                @error("deliveryNotes.$ttoId.transporter_loaded_freight")
                    <span class="text-danger small">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>
@endif