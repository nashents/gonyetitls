<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Audit Checklist<span class="required" style="color: red">*</span></label>
            <select class="form-control" wire:model.debounce.300ms="sheq_audit_template_id" required>
                <option value="">Select Option</option>
                @foreach ($templates as $template)
                    <option value="{{$template->id}}">{{$template->name}}</option>
                @endforeach
            </select>
            @error('sheq_audit_template_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
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
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Lead Auditor<span class="required" style="color: red">*</span></label>
            <select class="form-control" wire:model.debounce.300ms="lead_auditor_id" required>
                <option value="">Select Option</option>
                @foreach ($employees as $employee)
                    <option value="{{$employee->id}}">{{$employee->name}} {{$employee->surname}}</option>
                @endforeach
            </select>
            @error('lead_auditor_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Auditee / Departmental Representative</label>
            <select class="form-control" wire:model.debounce.300ms="auditee_id">
                <option value="">Select Option</option>
                @foreach ($employees as $employee)
                    <option value="{{$employee->id}}">{{$employee->name}} {{$employee->surname}}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Audit Type</label>
            <select class="form-control" wire:model.debounce.300ms="audit_type">
                <option value="internal">Internal</option>
                <option value="external">External</option>
                <option value="supplier">Supplier / Contractor</option>
                <option value="cross_functional">Cross-Functional</option>
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Scheduled Date<span class="required" style="color: red">*</span></label>
            <input type="date" class="form-control" wire:model.debounce.300ms="scheduled_date" required>
            @error('scheduled_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
</div>
