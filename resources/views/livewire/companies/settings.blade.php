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
        <div class="mb-10 form-group">
            <label for="default_booking_transaction_type">Default Booking Transaction Type</label>
            <select wire:model.debounce.300ms="default_booking_transaction_type" id="default_booking_transaction_type" class="form-control">
                <option value="expense">Expense/Internal Job</option>
                <option value="income">Income/External Job</option>
            </select>
            <small class="form-text text-muted">The transaction type pre-selected when a new booking is created.</small>
            @error('default_booking_transaction_type') <span class="text-danger error">{{ $message }}</span>@enderror
        </div>
        <div class="btn-group" role="group" style="float: right;">
            <button type="submit" class="btn btn-success btn-wide btn-rounded" ><i class="fa fa-refresh"></i>Update</button>
        </div>
        <br>
        <hr> 
    </form>
</div>
