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
            <label>Source</label>
            <select class="form-control" wire:model.debounce.300ms="source">
                <option value="">Select Option</option>
                <option value="audit">Audit</option>
                <option value="incident">Incident</option>
                <option value="inspection">Inspection</option>
                <option value="drill">Emergency Drill</option>
                <option value="meeting">Meeting</option>
                <option value="risk_assessment">Risk Assessment</option>
                <option value="non_conformity">Non-Conformity</option>
                <option value="hygiene_survey">Hygiene Survey</option>
                <option value="change">Change Management</option>
                <option value="engagement">Leadership Engagement</option>
                <option value="management_review">Management Review</option>
                <option value="evaluation">Compliance Evaluation</option>
                <option value="other">Other</option>
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
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
    <div class="col-md-6">
        <div class="form-group">
            <label>Responsible Person<span class="required" style="color: red">*</span></label>
            <select class="form-control" wire:model.debounce.300ms="employee_id" required>
                <option value="">Select Option</option>
                @foreach ($employees as $employee)
                    <option value="{{$employee->id}}">{{$employee->name}} {{$employee->surname}}</option>
                @endforeach
            </select>
            @error('employee_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Priority</label>
            <select class="form-control" wire:model.debounce.300ms="priority">
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Due Date<span class="required" style="color: red">*</span></label>
            <input type="date" class="form-control" wire:model.debounce.300ms="due_date" required>
            @error('due_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Reference</label>
            <input type="text" class="form-control" wire:model.debounce.300ms="reference" placeholder="e.g. source record number">
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label>Description</label>
            <textarea class="form-control" wire:model.debounce.300ms="description" cols="30" rows="3"></textarea>
        </div>
    </div>
</div>
