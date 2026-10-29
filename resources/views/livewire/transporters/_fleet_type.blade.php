{{-- Managed vs once-off; the own fleet is the Default Transporter checkbox above. --}}
@unless ($default)
    <div class="form-group">
        <label for="fleet_type">Fleet Type</label>
        <select wire:model="fleet_type" class="form-control">
            <option value="{{ \App\Models\Transporter::FLEET_ONCE_OFF }}">Once-off (trips only)</option>
            <option value="{{ \App\Models\Transporter::FLEET_MANAGED }}">Managed (run like own fleet)</option>
        </select>
        <small class="d-block text-muted">
            Managed transporters' assets appear in bookings, inspections, workshop and stores, and count toward the fleet plan.
            Once-off transporters' assets are used on trips (and fuel) only.
        </small>
        @error('fleet_type') <span class="text-danger error">{{ $message }}</span>@enderror
    </div>
@endunless
