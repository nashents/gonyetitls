<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Parameter Name<span class="required" style="color: red">*</span></label>
            <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="e.g. Water Consumption, Effluent pH" required>
            @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Category<span class="required" style="color: red">*</span></label>
            <select class="form-control" wire:model.debounce.300ms="category" required>
                <option value="">Select Option</option>
                <option value="water">Water</option>
                <option value="energy">Energy</option>
                <option value="fuel">Fuel / Hydrocarbons</option>
                <option value="waste">Waste</option>
                <option value="emissions">Air Emissions</option>
                <option value="effluent">Effluent</option>
                <option value="consumables">Consumables (e.g. Paper)</option>
                <option value="other">Other</option>
            </select>
            @error('category') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>Unit</label>
            <input type="text" class="form-control" wire:model.debounce.300ms="unit" placeholder="e.g. kL, kWh, L, kg">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Limit / Target Value</label>
            <input type="text" class="form-control" wire:model.debounce.300ms="limit_value">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Limit Type</label>
            <select class="form-control" wire:model.debounce.300ms="limit_type">
                <option value="max">Maximum (breach if above)</option>
                <option value="min">Minimum (breach if below)</option>
            </select>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Monitoring Frequency</label>
            <select class="form-control" wire:model.debounce.300ms="frequency">
                <option value="">Select Option</option>
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
                <option value="quarterly">Quarterly</option>
                <option value="annually">Annually</option>
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Active</label>
            <select class="form-control" wire:model.debounce.300ms="is_active">
                <option value="1">Yes</option>
                <option value="0">No</option>
            </select>
        </div>
    </div>
</div>
