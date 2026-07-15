<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>Code</label>
            <input type="text" class="form-control" wire:model.debounce.300ms="section_code" placeholder="e.g. 4.0">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Title<span class="required" style="color: red">*</span></label>
            <input type="text" class="form-control" wire:model.debounce.300ms="section_title" placeholder="e.g. Organisational Context" required>
            @error('section_title') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Sort Order</label>
            <input type="number" class="form-control" wire:model.debounce.300ms="section_sort_order">
        </div>
    </div>
</div>
