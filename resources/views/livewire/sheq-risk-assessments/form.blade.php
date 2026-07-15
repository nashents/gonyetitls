<div class="row">
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
    <div class="col-md-6">
        <div class="form-group">
            <label>Assessment Type</label>
            <select class="form-control" wire:model.debounce.300ms="type">
                <option value="baseline">Baseline</option>
                <option value="issue_based">Issue-Based</option>
                <option value="continuous">Continuous (Pre-Task)</option>
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Activity / Process<span class="required" style="color: red">*</span></label>
            <input type="text" class="form-control" wire:model.debounce.300ms="activity" required>
            @error('activity') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Area / Location</label>
            <input type="text" class="form-control" wire:model.debounce.300ms="area">
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Assessment Date<span class="required" style="color: red">*</span></label>
            <input type="date" class="form-control" wire:model.debounce.300ms="assessment_date" required>
            @error('assessment_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Next Review Date</label>
            <input type="date" class="form-control" wire:model.debounce.300ms="review_date">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Status</label>
            <select class="form-control" wire:model.debounce.300ms="status">
                <option value="active">Active</option>
                <option value="under_review">Under Review</option>
                <option value="archived">Archived</option>
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label>Assessment Team</label>
            <textarea class="form-control" wire:model.debounce.300ms="team" cols="30" rows="2" placeholder="Names of team members / worker representatives involved"></textarea>
        </div>
    </div>
</div>
