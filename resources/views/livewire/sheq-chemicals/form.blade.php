<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Chemical Name<span class="required" style="color: red">*</span></label>
            <input type="text" class="form-control" wire:model.debounce.300ms="name" required>
            @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Trade Name</label>
            <input type="text" class="form-control" wire:model.debounce.300ms="trade_name">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Supplier</label>
            <input type="text" class="form-control" wire:model.debounce.300ms="supplier">
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Hazard Class</label>
            <select class="form-control" wire:model.debounce.300ms="hazard_class">
                <option value="">Select Option</option>
                <option value="flammable">Flammable</option>
                <option value="corrosive">Corrosive</option>
                <option value="toxic">Toxic</option>
                <option value="oxidising">Oxidising</option>
                <option value="explosive">Explosive</option>
                <option value="compressed_gas">Compressed Gas</option>
                <option value="health_hazard">Health Hazard</option>
                <option value="environmental_hazard">Environmental Hazard</option>
                <option value="irritant">Irritant</option>
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Department<span class="required" style="color: red">*</span></label>
            <select class="form-control" wire:model.debounce.300ms="department_id" required>
                <option value="">Select Option</option>
                @foreach ($departments as $department)
                    <option value="{{$department->id}}">{{$department->name}}</option>
                @endforeach
            </select>
            @error('department_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Storage Location</label>
            <input type="text" class="form-control" wire:model.debounce.300ms="storage_location">
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>Quantity</label>
            <input type="text" class="form-control" wire:model.debounce.300ms="quantity">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Unit of Measure</label>
            <select class="form-control" wire:model.debounce.300ms="unit_of_measure">
                <option value="">Select Option</option>
                @foreach ($unit_of_measures as $uom)
                    <option value="{{$uom->name}}">{{$uom->name}}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>SDS Available?</label>
            <select class="form-control" wire:model.debounce.300ms="sds_available">
                <option value="0">No</option>
                <option value="1">Yes</option>
            </select>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>SDS Review Date</label>
            <input type="date" class="form-control" wire:model.debounce.300ms="sds_review_date">
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>Storage Bunded?</label>
            <select class="form-control" wire:model.debounce.300ms="storage_bunded">
                <option value="0">No</option>
                <option value="1">Yes</option>
            </select>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Spill Kit Available?</label>
            <select class="form-control" wire:model.debounce.300ms="spill_kit_available">
                <option value="0">No</option>
                <option value="1">Yes</option>
            </select>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>HCS Coordinator</label>
            <select class="form-control" wire:model.debounce.300ms="coordinator_id">
                <option value="">Select Option</option>
                @foreach ($employees as $employee)
                    <option value="{{$employee->id}}">{{$employee->name}} {{$employee->surname}}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Status</label>
            <select class="form-control" wire:model.debounce.300ms="status">
                <option value="in_use">In Use</option>
                <option value="phased_out">Phased Out</option>
                <option value="banned">Banned</option>
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Licence / Permit Required</label>
            <input type="text" class="form-control" wire:model.debounce.300ms="licence_required">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Incompatible With</label>
            <textarea class="form-control" wire:model.debounce.300ms="incompatible_with" rows="2"></textarea>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>PPE Required</label>
            <textarea class="form-control" wire:model.debounce.300ms="ppe_required" rows="2"></textarea>
        </div>
    </div>
</div>
