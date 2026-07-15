<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Emergency Scenario<span class="required" style="color: red">*</span></label>
            <select class="form-control" wire:model.debounce.300ms="sheq_emergency_id" required>
                <option value="">Select Option</option>
                @foreach ($emergencies as $emergency)
                    <option value="{{$emergency->id}}">{{$emergency->scenario}}</option>
                @endforeach
            </select>
            @error('sheq_emergency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
            <label>Coordinator</label>
            <select class="form-control" wire:model.debounce.300ms="coordinator_id">
                <option value="">Select Option</option>
                @foreach ($employees as $employee)
                    <option value="{{$employee->id}}">{{$employee->name}} {{$employee->surname}}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>Planned Date<span class="required" style="color: red">*</span></label>
            <input type="date" class="form-control" wire:model.debounce.300ms="planned_date" required>
            @error('planned_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Conducted Date</label>
            <input type="date" class="form-control" wire:model.debounce.300ms="conducted_date">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Participants</label>
            <input type="number" min="0" class="form-control" wire:model.debounce.300ms="participants_count">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Response Time</label>
            <input type="text" class="form-control" wire:model.debounce.300ms="response_time" placeholder="e.g. 4 min 30 sec">
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Evaluation / Observations</label>
            <textarea class="form-control" wire:model.debounce.300ms="evaluation" rows="2"></textarea>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Findings / Deviations</label>
            <textarea class="form-control" wire:model.debounce.300ms="findings" rows="2"></textarea>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Findings Communicated to Employees?</label>
            <select class="form-control" wire:model.debounce.300ms="findings_communicated">
                <option value="0">No</option>
                <option value="1">Yes</option>
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Status</label>
            <select class="form-control" wire:model.debounce.300ms="status">
                <option value="planned">Planned</option>
                <option value="conducted">Conducted</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>
    </div>
</div>
