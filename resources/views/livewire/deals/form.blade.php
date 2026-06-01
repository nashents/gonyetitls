<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Deal Number <span class="required" style="color:red">*</span></label>
            <input type="text" class="form-control" wire:model.debounce.300ms="deal_number" placeholder="Enter deal number">
            @error('deal_number') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>Reference</label>
            <input type="text" class="form-control" wire:model.debounce.300ms="reference" placeholder="Enter reference">
            @error('reference') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Customer</label>
            <select class="form-control" wire:model.debounce.300ms="customer_id">
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
            <label>Cargo</label>
            <select class="form-control" wire:model.debounce.300ms="cargo_id">
                <option value="">Select Cargo</option>
                @foreach ($cargos as $cargo)
                    <option value="{{ $cargo->id }}">{{ $cargo->name }}</option>
                @endforeach
            </select>
            @error('cargo_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
            <label>Weight</label>
            <input type="number" step="0.01" class="form-control" wire:model.debounce.300ms="weight" placeholder="Enter weight">
            @error('weight') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label>Litreage</label>
            <input type="number" step="0.01" class="form-control" wire:model.debounce.300ms="litreage" placeholder="Enter litreage">
            @error('litreage') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label>Quantity</label>
            <input type="number" step="0.01" class="form-control" wire:model.debounce.300ms="quantity" placeholder="Enter quantity">
            @error('quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
            <label>End Date</label>
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