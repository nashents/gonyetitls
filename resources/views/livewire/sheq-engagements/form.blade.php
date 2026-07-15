<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Type<span class="required" style="color: red">*</span></label>
            <select class="form-control" wire:model.debounce.300ms="type" required>
                <option value="">Select Option</option>
                <option value="walk">Leadership Walk / Visible Felt Leadership</option>
                <option value="observation">Planned Job Observation</option>
                <option value="stand_down">Stand-Down Session</option>
                <option value="inspection_tour">Inspection Tour</option>
            </select>
            @error('type') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Leader<span class="required" style="color: red">*</span></label>
            <select class="form-control" wire:model.debounce.300ms="leader_id" required>
                <option value="">Select Option</option>
                @foreach ($employees as $employee)
                    <option value="{{$employee->id}}">{{$employee->name}} {{$employee->surname}}</option>
                @endforeach
            </select>
            @error('leader_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Date<span class="required" style="color: red">*</span></label>
            <input type="date" class="form-control" wire:model.debounce.300ms="engagement_date" required>
            @error('engagement_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Area / Location Visited</label>
            <input type="text" class="form-control" wire:model.debounce.300ms="area">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Status</label>
            <select class="form-control" wire:model.debounce.300ms="status">
                <option value="open">Open</option>
                <option value="closed">Closed</option>
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label>Observations</label>
            <textarea class="form-control" wire:model.debounce.300ms="observations" rows="2"></textarea>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Positive Findings</label>
            <textarea class="form-control" wire:model.debounce.300ms="positives" rows="2"></textarea>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Concerns / Issues Raised</label>
            <textarea class="form-control" wire:model.debounce.300ms="concerns" rows="2"></textarea>
        </div>
    </div>
</div>
