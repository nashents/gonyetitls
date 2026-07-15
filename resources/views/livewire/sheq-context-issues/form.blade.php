<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>Type<span class="required" style="color: red">*</span></label>
            <select class="form-control" wire:model.debounce.300ms="type" required>
                <option value="internal">Internal</option>
                <option value="external">External</option>
            </select>
            @error('type') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Framework</label>
            <select class="form-control" wire:model="framework">
                <option value="swot">SWOT</option>
                <option value="pestel">PESTEL</option>
            </select>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Category<span class="required" style="color: red">*</span></label>
            <select class="form-control" wire:model.debounce.300ms="category" required>
                <option value="">Select Option</option>
                @if ($framework == 'pestel')
                    <option value="political">Political</option>
                    <option value="economic">Economic</option>
                    <option value="social">Social</option>
                    <option value="technological">Technological</option>
                    <option value="environmental">Environmental (incl. climate)</option>
                    <option value="legal">Legal</option>
                @else
                    <option value="strength">Strength</option>
                    <option value="weakness">Weakness</option>
                    <option value="opportunity">Opportunity</option>
                    <option value="threat">Threat</option>
                @endif
            </select>
            @error('category') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Issue Description<span class="required" style="color: red">*</span></label>
            <textarea class="form-control" wire:model.debounce.300ms="description" rows="3" required></textarea>
            @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Impact on Management System Outcomes</label>
            <textarea class="form-control" wire:model.debounce.300ms="impact" rows="3"></textarea>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Next Review Date</label>
            <input type="date" class="form-control" wire:model.debounce.300ms="review_date">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Status</label>
            <select class="form-control" wire:model.debounce.300ms="status">
                <option value="open">Open</option>
                <option value="monitored">Monitored</option>
                <option value="closed">Closed</option>
            </select>
        </div>
    </div>
</div>
