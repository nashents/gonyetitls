{{-- Job fields for Bulk Schedule: $prefix is '' for the shared job or "rows.N." for one trip; $job holds its current values. --}}
@php($sm = $prefix === '' ? '' : ' input-sm')
<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>Customer<span class="required" style="color: red">*</span></label>
            <select wire:model.defer="{{ $prefix }}customer_id" class="form-control{{ $sm }}">
                <option value="">Select Customer</option>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                @endforeach
            </select>
            @error($prefix.'customer_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label>Trip Type<span class="required" style="color: red">*</span></label>
            <select wire:model.defer="{{ $prefix }}trip_type_id" class="form-control{{ $sm }}">
                <option value="">Select</option>
                @foreach ($tripTypes as $tripType)
                    <option value="{{ $tripType->id }}">{{ $tripType->name }}</option>
                @endforeach
            </select>
            @error($prefix.'trip_type_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label>Cargo<span class="required" style="color: red">*</span></label>
            <select wire:model="{{ $prefix }}cargo_id" class="form-control{{ $sm }}">
                <option value="">Select</option>
                @foreach ($cargos as $cargo)
                    <option value="{{ $cargo->id }}">{{ $cargo->name }}{{ $cargo->type ? ' ('.$cargo->type.')' : '' }}</option>
                @endforeach
            </select>
            @error($prefix.'cargo_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label>Consignee</label>
            <select wire:model.defer="{{ $prefix }}consignee_id" class="form-control{{ $sm }}">
                <option value="">None</option>
                @foreach ($consignees as $consignee)
                    <option value="{{ $consignee->id }}">{{ $consignee->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label>Consignor</label>
            <select wire:model.defer="{{ $prefix }}consignor_id" class="form-control{{ $sm }}">
                <option value="">None</option>
                @foreach ($consignors as $consignor)
                    <option value="{{ $consignor->id }}">{{ $consignor->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>From<span class="required" style="color: red">*</span></label>
            <select wire:model.defer="{{ $prefix }}from" class="form-control{{ $sm }}">
                <option value="">Select Origin</option>
                @foreach ($destinations as $destination)
                    <option value="{{ $destination->id }}">{{ $destination->country ? $destination->country->name : '' }} {{ $destination->city }}</option>
                @endforeach
            </select>
            @error($prefix.'from') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Loading Point</label>
            <select wire:model.defer="{{ $prefix }}loading_point_id" class="form-control{{ $sm }}">
                <option value="">Select Loading Point</option>
                @foreach ($loadingPoints as $point)
                    <option value="{{ $point->id }}">{{ ucfirst($point->name) }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>To<span class="required" style="color: red">*</span></label>
            <select wire:model.defer="{{ $prefix }}to" class="form-control{{ $sm }}">
                <option value="">Select Destination</option>
                @foreach ($destinations as $destination)
                    <option value="{{ $destination->id }}">{{ $destination->country ? $destination->country->name : '' }} {{ ucfirst($destination->city) }}</option>
                @endforeach
            </select>
            @error($prefix.'to') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Offloading Point</label>
            <select wire:model.defer="{{ $prefix }}offloading_point_id" class="form-control{{ $sm }}">
                <option value="">Select Offloading Point</option>
                @foreach ($offloadingPoints as $point)
                    <option value="{{ $point->id }}">{{ ucfirst($point->name) }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>Freight Calculation<span class="required" style="color: red">*</span></label>
            <select wire:model="{{ $prefix }}freight_calculation" class="form-control{{ $sm }}">
                @foreach (\App\Http\Livewire\Trips\BulkCreate::FREIGHT_CALCULATIONS as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            @error($prefix.'freight_calculation') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label>Currency<span class="required" style="color: red">*</span></label>
            <select wire:model="{{ $prefix }}currency_id" class="form-control{{ $sm }}">
                <option value="">Select</option>
                @foreach ($currencies as $currency)
                    <option value="{{ $currency->id }}">{{ $currency->name }}</option>
                @endforeach
            </select>
            @error($prefix.'currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    @if ($this->isForeign($job['currency_id']))
        <div class="col-md-2">
            <div class="form-group">
                <label>Conversion Rate<span class="required" style="color: red">*</span></label>
                <input type="number" step="any" min="0" wire:model.lazy="{{ $prefix }}exchange_rate" class="form-control{{ $sm }}">
                @error($prefix.'exchange_rate') <span class="error" style="color:red">{{ $message }}</span> @enderror
            </div>
        </div>
    @endif
    <div class="col-md-2">
        <div class="form-group">
            <label>Customer Rate<span class="required" style="color: red">*</span></label>
            <input type="number" step="any" min="0" wire:model.lazy="{{ $prefix }}rate" class="form-control{{ $sm }}">
            @error($prefix.'rate') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-1">
        <div class="form-group">
            <label>Km @if (\App\Http\Livewire\Trips\BulkCreate::usesDistance($job['freight_calculation']))<span class="required" style="color: red">*</span>@endif</label>
            <input type="number" step="any" min="0" wire:model.lazy="{{ $prefix }}distance" class="form-control{{ $sm }}">
            @error($prefix.'distance') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-1">
        <div class="form-group">
            <label>UOM</label>
            <select wire:model.defer="{{ $prefix }}units_of_measure_id" class="form-control{{ $sm }}">
                <option value=""></option>
                @foreach ($unitsOfMeasures as $uom)
                    <option value="{{ $uom->id }}">{{ $uom->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-{{ $this->isForeign($job['currency_id']) ? 1 : 3 }}">
        <div class="form-group">
            <label>Cargo Details</label>
            <input type="text" wire:model.defer="{{ $prefix }}cargo_details" class="form-control{{ $sm }}" placeholder="Optional">
        </div>
    </div>
</div>
