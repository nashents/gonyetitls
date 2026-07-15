<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Scenario<span class="required" style="color: red">*</span></label>
            <input type="text" class="form-control" wire:model.debounce.300ms="scenario" placeholder="e.g. Fire, Chemical Spill, Flooding, Vehicle Accident" required>
            @error('scenario') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
            <label>Location / Area</label>
            <input type="text" class="form-control" wire:model.debounce.300ms="location">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Linked Risk (from Risk Register)</label>
            <select class="form-control" wire:model.debounce.300ms="sheq_risk_id">
                <option value="">None</option>
                @foreach ($risks as $risk)
                    <option value="{{$risk->id}}">{{ \Illuminate\Support\Str::limit($risk->hazard, 60) }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Drill Frequency</label>
            <select class="form-control" wire:model.debounce.300ms="drill_frequency">
                <option value="">Select Option</option>
                <option value="monthly">Monthly</option>
                <option value="quarterly">Quarterly</option>
                <option value="bi_annually">Bi-Annually</option>
                <option value="annually">Annually</option>
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Status</label>
            <select class="form-control" wire:model.debounce.300ms="status">
                <option value="active">Active</option>
                <option value="archived">Archived</option>
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label>Response Plan Summary</label>
            <textarea class="form-control" wire:model.debounce.300ms="response_plan" rows="3"></textarea>
        </div>
    </div>
</div>
