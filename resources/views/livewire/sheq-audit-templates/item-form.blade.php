<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Reference Code</label>
            <input type="text" class="form-control" wire:model.debounce.300ms="item_code" placeholder="e.g. 4.1">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Possible Mark<span class="required" style="color: red">*</span></label>
            <input type="number" class="form-control" wire:model.debounce.300ms="possible_mark" min="0" required>
            @error('possible_mark') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Sort Order</label>
            <input type="number" class="form-control" wire:model.debounce.300ms="item_sort_order">
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label>Requirement<span class="required" style="color: red">*</span></label>
            <textarea class="form-control" wire:model.debounce.300ms="requirement" cols="30" rows="3" placeholder="What must be in place / audited?" required></textarea>
            @error('requirement') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label>Guidance / What to check</label>
            <textarea class="form-control" wire:model.debounce.300ms="guidance" cols="30" rows="3" placeholder="Evidence the auditor should look for"></textarea>
        </div>
    </div>
</div>
