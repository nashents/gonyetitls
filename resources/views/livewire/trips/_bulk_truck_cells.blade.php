{{-- One truck's cells for Bulk Schedule ($i, $row, $liquid); drivers/trailers are grouped by transporter. --}}
<td>
    <select wire:model.defer="rows.{{ $i }}.driver_id" class="form-control input-sm">
        <option value="">Select driver</option>
        @foreach ($drivers->get($row['transporter_id'], collect())->merge($drivers->collapse()->where('id', (int) $row['driver_id']))->unique('id') as $driver)
            <option value="{{ $driver->id }}">{{ $driver->employee->name }} {{ $driver->employee->surname }}</option>
        @endforeach
    </select>
    @error('rows.'.$i.'.driver_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
</td>
<td>
    {{-- Tick one or more trailers for this truck (e.g. a link / interlink pair). --}}
    <div style="max-height: 80px; overflow-y: auto; border: 1px solid #ddd; border-radius: 3px; padding: 2px 6px;">
        @forelse ($trailers->get($row['transporter_id'], collect())->merge($trailers->collapse()->whereIn('id', array_map('intval', (array) $row['trailer_ids'])))->unique('id') as $trailer)
            <label wire:key="bulk-trip-{{ $row['horse_id'] }}-trailer-{{ $trailer->id }}" style="display: block; font-weight: normal; margin: 0; white-space: nowrap;">
                <input type="checkbox" wire:model.defer="rows.{{ $i }}.trailer_ids" value="{{ $trailer->id }}"> {{ $trailer->identifier_label }}
            </label>
        @empty
            <small class="text-muted">No trailers</small>
        @endforelse
    </div>
    @error('rows.'.$i.'.trailer_ids') <span class="error" style="color:red">{{ $message }}</span> @enderror
</td>
<td>
    <input type="text" maxlength="255" wire:model.lazy="rows.{{ $i }}.trip_ref" class="form-control input-sm" placeholder="Optional">
    @error('rows.'.$i.'.trip_ref') <span class="error" style="color:red">{{ $message }}</span> @enderror
</td>
<td>
    <input type="text" maxlength="255" wire:model.defer="rows.{{ $i }}.container_number" class="form-control input-sm" placeholder="Separate by ,">
    @error('rows.'.$i.'.container_number') <span class="error" style="color:red">{{ $message }}</span> @enderror
</td>
<td>
    <input type="text" maxlength="255" wire:model.defer="rows.{{ $i }}.bill_of_lading" class="form-control input-sm" placeholder="BOL #">
    @error('rows.'.$i.'.bill_of_lading') <span class="error" style="color:red">{{ $message }}</span> @enderror
</td>
<td>
    <input type="date" wire:model.defer="rows.{{ $i }}.start_date" class="form-control input-sm">
    @error('rows.'.$i.'.start_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
</td>
@if ($liquid)
    <td>
        <input type="number" step="any" min="0" wire:model.lazy="rows.{{ $i }}.litreage" class="form-control input-sm">
        @error('rows.'.$i.'.litreage') <span class="error" style="color:red">{{ $message }}</span> @enderror
    </td>
@else
    <td>
        <input type="number" step="any" min="0" wire:model.lazy="rows.{{ $i }}.weight" class="form-control input-sm">
        @error('rows.'.$i.'.weight') <span class="error" style="color:red">{{ $message }}</span> @enderror
    </td>
    <td>
        <input type="number" step="any" min="0" wire:model.defer="rows.{{ $i }}.quantity" class="form-control input-sm">
        @error('rows.'.$i.'.quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
    </td>
@endif
<td class="text-right">{{ number_format($this->rowFreight($i), 2) }}</td>
<td>
    <label style="font-weight: normal; margin-bottom: 2px;"><input type="checkbox" wire:model="rows.{{ $i }}.transporter_agreement"> Add</label>
    @if ($row['transporter_agreement'])
        <input type="number" step="any" min="0" wire:model.lazy="rows.{{ $i }}.transporter_rate" class="form-control input-sm" placeholder="Rate" style="display: inline-block; width: 90px;">
        <small>= {{ number_format($this->rowTransporterFreight($i), 2) }}</small>
        @error('rows.'.$i.'.transporter_rate') <br><span class="error" style="color:red">{{ $message }}</span> @enderror
    @endif
</td>
<td>
    <input type="text" wire:model.defer="rows.{{ $i }}.comments" class="form-control input-sm">
</td>
