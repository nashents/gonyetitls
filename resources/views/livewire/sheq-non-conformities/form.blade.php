<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Source<span class="required" style="color: red">*</span></label>
            <select class="form-control" wire:model.debounce.300ms="source" required>
                <option value="">Select Option</option>
                <option value="audit">Audit</option>
                <option value="inspection">Inspection</option>
                <option value="incident">Incident</option>
                <option value="customer_complaint">Customer Complaint</option>
                <option value="contractor">Contractor</option>
                <option value="process">Process / Product</option>
                <option value="stop_and_fix">Stop & Fix / Stop Note</option>
                <option value="other">Other</option>
            </select>
            @error('source') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
            <label>Raised By</label>
            <select class="form-control" wire:model.debounce.300ms="raised_by_id">
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
            <label>Date Raised<span class="required" style="color: red">*</span></label>
            <input type="date" class="form-control" wire:model.debounce.300ms="date_raised" required>
            @error('date_raised') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Classification</label>
            <select class="form-control" wire:model.debounce.300ms="classification">
                <option value="major">Major</option>
                <option value="minor">Minor</option>
                <option value="observation">Observation</option>
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Status</label>
            <select class="form-control" wire:model.debounce.300ms="status">
                <option value="open">Open</option>
                <option value="investigation">Under Investigation</option>
                <option value="actions">Actions In Progress</option>
                <option value="closed">Closed</option>
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label>Description of Non-Conformity<span class="required" style="color: red">*</span></label>
            <textarea class="form-control" wire:model.debounce.300ms="description" rows="2" required></textarea>
            @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Immediate Action (Correction)</label>
            <textarea class="form-control" wire:model.debounce.300ms="immediate_action" rows="2"></textarea>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Root Cause</label>
            <textarea class="form-control" wire:model.debounce.300ms="root_cause" rows="2"></textarea>
        </div>
    </div>
</div>
