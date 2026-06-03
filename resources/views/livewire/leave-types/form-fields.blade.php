<div class="modal-body">
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label>Name <span class="required" style="color: red">*</span></label>
                <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="e.g. Annual">
                @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label>Code</label>
                <input type="text" class="form-control" wire:model.debounce.300ms="code" placeholder="e.g. AL">
                @error('code') <span class="error" style="color:red">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label>Entitlement / Year</label>
                <input type="number" min="0" step="0.01" class="form-control" wire:model.debounce.300ms="entitlement" placeholder="e.g. 30">
                @error('entitlement') <span class="error" style="color:red">{{ $message }}</span> @enderror
            </div>
        </div>
    </div>

    <hr>

    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label>Monthly Accrual Rate</label>
                <input type="number" min="0" step="0.01" class="form-control" wire:model.debounce.300ms="monthly_accrual_rate" placeholder="e.g. 2.5">
                @error('monthly_accrual_rate') <span class="error" style="color:red">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label>Max Carry Forward Days</label>
                <input type="number" min="0" step="0.01" class="form-control" wire:model.debounce.300ms="max_carry_forward_days" placeholder="e.g. 30">
                @error('max_carry_forward_days') <span class="error" style="color:red">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label>Max Consecutive Days</label>
                <input type="number" min="0" step="0.01" class="form-control" wire:model.debounce.300ms="max_consecutive_days" placeholder="Optional">
                @error('max_consecutive_days') <span class="error" style="color:red">{{ $message }}</span> @enderror
            </div>
        </div>
    </div>

    <hr>

    <div class="row">
        <div class="col-md-3">
            <label>
                <input type="checkbox" wire:model="is_paid">
                Paid Leave
            </label>
        </div>

        <div class="col-md-3">
            <label>
                <input type="checkbox" wire:model="is_accruable">
                Accruable
            </label>
        </div>

        <div class="col-md-3">
            <label>
                <input type="checkbox" wire:model="carry_forward_allowed">
                Carry Forward
            </label>
        </div>

        <div class="col-md-3">
            <label>
                <input type="checkbox" wire:model="active">
                Active
            </label>
        </div>
    </div>

    <br>

    <div class="row">
        <div class="col-md-3">
            <label>
                <input type="checkbox" wire:model="requires_medical_report">
                Requires Medical Report
            </label>
        </div>

        <div class="col-md-3">
            <label>
                <input type="checkbox" wire:model="requires_attachment">
                Requires Attachment
            </label>
        </div>

        <div class="col-md-3">
            <label>
                <input type="checkbox" wire:model="requires_hod_approval">
                HOD Approval
            </label>
        </div>

        <div class="col-md-3">
            <label>
                <input type="checkbox" wire:model="requires_management_approval">
                Management Approval
            </label>
        </div>
    </div>

    <hr>

    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label>Description</label>
                <textarea class="form-control" rows="3" wire:model.debounce.300ms="description" placeholder="Enter description"></textarea>
                @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
            </div>
        </div>
    </div>
</div>