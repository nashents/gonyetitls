<div>
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="bulkFuelModal" tabindex="-1" role="dialog" aria-labelledby="bulkFuelLabel">
        <div class="modal-dialog" role="document" style="width: 96%; max-width: 1500px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="bulkFuelLabel"><i class="fas fa-gas-pump"></i> Bulk Create Fuel Orders
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    </h4>
                    <small class="text-muted">One station, one date and price for all the trucks. Each truck becomes its own fuel order, awaiting authorization. You can authorize them in bulk under Pending.</small>
                </div>
                <form wire:submit.prevent="store()">
                    <div class="modal-body">
                        {{-- Shared details --}}
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Fueling Station<span class="required" style="color: red">*</span></label>
                                    <select wire:model="container_id" class="form-control" required>
                                        <option value="">Select Fueling Station</option>
                                        @foreach ($containers as $container)
                                            <option value="{{ $container->id }}">{{ $container->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('container_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    @if ($this->container)
                                        <small class="text-muted">
                                            {{ $this->container->purchase_type }} · {{ $fuel_type }}
                                            @if ($this->isBulkBuy)
                                                · Bal: {{ (float) $this->container->balance }} l / {{ number_format((float) $this->container->account_balance, 2) }}
                                            @endif
                                        </small>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Fill Up Date<span class="required" style="color: red">*</span></label>
                                    <input type="datetime-local" wire:model.defer="date" class="form-control" required>
                                    @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Currency<span class="required" style="color: red">*</span></label>
                                    <select wire:model="currency_id" class="form-control" required>
                                        <option value="">Select</option>
                                        @foreach ($currencies as $currency)
                                            <option value="{{ $currency->id }}">{{ $currency->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            @if ($this->isForeignCurrency)
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Conversion Rate<span class="required" style="color: red">*</span></label>
                                        <input type="number" step="any" min="0" wire:model.lazy="exchange_rate" class="form-control">
                                        @error('exchange_rate') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            @endif
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Rate per litre<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" min="0" wire:model.lazy="unit_price" class="form-control" required>
                                    @error('unit_price') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        @if ($this->isBulkBuy)
                            <div class="form-group">
                                <label>Deduct from<span class="required" style="color: red">*</span></label>
                                <label class="radio-inline"><input type="radio" wire:model="deduct_from" value="account"> Account Balance</label>
                                <label class="radio-inline"><input type="radio" wire:model="deduct_from" value="quantity"> Quantity Balance (l)</label>
                                @error('deduct_from') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        {{-- Truck picker --}}
                        <div class="panel panel-default" style="padding: 10px; margin-bottom: 10px;">
                            <div class="row">
                                <div class="col-md-4">
                                    <input type="text" wire:model.debounce.300ms="truckSearch" class="form-control" placeholder="Search trucks by reg / fleet number...">
                                </div>
                                <div class="col-md-8">
                                    <button type="button" wire:click="addPickedTrucks" class="btn btn-default border-primary btn-rounded" @if (empty($pickedHorses)) disabled @endif>
                                        <i class="fa fa-plus"></i> Add {{ count($pickedHorses) ?: '' }} Selected Truck(s)
                                    </button>
                                </div>
                            </div>
                            <div style="max-height: 140px; overflow-y: auto; margin-top: 8px;">
                                @forelse ($trucks as $truck)
                                    <label wire:key="bulk-pick-{{ $truck->id }}" style="display: inline-block; width: 200px; font-weight: normal;">
                                        <input type="checkbox" wire:model="pickedHorses" value="{{ $truck->id }}"> {{ $truck->identifier_label }}
                                    </label>
                                @empty
                                    <small class="text-muted">No more trucks match.</small>
                                @endforelse
                            </div>
                        </div>

                        @error('rows') <div class="alert alert-danger">{{ $message }}</div> @enderror
                        @error('batch') <div class="alert alert-danger">{{ $message }}</div> @enderror

                        @if (count($rows) > 0)
                            <div style="overflow-x: auto;">
                                <table class="table table-bordered table-sm">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Truck</th>
                                            <th style="min-width: 180px">Driver<span style="color: red">*</span></th>
                                            <th style="min-width: 170px">Trip</th>
                                            <th>Category</th>
                                            <th style="width: 110px">Mileage<span style="color: red">*</span></th>
                                            <th style="width: 100px">Litres<span style="color: red">*</span></th>
                                            <th style="width: 120px">Full Tank?</th>
                                            <th style="width: 110px">Fill Up</th>
                                            <th class="text-right">Amount</th>
                                            <th style="min-width: 140px">Comments</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($rows as $i => $row)
                                            <tr wire:key="bulk-row-{{ $row['horse_id'] }}">
                                                <td>
                                                    <strong>{{ $row['label'] }}</strong>
                                                    @if ((float) $row['tank_capacity'] > 0)
                                                        <br><small class="text-muted">Tank {{ (float) $row['tank_capacity'] }} l</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <select wire:model.defer="rows.{{ $i }}.employee_id" class="form-control input-sm">
                                                        <option value="">Select driver</option>
                                                        @foreach ($employees as $employee)
                                                            <option value="{{ $employee->id }}">{{ $employee->name }} {{ $employee->surname }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('rows.'.$i.'.employee_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </td>
                                                <td>
                                                    <select wire:model="rows.{{ $i }}.trip_id" class="form-control input-sm">
                                                        <option value="">No trip</option>
                                                        @foreach ($rowTrips[$i] ?? [] as $trip)
                                                            <option value="{{ $trip['id'] }}">{{ $trip['label'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td>
                                                    @if ($row['trip_id'])
                                                        <select wire:model.defer="rows.{{ $i }}.category" class="form-control input-sm">
                                                            <option value="Self">Self</option>
                                                            <option value="Transporter">Transporter</option>
                                                            <option value="Customer">Customer</option>
                                                        </select>
                                                    @else
                                                        <small class="text-muted">-</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <input type="number" step="any" min="0" wire:model.defer="rows.{{ $i }}.mileage" class="form-control input-sm">
                                                    @error('rows.'.$i.'.mileage') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </td>
                                                <td>
                                                    <input type="number" step="any" min="0" wire:model.lazy="rows.{{ $i }}.quantity" class="form-control input-sm">
                                                    @error('rows.'.$i.'.quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </td>
                                                <td>
                                                    <select wire:model.defer="rows.{{ $i }}.is_full_tank" class="form-control input-sm">
                                                        <option value="1">Yes - Full</option>
                                                        <option value="0">No - Partial</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <select wire:model.defer="rows.{{ $i }}.fillup" class="form-control input-sm">
                                                        <option value="1">Initial</option>
                                                        <option value="0">Top Up</option>
                                                    </select>
                                                </td>
                                                <td class="text-right">
                                                    {{ number_format(round((float) ($row['quantity'] ?: 0) * (float) $unit_price, 2), 2) }}
                                                </td>
                                                <td>
                                                    <input type="text" wire:model.defer="rows.{{ $i }}.comments" class="form-control input-sm">
                                                </td>
                                                <td>
                                                    <a href="#" wire:click.prevent="removeRow({{ $i }})" class="text-danger" title="Remove truck"><i class="fa fa-times"></i></a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="5" class="text-right">{{ count($rows) }} truck(s)</th>
                                            <th>{{ $this->totalQuantity }} l</th>
                                            <th colspan="2"></th>
                                            <th class="text-right">{{ number_format($this->totalAmount, 2) }}</th>
                                            <th colspan="2"></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <p class="text-muted text-center">Tick the trucks above and click <strong>Add Selected Truck(s)</strong>.</p>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded" wire:loading.attr="disabled" @if (count($rows) === 0) disabled @endif>
                            <i class="fa fa-save"></i> Create {{ count($rows) ?: '' }} Fuel Order(s)
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
