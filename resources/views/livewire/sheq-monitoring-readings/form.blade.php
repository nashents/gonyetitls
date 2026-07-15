<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Parameter<span class="required" style="color: red">*</span></label>
            <select class="form-control" wire:model.debounce.300ms="sheq_monitoring_parameter_id" required>
                <option value="">Select Option</option>
                @foreach ($parameters as $parameter)
                    <option value="{{$parameter->id}}">{{$parameter->name}} ({{$parameter->unit}})</option>
                @endforeach
            </select>
            @error('sheq_monitoring_parameter_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Department</label>
            <select class="form-control" wire:model.debounce.300ms="department_id">
                <option value="">Organisation-Wide</option>
                @foreach ($departments as $department)
                    <option value="{{$department->id}}">{{$department->name}}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Reading Date<span class="required" style="color: red">*</span></label>
            <input type="date" class="form-control" wire:model.debounce.300ms="reading_date" required>
            @error('reading_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Value<span class="required" style="color: red">*</span></label>
            <input type="number" step="any" class="form-control" wire:model.debounce.300ms="value" required>
            @error('value') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label>Comments</label>
            <textarea class="form-control" wire:model.debounce.300ms="comments" rows="2"></textarea>
        </div>
    </div>
</div>
