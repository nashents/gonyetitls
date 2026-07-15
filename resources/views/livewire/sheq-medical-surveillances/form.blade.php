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
            <label>Exam Type<span class="required" style="color: red">*</span></label>
            <select class="form-control" wire:model.debounce.300ms="exam_type" required>
                <option value="">Select Option</option>
                <option value="pre_employment">Pre-Employment</option>
                <option value="periodic">Periodic</option>
                <option value="transfer">Transfer / Re-assignment</option>
                <option value="exit">Exit</option>
            </select>
            @error('exam_type') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Exam Date<span class="required" style="color: red">*</span></label>
            <input type="date" class="form-control" wire:model.debounce.300ms="exam_date" required>
            @error('exam_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Next Due Date</label>
            <input type="date" class="form-control" wire:model.debounce.300ms="next_due_date">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Provider / Clinic</label>
            <input type="text" class="form-control" wire:model.debounce.300ms="provider">
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Outcome</label>
            <select class="form-control" wire:model.debounce.300ms="outcome">
                <option value="">Select Option</option>
                <option value="fit">Fit</option>
                <option value="fit_with_restrictions">Fit With Restrictions</option>
                <option value="unfit">Unfit</option>
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Restrictions (if any)</label>
            <textarea class="form-control" wire:model.debounce.300ms="restrictions" rows="2"></textarea>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Remarks</label>
            <textarea class="form-control" wire:model.debounce.300ms="remarks" rows="2"></textarea>
        </div>
    </div>
</div>
