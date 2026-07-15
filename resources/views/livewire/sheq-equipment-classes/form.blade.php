<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Class Name<span class="required" style="color: red">*</span></label>
            <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="e.g. Lifting Equipment, Fall Arrest, Machine Guards" required>
            @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Inspection Frequency (Days)</label>
            <input type="number" min="1" class="form-control" wire:model.debounce.300ms="inspection_frequency_days" placeholder="e.g. 90 for quarterly">
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Requires Quarterly Colour Coding?</label>
            <select class="form-control" wire:model.debounce.300ms="requires_color_code">
                <option value="0">No</option>
                <option value="1">Yes</option>
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Requires Load Testing / Certification?</label>
            <select class="form-control" wire:model.debounce.300ms="requires_load_test">
                <option value="0">No</option>
                <option value="1">Yes</option>
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label>Description / Inspection Requirements</label>
            <textarea class="form-control" wire:model.debounce.300ms="description" rows="2"></textarea>
        </div>
    </div>
</div>
