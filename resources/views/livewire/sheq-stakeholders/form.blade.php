<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Name<span class="required" style="color: red">*</span></label>
            <input type="text" class="form-control" wire:model.debounce.300ms="name" required>
            @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Type<span class="required" style="color: red">*</span></label>
            <select class="form-control" wire:model.debounce.300ms="type" required>
                <option value="internal">Internal</option>
                <option value="external">External</option>
            </select>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Category</label>
            <select class="form-control" wire:model.debounce.300ms="category">
                <option value="">Select Option</option>
                <option value="worker">Workers</option>
                <option value="customer">Customer</option>
                <option value="supplier">Supplier / Contractor</option>
                <option value="regulator">Regulator / Authority</option>
                <option value="community">Community</option>
                <option value="shareholder">Shareholder / Owner</option>
                <option value="insurer">Insurer</option>
                <option value="other">Other</option>
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label>Needs & Expectations<span class="required" style="color: red">*</span></label>
            <textarea class="form-control" wire:model.debounce.300ms="needs_expectations" rows="3" required></textarea>
            @error('needs_expectations') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>Becomes Compliance Obligation?</label>
            <select class="form-control" wire:model.debounce.300ms="becomes_obligation">
                <option value="0">No</option>
                <option value="1">Yes</option>
            </select>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Department</label>
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
            <label>Engagement Method</label>
            <input type="text" class="form-control" wire:model.debounce.300ms="engagement_method" placeholder="e.g. Meetings, Surveys">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Engagement Frequency</label>
            <select class="form-control" wire:model.debounce.300ms="engagement_frequency">
                <option value="">Select Option</option>
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
                <option value="quarterly">Quarterly</option>
                <option value="annually">Annually</option>
                <option value="as_needed">As Needed</option>
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Status</label>
            <select class="form-control" wire:model.debounce.300ms="status">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
    </div>
</div>
