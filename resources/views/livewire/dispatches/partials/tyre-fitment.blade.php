{{-- Optional fitting position for one tyre row on a tyre dispatch. Expects $row (the row key). --}}
@if (!empty($needsSerial[$row]))
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label>Serial#<span class="required" style="color: red">*</span> <small class="text-muted">(this tyre has none recorded)</small></label>
                <input type="text" class="form-control" wire:model.defer="tyre_serial.{{ $row }}" placeholder="Enter tyre serial number" required>
                @error('tyre_serial.'.$row) <span class="error" style="color:red">{{ $message }}</span> @enderror
            </div>
        </div>
    </div>
@endif
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Fit To <small class="text-muted">(optional; on a ticket dispatch the tyre is assigned to the ticket's unit anyway)</small></label>
            <select wire:model="fit_type.{{ $row }}" class="form-control">
                <option value="">Select Unit</option>
                @foreach ($fitUnitOptions as $type => $label)
                    <option value="{{ $type }}">{{ $label }}</option>
                @endforeach
            </select>
            @if (empty($fitUnitOptions))
                <small style="color: #a94442">Select the horse, trailer or vehicle under Destination Details first.</small>
            @endif
            @error('fit_type.'.$row) <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Axle</label>
            <select wire:model="fit_axle.{{ $row }}" class="form-control">
                <option value="">Select Axle</option>
                @foreach (\App\Models\TyreAssignment::AXLES as $axle)
                    <option value="{{ $axle }}">{{ $axle }}</option>
                @endforeach
            </select>
            @error('fit_axle.'.$row) <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Position</label>
            <select wire:model="fit_position.{{ $row }}" class="form-control">
                <option value="">Select Position</option>
                @foreach (\App\Models\TyreAssignment::POSITIONS as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            @error('fit_position.'.$row) <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    @if (!empty($fitWarnings[$row]))
        <div class="col-md-12">
            <small style="color: #8a6d3b"><i class="fa fa-exclamation-triangle"></i> {{ $fitWarnings[$row] }}</small>
        </div>
    @endif
</div>
