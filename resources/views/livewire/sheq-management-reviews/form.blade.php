<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>Level</label>
            <select class="form-control" wire:model.debounce.300ms="department_id">
                <option value="">Organisation-Wide</option>
                @foreach ($departments as $department)
                    <option value="{{$department->id}}">{{$department->name}}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Chairperson<span class="required" style="color: red">*</span></label>
            <select class="form-control" wire:model.debounce.300ms="chairperson_id" required>
                <option value="">Select Option</option>
                @foreach ($employees as $employee)
                    <option value="{{$employee->id}}">{{$employee->name}} {{$employee->surname}}</option>
                @endforeach
            </select>
            @error('chairperson_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Review Date<span class="required" style="color: red">*</span></label>
            <input type="date" class="form-control" wire:model.debounce.300ms="review_date" required>
            @error('review_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Status</label>
            <select class="form-control" wire:model.debounce.300ms="status">
                <option value="scheduled">Scheduled</option>
                <option value="held">Held</option>
                <option value="closed">Closed</option>
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label>Attendees</label>
            <textarea class="form-control" wire:model.debounce.300ms="attendees" rows="1"></textarea>
        </div>
    </div>
</div>
<h5><strong>Review Inputs</strong></h5>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Audit Results & Findings Closure</label>
            <textarea class="form-control" wire:model.debounce.300ms="audit_results" rows="2"></textarea>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Customer Feedback & Complaints</label>
            <textarea class="form-control" wire:model.debounce.300ms="customer_feedback" rows="2"></textarea>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>SHEQ / Process Performance</label>
            <textarea class="form-control" wire:model.debounce.300ms="process_performance" rows="2"></textarea>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Incident & Non-Conformity Statistics</label>
            <textarea class="form-control" wire:model.debounce.300ms="incident_nc_status" rows="2"></textarea>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Status of Actions from Previous Reviews</label>
            <textarea class="form-control" wire:model.debounce.300ms="action_status" rows="2"></textarea>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Objectives & Programmes Progress</label>
            <textarea class="form-control" wire:model.debounce.300ms="objective_progress" rows="2"></textarea>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Compliance Obligation Evaluation Results</label>
            <textarea class="form-control" wire:model.debounce.300ms="compliance_status" rows="2"></textarea>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Risks & Opportunities</label>
            <textarea class="form-control" wire:model.debounce.300ms="risks_opportunities" rows="2"></textarea>
        </div>
    </div>
</div>
<h5><strong>Review Outputs</strong></h5>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Resource Adequacy</label>
            <textarea class="form-control" wire:model.debounce.300ms="resource_adequacy" rows="2"></textarea>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Opportunities for Improvement</label>
            <textarea class="form-control" wire:model.debounce.300ms="improvement_opportunities" rows="2"></textarea>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label>Decisions & Conclusions</label>
            <textarea class="form-control" wire:model.debounce.300ms="decisions" rows="2"></textarea>
        </div>
    </div>
</div>
