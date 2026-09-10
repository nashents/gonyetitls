<div>
    <form wire:submit.prevent="update()">
        <div class="mb-10" style="margin-top:30px;">
            <input type="checkbox" wire:model.debounce.300ms="enable_requisition_two_step_authorization"   class="line-style" />
            <label for="one" class="radio-label">Enable Requisition Two Step Authorization</label>
            @error('enable_requisition_two_step_authorization') <span class="text-danger error">{{ $message }}</span>@enderror
        </div>
        <div class="mb-10 form-group">
            <label for="fleet_identifier_preference">Fleet Identifier</label>
            <select wire:model.debounce.300ms="fleet_identifier_preference" id="fleet_identifier_preference" class="form-control">
                <option value="registration_number">Vehicle Registration Number (VRN)</option>
                <option value="fleet_number">Fleet Number</option>
            </select>
            <small class="form-text text-muted">Controls how horses, trailers and vehicles are labelled and sorted throughout the system.</small>
            @error('fleet_identifier_preference') <span class="text-danger error">{{ $message }}</span>@enderror
        </div>
        <div class="btn-group" role="group" style="float: right;">
            <button type="submit" class="btn btn-success btn-wide btn-rounded" ><i class="fa fa-refresh"></i>Update</button>
        </div>
        <br>
        <hr> 
    </form>
</div>
