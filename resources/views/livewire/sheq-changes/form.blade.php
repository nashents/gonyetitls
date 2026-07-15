<div class="row">
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
            <label>Requested By<span class="required" style="color: red">*</span></label>
            <select class="form-control" wire:model.debounce.300ms="requested_by_id" required>
                <option value="">Select Option</option>
                @foreach ($employees as $employee)
                    <option value="{{$employee->id}}">{{$employee->name}} {{$employee->surname}}</option>
                @endforeach
            </select>
            @error('requested_by_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Request Date<span class="required" style="color: red">*</span></label>
            <input type="date" class="form-control" wire:model.debounce.300ms="request_date" required>
            @error('request_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Type of Change</label>
            <select class="form-control" wire:model.debounce.300ms="type">
                <option value="permanent">Permanent</option>
                <option value="temporary">Temporary</option>
                <option value="emergency">Emergency</option>
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Issue-Based Risk Assessment</label>
            <select class="form-control" wire:model.debounce.300ms="sheq_risk_assessment_id">
                <option value="">Not Yet Conducted</option>
                @foreach ($assessments as $assessment)
                    <option value="{{$assessment->id}}">{{$assessment->assessment_number}} - {{$assessment->activity}}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Description of Change<span class="required" style="color: red">*</span></label>
            <textarea class="form-control" wire:model.debounce.300ms="description" rows="2" required></textarea>
            @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Reason for Change</label>
            <textarea class="form-control" wire:model.debounce.300ms="reason" rows="2"></textarea>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Implementation Date</label>
            <input type="date" class="form-control" wire:model.debounce.300ms="implementation_date">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Close-Out Date</label>
            <input type="date" class="form-control" wire:model.debounce.300ms="closeout_date">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Status</label>
            <select class="form-control" wire:model.debounce.300ms="status">
                <option value="open">Open</option>
                <option value="implementing">Implementing</option>
                <option value="closed">Closed</option>
            </select>
        </div>
    </div>
</div>
