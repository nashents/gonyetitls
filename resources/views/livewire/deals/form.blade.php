

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Reference</label>
            <input type="text" class="form-control" wire:model.debounce.300ms="reference" placeholder="Enter reference">
            @error('reference') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Customer<span class="required" style="color:red">*</span></label>
            <select class="form-control" wire:model.debounce.300ms="customer_id" required>
                <option value="">Select Customer</option>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                @endforeach
            </select>
            @error('customer_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Cargo<span class="required" style="color:red">*</span></label>
            <select class="form-control" wire:model.debounce.300ms="cargo_id" required>
                <option value="">Select Cargo</option>
                @foreach ($cargos as $cargo)
                    <option value="{{ $cargo->id }}">{{ $cargo->name }}</option>
                @endforeach
            </select>
            @error('cargo_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
</div>

<div class="row">
    @if (isset($cargo_type) && $cargo_type == "Solid")
        <div class="col-md-4">
            <div class="form-group">
                <label>Weight(t)</label>
                <input type="number" step="0.01" class="form-control" wire:model.debounce.300ms="weight" placeholder="Enter weight in tons">
                @error('weight') <span class="error" style="color:red">{{ $message }}</span> @enderror
            </div>
        </div>
    @elseif(isset($cargo_type) && $cargo_type == "Liquid")
        <div class="col-md-4">
            <div class="form-group">
                <label>Litreage(l)</label>
                <input type="number" step="0.01" class="form-control" wire:model.debounce.300ms="litreage" placeholder="Enter litreage in litres" >
                @error('litreage') <span class="error" style="color:red">{{ $message }}</span> @enderror
            </div>
        </div>
    @endif
    <div class="col-md-4">
        <div class="form-group">
            <label>Quantity</label>
            <input type="number" step="0.01" class="form-control" wire:model.debounce.300ms="quantity" placeholder="Enter quantity eg 650 Bag(s)">
            @error('quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Unit of Measure</label>
            <select class="form-control" wire:model.debounce.300ms="units_of_measure_id">
                <option value="">Select Unit</option>
                @foreach ($units_of_measures as $unit)
                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                @endforeach
            </select>
            @error('units_of_measure_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label for="country">Currencies</label>
            <select wire:model.debounce.300ms="currency_id" class="form-control">
                <option value="">Select Currency</option>
                @foreach ($currencies as $currency)
                <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>                                      
                @endforeach
            </select>
            @error('currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
            <small><a href="{{ route('currencies.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Currency</a></small> 
        </div>
    </div>
   <div class="col-md-4">
        <div class="form-group">
            <label>Rate</label>
            <input type="number" min="0" step="any" class="form-control" wire:model.debounce.300ms="rate" placeholder="Enter rate $$">
            @error('rate') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
   <div class="col-md-4">
        <div class="form-group">
            <label>Freight</label>
            <input type="number" min="0" step="any" class="form-control" wire:model.debounce.300ms="freight" placeholder="Enter total freight $$">
            @error('freight') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Start Date</label>
            <input type="datetime-local" class="form-control" wire:model.debounce.300ms="start_date">
            @error('start_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Expiry Date</label>
            <input type="datetime-local" class="form-control" wire:model.debounce.300ms="end_date">
            @error('end_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Status</label>
            <select class="form-control" wire:model.debounce.300ms="status">
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
            @error('status') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
</div>