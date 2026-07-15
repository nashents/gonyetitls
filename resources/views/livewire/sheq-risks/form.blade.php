<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Category<span class="required" style="color: red">*</span></label>
            <select class="form-control" wire:model.debounce.300ms="category" required>
                <option value="">Select Option</option>
                <option value="safety">Safety</option>
                <option value="health">Health</option>
                <option value="environment">Environment</option>
                <option value="quality">Quality</option>
            </select>
            @error('category') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
            <label>Risk Assessment</label>
            <select class="form-control" wire:model.debounce.300ms="sheq_risk_assessment_id">
                <option value="">None (Standalone Register Entry)</option>
                @foreach ($assessments as $assessment)
                    <option value="{{$assessment->id}}">{{$assessment->assessment_number}} - {{$assessment->activity}}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Hazard / Aspect<span class="required" style="color: red">*</span></label>
            <textarea class="form-control" wire:model.debounce.300ms="hazard" rows="2" required></textarea>
            @error('hazard') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Risk / Impact<span class="required" style="color: red">*</span></label>
            <textarea class="form-control" wire:model.debounce.300ms="risk" rows="2" required></textarea>
            @error('risk') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>Likelihood (1-5)<span class="required" style="color: red">*</span></label>
            <select class="form-control" wire:model.debounce.300ms="likelihood" required>
                <option value="">-</option>
                <option value="1">1 - Rare</option>
                <option value="2">2 - Unlikely</option>
                <option value="3">3 - Possible</option>
                <option value="4">4 - Likely</option>
                <option value="5">5 - Almost Certain</option>
            </select>
            @error('likelihood') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Severity (1-5)<span class="required" style="color: red">*</span></label>
            <select class="form-control" wire:model.debounce.300ms="severity" required>
                <option value="">-</option>
                <option value="1">1 - Insignificant</option>
                <option value="2">2 - Minor</option>
                <option value="3">3 - Moderate</option>
                <option value="4">4 - Major</option>
                <option value="5">5 - Catastrophic</option>
            </select>
            @error('severity') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Residual Likelihood (1-5)</label>
            <select class="form-control" wire:model.debounce.300ms="residual_likelihood">
                <option value="">-</option>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
            </select>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Residual Severity (1-5)</label>
            <select class="form-control" wire:model.debounce.300ms="residual_severity">
                <option value="">-</option>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Top Risk?</label>
            <select class="form-control" wire:model.debounce.300ms="is_top_risk">
                <option value="0">No</option>
                <option value="1">Yes - Include in Top Risks</option>
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Status</label>
            <select class="form-control" wire:model.debounce.300ms="status">
                <option value="open">Open</option>
                <option value="controlled">Controlled</option>
                <option value="closed">Closed</option>
            </select>
        </div>
    </div>
</div>
