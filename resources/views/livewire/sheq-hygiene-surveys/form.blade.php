<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Stressor<span class="required" style="color: red">*</span></label>
            <select class="form-control" wire:model.debounce.300ms="stressor" required>
                <option value="">Select Option</option>
                <option value="noise">Noise</option>
                <option value="dust">Dust</option>
                <option value="illumination">Illumination</option>
                <option value="heat">Heat</option>
                <option value="vibration">Vibration</option>
                <option value="ergonomics">Ergonomics</option>
                <option value="other">Other</option>
            </select>
            @error('stressor') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
            <label>Area Surveyed</label>
            <input type="text" class="form-control" wire:model.debounce.300ms="area">
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Survey Date<span class="required" style="color: red">*</span></label>
            <input type="date" class="form-control" wire:model.debounce.300ms="survey_date" required>
            @error('survey_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Surveyor / Service Provider</label>
            <input type="text" class="form-control" wire:model.debounce.300ms="surveyor">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Next Survey Date</label>
            <input type="date" class="form-control" wire:model.debounce.300ms="next_survey_date">
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>Measured Result</label>
            <input type="text" class="form-control" wire:model.debounce.300ms="result" placeholder="e.g. 88 dBA">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Limit / Standard</label>
            <input type="text" class="form-control" wire:model.debounce.300ms="limit_standard" placeholder="e.g. 85 dBA OEL">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Exceeds Limit?</label>
            <select class="form-control" wire:model.debounce.300ms="exceeds_limit">
                <option value="0">No</option>
                <option value="1">Yes</option>
            </select>
        </div>
    </div>
    <div class="col-md-3">
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
            <label>Findings / Recommendations</label>
            <textarea class="form-control" wire:model.debounce.300ms="findings" rows="2"></textarea>
        </div>
    </div>
</div>
