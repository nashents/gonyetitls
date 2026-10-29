{{-- Trip allocations for a fuel order (HandlesFuelTripAllocations). --}}
@if (count($trip_allocations))
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label>Trip Allocations <small class="text-muted">(litres of this order going to each trip)</small></label>
            <table class="table table-bordered table-condensed" style="margin-bottom: 5px;">
                <thead>
                    <tr>
                        <th>Trip</th>
                        <th style="width: 180px;">Litres</th>
                        <th style="width: 50px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($trip_allocations as $index => $row)
                        <tr wire:key="fuel-trip-allocation-{{ $row['trip_id'] }}">
                            <td>{{ $row['label'] }}</td>
                            <td>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="trip_allocations.{{ $index }}.quantity" placeholder="Litres" required>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-xs" wire:click="removeTripAllocation({{ $index }})" title="Remove trip"><i class="fa fa-times"></i></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th class="text-right">Allocated</th>
                        <th colspan="2">{{ number_format($this->allocatedLitres, 2) }} L of {{ number_format((float) $quantity, 2) }} L</th>
                    </tr>
                    @if ($this->unallocatedLitres > 0)
                        <tr>
                            <th class="text-right">Unallocated</th>
                            <th colspan="2" class="text-warning">{{ number_format($this->unallocatedLitres, 2) }} L <small class="text-muted">(posted to Fuel - Ops until allocated to a trip)</small></th>
                        </tr>
                    @elseif ($this->allocatedLitres - (float) $quantity > 0.005)
                        <tr>
                            <th class="text-right">Over by</th>
                            <th colspan="2" class="text-danger">{{ number_format($this->allocatedLitres - (float) $quantity, 2) }} L</th>
                        </tr>
                    @endif
                </tfoot>
            </table>
            @error('trip_allocations') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
</div>
@endif
