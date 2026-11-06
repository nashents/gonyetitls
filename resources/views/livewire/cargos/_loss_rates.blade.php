{{-- Cargo loss terms (shared by create + edit modals). Blank = Business Settings default. --}}
@php $ownFleetName = app(\App\Services\LossRateService::class)->ownFleetName(); @endphp
<div class="row">
    <div class="col-md-12">
        <div class="form-group" style="margin-bottom:5px;">
            <input type="checkbox" wire:model.defer="status" id="cargo_status_{{ $cargo_id ?? 'new' }}" class="line-style">
            <label class="radio-label" for="cargo_status_{{ $cargo_id ?? 'new' }}">Active</label>
            <small class="text-muted">&nbsp;Inactive cargos can no longer be picked on trips, orders, quotes etc. or in Loss Rates. Existing records keep theirs.</small>
        </div>
    </div>
    <div class="col-md-12">
        <h6 style="margin-top:5px;"><strong>Loss Allowance &amp; Chargeable Rates</strong>
            <small class="text-muted">Blank = Business Settings default.</small></h6>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Default Allowable Loss %</label>
            <input type="number" step="any" min="0" max="100" class="form-control" wire:model.defer="allowable_loss_percentage" placeholder="e.g. 0.3 = 0.003">
            <small class="text-muted">Customers' own % (all cargos or this cargo) wins.</small>
            @error('allowable_loss_percentage') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Rate &ndash; Own Fleet ({{ $ownFleetName }})</label>
            <input type="number" step="any" min="0" class="form-control" wire:model.defer="own_fleet_chargeable_loss_rate" placeholder="per ton / litre">
            @error('own_fleet_chargeable_loss_rate') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Rate &ndash; Subcontractors</label>
            <input type="number" step="any" min="0" class="form-control" wire:model.defer="subcontractor_chargeable_loss_rate" placeholder="per ton / litre">
            @error('subcontractor_chargeable_loss_rate') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
</div>
