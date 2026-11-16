{{-- Trips > Edit, Fuel Order Details > Attach Existing Fuel Order(s) - see AttachesTripFuelOrders. --}}
@php $attachableFuels = $this->attachableFuels; @endphp
<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>Categories<span class="required" style="color: red">*</span></label>
            <select class="form-control" wire:model.debounce.300ms="fuel_category">
                <option value="">Select Category</option>
                <option value="Customer">Customer</option>
                <option value="Self">Self</option>
                <option value="Transporter">Transporter</option>
            </select>
            @error('fuel_category') <span class="text-danger error">{{ $message }}</span>@enderror
        </div>
    </div>
    <div class="col-md-9">
        <small class="text-muted d-block" style="padding-top: 30px">
            Fuel orders already on this trip are ticked - change the litres, or untick an order to take it off the trip. Tick any other order{{ ($selectedHorse || $selectedVehicle) ? ' for the selected ' . strtolower($mode_of_transport ?? 'unit') : '' }} with unallocated litres and enter the litres going to this trip (defaults to all that is available) - the rest stays available for other trips and posts to Fuel - Ops until allocated.
            The category applies to orders not yet on a trip; an order already on a trip keeps its own category.
        </small>
    </div>
</div>
@error('attach_fuel_ids') <span class="text-danger error d-block">{{ $message }}</span>@enderror
@foreach ($errors->getMessages() as $errorKey => $errorMessages)
    @if (\Illuminate\Support\Str::startsWith($errorKey, 'attach_fuel_quantities.'))
        <span class="text-danger error d-block">{{ $errorMessages[0] }}</span>
    @endif
@endforeach
<div class="table-responsive">
    <table class="table table-striped table-bordered table-sm">
        <thead>
            <tr>
                <th></th>
                <th>Order #</th>
                <th>Reference</th>
                <th>Date</th>
                <th>Horse/Vehicle</th>
                <th>Station</th>
                <th>Qty (L)</th>
                <th>On Other Trips (L)</th>
                <th>Available (L)</th>
                <th style="min-width: 110px">To This Trip (L)</th>
                <th>Amount</th>
                <th>This Trip's Cost</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @php $attachTotals = ['litres' => 0, 'cost' => []]; @endphp
            @forelse ($attachableFuels as $attachableFuel)
                @php
                    $attachOnTrip = $attachableFuel->this_trip_litres !== null;
                    $attachLocked = $attachableFuel->attach_locked;
                    $attachTicked = in_array((string) $attachableFuel->id, array_map('strval', (array) $attach_fuel_ids), true);
                    $attachQty = $attachLocked ? $attachableFuel->this_trip_litres : ($attach_fuel_quantities[$attachableFuel->id] ?? null);
                    $attachOtherTrips = round((float) $attachableFuel->allocated_litres - (float) $attachableFuel->this_trip_litres, 2);
                    $attachOrderAmount = is_numeric($attachableFuel->amount) && (float) $attachableFuel->amount > 0
                        ? (float) $attachableFuel->amount
                        : (float) $attachableFuel->quantity * (float) $attachableFuel->unit_price;
                    $attachCost = ($attachTicked || $attachLocked) && is_numeric($attachQty) && (float) $attachableFuel->quantity > 0
                        ? $attachOrderAmount * (float) $attachQty / (float) $attachableFuel->quantity
                        : null;
                    if ($attachCost !== null) {
                        $attachCurrency = optional($attachableFuel->currency)->name ?? '';
                        $attachTotals['litres'] += (float) $attachQty;
                        $attachTotals['cost'][$attachCurrency] = ($attachTotals['cost'][$attachCurrency] ?? 0) + $attachCost;
                    }
                @endphp
                <tr wire:key="attach-fuel-{{ $attachableFuel->id }}">
                    <td>
                        @if ($attachLocked)
                            <input type="checkbox" checked disabled />
                        @else
                            <input type="checkbox" wire:model="attach_fuel_ids" value="{{ $attachableFuel->id }}" />
                        @endif
                    </td>
                    <td><a href="{{ route('fuels.edit', $attachableFuel->id) }}" target="_blank">{{ $attachableFuel->order_number }}</a></td>
                    <td>{{ $attachableFuel->reference }}</td>
                    <td>{{ $attachableFuel->date }}</td>
                    <td>{{ optional($attachableFuel->horse)->registration_number ?? optional($attachableFuel->vehicle)->registration_number }}</td>
                    <td>{{ optional($attachableFuel->container)->name ?? (optional($attachableFuel->source_horse)->registration_number ? 'Truck ' . $attachableFuel->source_horse->registration_number : '') }}</td>
                    <td>{{ $attachableFuel->quantity }}</td>
                    <td>{{ $attachOtherTrips > 0 ? $attachOtherTrips : '-' }}</td>
                    <td>{{ $attachableFuel->available_litres }}</td>
                    <td>
                        @if ($attachLocked)
                            {{ round((float) $attachQty, 2) }}
                        @else
                            <input type="number" step="any" min="0" max="{{ $attachableFuel->available_litres }}" class="form-control form-control-sm"
                                wire:model.lazy="attach_fuel_quantities.{{ $attachableFuel->id }}" @if (!$attachTicked) disabled @endif>
                        @endif
                    </td>
                    <td>{{ optional($attachableFuel->currency)->name }} {{ is_numeric($attachableFuel->amount) ? number_format($attachableFuel->amount, 2) : '' }}</td>
                    <td>{{ $attachCost !== null ? optional($attachableFuel->currency)->name . ' ' . number_format($attachCost, 2) : '' }}</td>
                    <td>
                        {{ ucfirst($attachableFuel->authorization ?? 'pending') }}
                        @if ($attachOnTrip)
                            <span class="badge badge-success">On this trip</span>
                        @endif
                        @if ($attachableFuel->supplied_by_customer)
                            <span class="badge badge-info">Customer funded</span>
                        @endif
                        @if ($attachOtherTrips > 0)
                            <span class="badge badge-warning">Split</span>
                        @endif
                        @if ($attachLocked)
                            <small class="text-muted d-block">Change from Fuel Orders</small>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="13" class="text-center">No fuel orders with unallocated litres found{{ ($selectedHorse || $selectedVehicle) ? ' for this ' . strtolower($mode_of_transport ?? 'unit') : '' }}.</td>
                </tr>
            @endforelse
        </tbody>
        @if ($attachTotals['litres'] > 0)
            <tfoot>
                <tr>
                    <th colspan="9" class="text-right">Total to this trip</th>
                    <th>{{ round($attachTotals['litres'], 2) }}</th>
                    <th></th>
                    <th>
                        @foreach ($attachTotals['cost'] as $attachCurrency => $attachCost)
                            <span class="d-block">{{ $attachCurrency }} {{ number_format($attachCost, 2) }}</span>
                        @endforeach
                    </th>
                    <th></th>
                </tr>
            </tfoot>
        @endif
    </table>
</div>
