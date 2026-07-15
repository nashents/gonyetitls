<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Contractor Type<span class="required" style="color: red">*</span></label>
            <select class="form-control" wire:model="contractor_type" required>
                <option value="vendor">Vendor / Supplier</option>
                <option value="transporter">Transporter / Subcontractor</option>
            </select>
            @error('contractor_type') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Contractor<span class="required" style="color: red">*</span></label>
            <select class="form-control" wire:model.debounce.300ms="contractor_id" required>
                <option value="">Select Option</option>
                @if ($contractor_type == 'transporter')
                    @foreach ($transporters as $transporter)
                        <option value="{{$transporter->id}}">{{$transporter->name}}</option>
                    @endforeach
                @else
                    @foreach ($vendors as $vendor)
                        <option value="{{$vendor->id}}">{{$vendor->name}}</option>
                    @endforeach
                @endif
            </select>
            @error('contractor_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>Induction Date</label>
            <input type="date" class="form-control" wire:model.debounce.300ms="induction_date">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Induction Expiry</label>
            <input type="date" class="form-control" wire:model.debounce.300ms="induction_expiry">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Pre-Engagement Screening</label>
            <select class="form-control" wire:model.debounce.300ms="screening_status">
                <option value="pending">Pending</option>
                <option value="passed">Passed</option>
                <option value="failed">Failed</option>
            </select>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>SHEQ File Status</label>
            <select class="form-control" wire:model.debounce.300ms="sheq_file_status">
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="conditional">Conditionally Approved</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>SHEQ Score / Rating</label>
            <input type="text" class="form-control" wire:model.debounce.300ms="sheq_score" placeholder="e.g. 85%">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Last SHEQ Audit</label>
            <input type="date" class="form-control" wire:model.debounce.300ms="last_audit_date">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Next SHEQ Audit</label>
            <input type="date" class="form-control" wire:model.debounce.300ms="next_audit_date">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Status</label>
            <select class="form-control" wire:model.debounce.300ms="status">
                <option value="active">Active</option>
                <option value="suspended">Suspended</option>
                <option value="terminated">Terminated</option>
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label>Notes (inductions, training, non-conforming equipment, etc.)</label>
            <textarea class="form-control" wire:model.debounce.300ms="notes" rows="2"></textarea>
        </div>
    </div>
</div>
