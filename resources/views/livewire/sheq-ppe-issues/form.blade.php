<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Employee<span class="required" style="color: red">*</span></label>
            <select class="form-control" wire:model.debounce.300ms="employee_id" required>
                <option value="">Select Option</option>
                @foreach ($employees as $employee)
                    <option value="{{$employee->id}}">{{$employee->name}} {{$employee->surname}}</option>
                @endforeach
            </select>
            @error('employee_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>PPE Type<span class="required" style="color: red">*</span></label>
            <input type="text" class="form-control" wire:model.debounce.300ms="ppe_type" placeholder="e.g. Safety Boots, Hard Hat, Overalls, Gloves, Respirator" required>
            @error('ppe_type') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>Size</label>
            <input type="text" class="form-control" wire:model.debounce.300ms="size">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Quantity</label>
            <input type="number" min="1" class="form-control" wire:model.debounce.300ms="quantity">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Issue Date<span class="required" style="color: red">*</span></label>
            <input type="date" class="form-control" wire:model.debounce.300ms="issue_date" required>
            @error('issue_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Next Replacement Date</label>
            <input type="date" class="form-control" wire:model.debounce.300ms="next_replacement_date">
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Employee Acknowledged Receipt?</label>
            <select class="form-control" wire:model.debounce.300ms="acknowledged">
                <option value="0">No</option>
                <option value="1">Yes</option>
            </select>
        </div>
    </div>
</div>
