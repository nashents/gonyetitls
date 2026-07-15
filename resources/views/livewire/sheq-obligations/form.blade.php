<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Title<span class="required" style="color: red">*</span></label>
            <input type="text" class="form-control" wire:model.debounce.300ms="title" required>
            @error('title') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Type<span class="required" style="color: red">*</span></label>
            <select class="form-control" wire:model.debounce.300ms="type" required>
                <option value="">Select Option</option>
                <option value="permit">Permit</option>
                <option value="licence">Licence</option>
                <option value="legal">Legal / Statutory</option>
                <option value="agreement">Agreement / SLA</option>
                <option value="exemption">Exemption</option>
                <option value="guideline">Regulator Guideline</option>
                <option value="other">Other</option>
            </select>
            @error('type') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Issuing Authority / Origin</label>
            <input type="text" class="form-control" wire:model.debounce.300ms="issuing_authority">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Reference Number</label>
            <input type="text" class="form-control" wire:model.debounce.300ms="reference_number">
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
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
    <div class="col-md-6">
        <div class="form-group">
            <label>Responsible Person</label>
            <select class="form-control" wire:model.debounce.300ms="employee_id">
                <option value="">Select Option</option>
                @foreach ($employees as $employee)
                    <option value="{{$employee->id}}">{{$employee->name}} {{$employee->surname}}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Issue Date</label>
            <input type="date" class="form-control" wire:model.debounce.300ms="issue_date">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Expiry Date</label>
            <input type="date" class="form-control" wire:model.debounce.300ms="expiry_date">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Status</label>
            <select class="form-control" wire:model.debounce.300ms="status">
                <option value="valid">Valid</option>
                <option value="pending_renewal">Pending Renewal</option>
                <option value="expired">Expired</option>
                <option value="not_applicable">No Longer Applicable</option>
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label>Key Requirements / Conditions</label>
            <textarea class="form-control" wire:model.debounce.300ms="requirements" cols="30" rows="3"></textarea>
        </div>
    </div>
</div>
