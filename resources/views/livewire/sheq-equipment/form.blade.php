<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Equipment Class<span class="required" style="color: red">*</span></label>
            <select class="form-control" wire:model.debounce.300ms="sheq_equipment_class_id" required>
                <option value="">Select Option</option>
                @foreach ($classes as $class)
                    <option value="{{$class->id}}">{{$class->name}}</option>
                @endforeach
            </select>
            @error('sheq_equipment_class_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Unique Equipment Number<span class="required" style="color: red">*</span></label>
            <input type="text" class="form-control" wire:model.debounce.300ms="equipment_number" required>
            @error('equipment_number') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Description</label>
            <input type="text" class="form-control" wire:model.debounce.300ms="description" placeholder="e.g. 2T Chain Block, Full Body Harness">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Location</label>
            <input type="text" class="form-control" wire:model.debounce.300ms="location">
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>SWL / Rating</label>
            <input type="text" class="form-control" wire:model.debounce.300ms="swl" placeholder="e.g. 2 Tonnes">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Certificate / Load Test Expiry</label>
            <input type="date" class="form-control" wire:model.debounce.300ms="certificate_expiry">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Current Colour Code</label>
            <select class="form-control" wire:model.debounce.300ms="current_color_code">
                <option value="">None</option>
                <option value="red">Red</option>
                <option value="green">Green</option>
                <option value="blue">Blue</option>
                <option value="yellow">Yellow</option>
                <option value="white">White</option>
                <option value="orange">Orange</option>
            </select>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Status</label>
            <select class="form-control" wire:model.debounce.300ms="status">
                <option value="in_service">In Service</option>
                <option value="quarantined">Quarantined</option>
                <option value="decommissioned">Decommissioned</option>
            </select>
        </div>
    </div>
</div>
