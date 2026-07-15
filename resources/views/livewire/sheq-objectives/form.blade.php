<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>Year<span class="required" style="color: red">*</span></label>
            <input type="number" class="form-control" wire:model.debounce.300ms="year" required>
            @error('year') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Category</label>
            <select class="form-control" wire:model.debounce.300ms="category">
                <option value="">Select Option</option>
                <option value="safety">Safety</option>
                <option value="health">Health</option>
                <option value="environment">Environment</option>
                <option value="quality">Quality</option>
            </select>
        </div>
    </div>
    <div class="col-md-3">
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
    <div class="col-md-3">
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
    <div class="col-md-12">
        <div class="form-group">
            <label>Objective<span class="required" style="color: red">*</span></label>
            <textarea class="form-control" wire:model.debounce.300ms="objective" rows="2" required></textarea>
            @error('objective') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>KPI / Measure</label>
            <input type="text" class="form-control" wire:model.debounce.300ms="kpi">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Baseline</label>
            <input type="text" class="form-control" wire:model.debounce.300ms="baseline">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Target<span class="required" style="color: red">*</span></label>
            <input type="text" class="form-control" wire:model.debounce.300ms="target" required>
            @error('target') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Programme / Plan to Achieve</label>
            <textarea class="form-control" wire:model.debounce.300ms="programme" rows="2" placeholder="Key activities, resources and milestones"></textarea>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Due Date</label>
            <input type="date" class="form-control" wire:model.debounce.300ms="due_date">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Status</label>
            <select class="form-control" wire:model.debounce.300ms="status">
                <option value="open">Open</option>
                <option value="on_track">On Track</option>
                <option value="at_risk">At Risk</option>
                <option value="achieved">Achieved</option>
                <option value="not_achieved">Not Achieved</option>
            </select>
        </div>
    </div>
</div>
