<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Meeting Type<span class="required" style="color: red">*</span></label>
            <select class="form-control" wire:model.debounce.300ms="type" required>
                <option value="">Select Option</option>
                <option value="sheq_committee">SHEQ Committee</option>
                <option value="toolbox_talk">Toolbox Talk</option>
                <option value="stand_down">SHEQ Stand-Down</option>
                <option value="departmental_review">Departmental SHEQ Review</option>
                <option value="other">Other</option>
            </select>
            @error('type') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
            <label>Chairperson<span class="required" style="color: red">*</span></label>
            <select class="form-control" wire:model.debounce.300ms="chairperson_id" required>
                <option value="">Select Option</option>
                @foreach ($employees as $employee)
                    <option value="{{$employee->id}}">{{$employee->name}} {{$employee->surname}}</option>
                @endforeach
            </select>
            @error('chairperson_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Meeting Date<span class="required" style="color: red">*</span></label>
            <input type="date" class="form-control" wire:model.debounce.300ms="meeting_date" required>
            @error('meeting_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Number of Attendees</label>
            <input type="number" min="0" class="form-control" wire:model.debounce.300ms="attendees_count">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Status</label>
            <select class="form-control" wire:model.debounce.300ms="status">
                <option value="scheduled">Scheduled</option>
                <option value="held">Held</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Agenda</label>
            <textarea class="form-control" wire:model.debounce.300ms="agenda" rows="3"></textarea>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Minutes / Key Outcomes</label>
            <textarea class="form-control" wire:model.debounce.300ms="minutes" rows="3"></textarea>
        </div>
    </div>
</div>
