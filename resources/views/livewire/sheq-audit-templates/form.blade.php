<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Name<span class="required" style="color: red">*</span></label>
            <input type="text" class="form-control" wire:model.debounce.300ms="name" required>
            @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Standard(s)</label>
            <input type="text" class="form-control" wire:model.debounce.300ms="standard" placeholder="e.g. ISO 9001:2015, ISO 14001:2015, ISO 45001:2018">
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-8">
        <div class="form-group">
            <label>Description</label>
            <textarea class="form-control" wire:model.debounce.300ms="description" cols="30" rows="3"></textarea>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Active</label>
            <select class="form-control" wire:model.debounce.300ms="is_active">
                <option value="1">Yes</option>
                <option value="0">No</option>
            </select>
        </div>
    </div>
</div>
