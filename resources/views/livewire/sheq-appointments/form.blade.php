<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Appointee<span class="required" style="color: red">*</span></label>
            <select class="form-control" wire:model.debounce.300ms="employee_id" required>
                <option value="">Select Option</option>
                @foreach ($employees as $employee)
                    <option value="{{$employee->id}}">{{$employee->name}} {{$employee->surname}}</option>
                @endforeach
            </select>
            @error('employee_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Appointment Title<span class="required" style="color: red">*</span></label>
            <input type="text" class="form-control" wire:model.debounce.300ms="title" placeholder="e.g. First Aider, Fire Warden, HCS Coordinator, SHE Representative" required>
            @error('title') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Type<span class="required" style="color: red">*</span></label>
            <select class="form-control" wire:model.debounce.300ms="type" required>
                <option value="statutory">Statutory / Legal</option>
                <option value="functional">Functional</option>
            </select>
            @error('type') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Department</label>
            <select class="form-control" wire:model.debounce.300ms="department_id">
                <option value="">Select Option</option>
                @foreach ($departments as $department)
                    <option value="{{$department->id}}">{{$department->name}}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Appointed By</label>
            <select class="form-control" wire:model.debounce.300ms="appointed_by_id">
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
            <label>Start Date<span class="required" style="color: red">*</span></label>
            <input type="date" class="form-control" wire:model.debounce.300ms="start_date" required>
            @error('start_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
                <option value="active">Active</option>
                <option value="expired">Expired</option>
                <option value="revoked">Revoked</option>
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label>Responsibilities / Notes</label>
            <textarea class="form-control" wire:model.debounce.300ms="notes" rows="2"></textarea>
        </div>
    </div>
</div>
