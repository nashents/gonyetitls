<div>
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="bulkShiftsModal" tabindex="-1" role="dialog" aria-labelledby="bulkShiftsLabel">
        <div class="modal-dialog" role="document" style="width: 97%; max-width: 1700px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="bulkShiftsLabel"><i class="fa fa-clone"></i> Bulk Shifts
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    </h4>
                    <small class="text-muted">One shift per truck / vehicle, saved exactly like Add Shift: trips are created Offloaded and approved with their delivery notes, rehandling works and the shift fuel order are saved with each shift. Set the defaults first; every truck you add starts from them and can then be changed.</small>
                </div>
                <form wire:submit.prevent="store()">
                    <div class="modal-body">
                        @php($cargoTypes = $this->cargoTypes)
                        @php($isTrips = $for === 'Trips')

                        {{-- Shared shift details --}}
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Type<span style="color: red">*</span></label>
                                    <select wire:model="type" class="form-control" required>
                                        <option value="">Select Option</option>
                                        <option value="Morning">Morning</option>
                                        <option value="Backshift">Backshift</option>
                                        <option value="Night">Night</option>
                                    </select>
                                    @error('type') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>For<span style="color: red">*</span></label>
                                    <select wire:model="for" class="form-control" required>
                                        <option value="Trips">Trips</option>
                                        <option value="Rehandling">Rehandling Work</option>
                                    </select>
                                    @error('for') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Date<span style="color: red">*</span></label>
                                    <input type="date" wire:model.lazy="date" class="form-control" required>
                                    @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Duty Start Time<span style="color: red">*</span></label>
                                    <input type="datetime-local" wire:model.lazy="shift_start_time" class="form-control">
                                    <small class="text-muted">Applies to every truck.</small>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Duty Close Time<span style="color: red">*</span></label>
                                    <input type="datetime-local" wire:model.lazy="shift_end_time" class="form-control">
                                    <small class="text-muted">Applies to every truck.</small>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Team<span style="color: red">*</span></label>
                                    <select wire:model="team_id" class="form-control" required>
                                        <option value="">Select Team</option>
                                        @foreach ($teams as $team)
                                            <option value="{{ $team->id }}">{{ $team->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('team_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Customer<span style="color: red">*</span></label>
                                    <select wire:model="customer_id" class="form-control" required>
                                        <option value="">Select Customer</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('customer_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Defaults for new trucks --}}
                        <div class="panel panel-default" style="padding: 10px 10px 0; margin-bottom: 10px;">
                            @if ($isTrips)
                                <strong>Default trips for each new truck</strong>
                                <div class="row" style="margin-top: 6px;">
                                    <div class="col-md-1">
                                        <div class="form-group">
                                            <label>Trips</label>
                                            <input type="number" min="0" max="20" wire:model.lazy="tripsPerTruck" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Haulage</label>
                                            <select wire:model.lazy="tripDefaults.haulage_type" class="form-control">
                                                <option value="">Select Option</option>
                                                <option value="short_haul">Short Haul</option>
                                                <option value="long_haul">Long Haul</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Cargo</label>
                                            <select wire:model.lazy="tripDefaults.cargo_id" class="form-control">
                                                <option value="">Select Cargo</option>
                                                @foreach ($cargos as $cargo)
                                                    <option value="{{ $cargo->id }}">{{ $cargo->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <div class="form-group">
                                            <label>Weight(t)</label>
                                            <input type="number" step="any" min="0" wire:model.lazy="tripDefaults.weight" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <div class="form-group">
                                            <label>Rate</label>
                                            <input type="number" step="any" min="0" wire:model.lazy="tripDefaults.rate" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <div class="form-group">
                                            <label>Measure</label>
                                            <select wire:model.lazy="tripDefaults.measurement" class="form-control">
                                                <option value=""></option>
                                                @foreach ($measurements->get($cargoTypes[$tripDefaults['cargo_id'] ?? ''] ?? 'Solid', collect()) as $measurement)
                                                    <option value="{{ $measurement->name }}">{{ $measurement->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <div class="form-group">
                                            <label>From</label>
                                            <select wire:model.lazy="tripDefaults.from" class="form-control">
                                                <option value=""></option>
                                                @foreach ($destinations as $destination)
                                                    <option value="{{ $destination->id }}">{{ optional($destination->country)->name }} {{ $destination->city }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <div class="form-group">
                                            <label>Loading Pt</label>
                                            <select wire:model.lazy="tripDefaults.loading_point_id" class="form-control">
                                                <option value=""></option>
                                                @foreach ($loadingPoints as $point)
                                                    <option value="{{ $point->id }}">{{ $point->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <div class="form-group">
                                            <label>To</label>
                                            <select wire:model.lazy="tripDefaults.to" class="form-control">
                                                <option value=""></option>
                                                @foreach ($destinations as $destination)
                                                    <option value="{{ $destination->id }}">{{ optional($destination->country)->name }} {{ $destination->city }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <div class="form-group">
                                            <label>Offloading Pt</label>
                                            <select wire:model.lazy="tripDefaults.offloading_point_id" class="form-control">
                                                <option value=""></option>
                                                @foreach ($offloadingPoints as $point)
                                                    <option value="{{ $point->id }}">{{ $point->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <strong>Default rehandling works for each new truck</strong>
                                <div class="row" style="margin-top: 6px;">
                                    <div class="col-md-1">
                                        <div class="form-group">
                                            <label>Works</label>
                                            <input type="number" min="0" max="20" wire:model.lazy="worksPerTruck" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Work Description</label>
                                            <select wire:model.lazy="workDefaults.work_id" class="form-control">
                                                <option value="">Select Work/Job</option>
                                                @foreach ($works as $work)
                                                    <option value="{{ $work->id }}">{{ $work->description }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Location / Work Site</label>
                                            <select wire:model.lazy="workDefaults.location_id" class="form-control">
                                                <option value="">Select Site</option>
                                                @foreach ($locations as $location)
                                                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Cargo</label>
                                            <select wire:model.lazy="workDefaults.cargo_id" class="form-control">
                                                <option value="">Select Cargo</option>
                                                @foreach ($cargos as $cargo)
                                                    <option value="{{ $cargo->id }}">{{ $cargo->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Weight(t)</label>
                                            <input type="number" step="any" min="0" wire:model.lazy="workDefaults.weight" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <label style="font-weight: bold;"><input type="checkbox" wire:model="fuelForAll"> Fuel order on every truck</label>
                            <small class="text-muted">Fuel defaults below fill each truck's fuel order when it is ticked; quantity, mileage and hours are entered per truck.</small>
                            <div class="row" style="margin-top: 6px;">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Fueling Station</label>
                                        <select wire:model="fuelDefaults.container_id" class="form-control">
                                            <option value="">Select Station</option>
                                            @foreach ($containers as $container)
                                                <option value="{{ $container->id }}">{{ $container->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label>Category</label>
                                        <select wire:model.lazy="fuelDefaults.category" class="form-control">
                                            <option value=""></option>
                                            <option value="Customer">Customer</option>
                                            <option value="Self">Self</option>
                                            <option value="Transporter">Transporter</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Expense Account</label>
                                        <select wire:model.lazy="fuelDefaults.account_id" class="form-control">
                                            <option value=""></option>
                                            @foreach ($accounts as $account)
                                                <option value="{{ $account->id }}">{{ $account->name }}</option>
                                            @endforeach
                                        </select>
                                        @if ($accounts->isEmpty()) <small class="text-muted">Tick a fuel order to load accounts.</small> @endif
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label>Fuel Type</label>
                                        <select wire:model.lazy="fuelDefaults.fuel_type" class="form-control">
                                            <option value="Diesel">Diesel</option>
                                            <option value="Petrol">Petrol</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label>Full Tank?</label>
                                        <select wire:model.lazy="fuelDefaults.is_full_tank" class="form-control">
                                            <option value=""></option>
                                            <option value="1">Yes</option>
                                            <option value="0">No</option>
                                        </select>
                                    </div>
                                </div>
                                @if ($isTrips)
                                    <div class="col-md-1">
                                        <div class="form-group">
                                            <label>Allocation To</label>
                                            <select wire:model.lazy="fuelDefaults.allocation_to" class="form-control">
                                                <option value=""></option>
                                                <option value="long_haul">Long Haul</option>
                                                <option value="short_haul">Short Haul</option>
                                            </select>
                                        </div>
                                    </div>
                                @endif
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Currency</label>
                                        <select wire:model="fuelDefaults.currency_id" class="form-control">
                                            @foreach ($currencies as $currency)
                                                <option value="{{ $currency->id }}">{{ $currency->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                @if ($this->isForeign($fuelDefaults['currency_id'] ?? null))
                                    <div class="col-md-1">
                                        <div class="form-group">
                                            <label>Conv. Rate</label>
                                            <input type="number" step="any" min="0" wire:model.lazy="fuelDefaults.exchange_rate" class="form-control">
                                        </div>
                                    </div>
                                @endif
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label>Rate/Litre</label>
                                        <input type="number" step="any" min="0" wire:model.lazy="fuelDefaults.unit_price" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Truck picker --}}
                        <div class="panel panel-default" style="padding: 10px; margin-bottom: 10px;">
                            <div class="row">
                                <div class="col-md-2" style="padding-top: 6px;">
                                    <label class="radio-inline"><input type="radio" wire:model="equipment" value="Horse"> Horse</label>
                                    <label class="radio-inline"><input type="radio" wire:model="equipment" value="Vehicle"> Vehicle</label>
                                </div>
                                <div class="col-md-3">
                                    <select wire:model="pickerTransporterId" class="form-control">
                                        <option value="">All transporters</option>
                                        @foreach ($transporters as $transporter)
                                            <option value="{{ $transporter->id }}">{{ $transporter->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" wire:model.debounce.300ms="truckSearch" class="form-control" placeholder="Search by reg / fleet number...">
                                </div>
                                <div class="col-md-4">
                                    <button type="button" wire:click="addPicked" wire:loading.attr="disabled" class="btn btn-default border-primary btn-rounded" @if (empty($picked)) disabled @endif>
                                        <i class="fa fa-plus"></i> Add {{ count($picked) ?: '' }} Selected
                                    </button>
                                </div>
                            </div>
                            <div style="max-height: 140px; overflow-y: auto; margin-top: 8px;">
                                @forelse ($assets as $asset)
                                    <label wire:key="bulk-shift-pick-{{ $equipment }}-{{ $asset->id }}" style="display: inline-block; width: 260px; font-weight: normal;">
                                        <input type="checkbox" wire:model="picked" value="{{ $asset->id }}"> {{ $asset->identifier_label }}
                                        <small class="text-muted">{{ optional($asset->transporter)->name }}</small>
                                    </label>
                                @empty
                                    <small class="text-muted">No more {{ strtolower($equipment) }}s match.</small>
                                @endforelse
                            </div>
                        </div>

                        @error('rows') <div class="alert alert-danger">{{ $message }}</div> @enderror

                        @if (count($rows) === 0)
                            <p class="text-muted text-center">Tick the trucks above and click <strong>Add Selected</strong>. Each truck's assigned driver, trailers and mileage are filled in for you.</p>
                        @endif

                        {{-- One card per shift --}}
                        @foreach ($rows as $i => $row)
                            @php($p = "rows.$i.")
                            <div class="panel panel-default" wire:key="bulk-shift-{{ $row['key'] }}" style="padding: 10px; margin-bottom: 12px; border-left: 4px solid #337ab7;">
                                <div style="margin-bottom: 8px;">
                                    <strong>Shift {{ $i + 1 }}: {{ $row['label'] }}</strong>
                                    <small class="text-muted">{{ $row['equipment'] }} &middot; {{ $row['transporter_name'] }}</small>
                                    @error($p.'key') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    <span style="float: right;">
                                        @if ($i > 0)
                                            <a href="#" wire:click.prevent="copyPrevious({{ $i }})" title="Copy trips / works and fuel order setup from shift {{ $i }}"><i class="fa fa-copy"></i> Copy previous</a>
                                            &nbsp;
                                        @endif
                                        <a href="#" wire:click.prevent="removeRow({{ $i }})" class="text-danger"><i class="fa fa-times"></i> Remove</a>
                                    </span>
                                </div>

                                <div class="row">
                                    <div class="col-md-2">
                                        <label>Driver<span style="color: red">*</span></label>
                                        <select wire:model="{{ $p }}driver_id" class="form-control input-sm">
                                            <option value="">Select Driver</option>
                                            @foreach ($drivers->get($row['transporter_id'], collect()) as $driver)
                                                <option value="{{ $driver->id }}">{{ $driver->employee->name }} {{ $driver->employee->surname }}</option>
                                            @endforeach
                                        </select>
                                        @error($p.'driver_id') <small class="error" style="color:red">{{ $message }}</small> @enderror
                                    </div>
                                    @if ($row['equipment'] === 'Horse')
                                        <div class="col-md-2">
                                            <label>Trailer(s)</label>
                                            <select wire:model="{{ $p }}trailer_ids" class="form-control input-sm" multiple size="2">
                                                @foreach ($trailers->get($row['transporter_id'], collect()) as $trailer)
                                                    <option value="{{ $trailer->id }}">{{ $trailer->identifier_label ?? $trailer->registration_number }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                    <div class="col-md-2">
                                        <label>Duty Start<span style="color: red">*</span></label>
                                        <input type="datetime-local" wire:model.lazy="{{ $p }}shift_start_time" class="form-control input-sm">
                                        @error($p.'shift_start_time') <small class="error" style="color:red">{{ $message }}</small> @enderror
                                    </div>
                                    <div class="col-md-2">
                                        <label>Duty Close<span style="color: red">*</span></label>
                                        <input type="datetime-local" wire:model.lazy="{{ $p }}shift_end_time" class="form-control input-sm">
                                        @error($p.'shift_end_time') <small class="error" style="color:red">{{ $message }}</small> @enderror
                                    </div>
                                    <div class="col-md-1">
                                        <label>Open Km @if ($isTrips)<span style="color: red">*</span>@endif</label>
                                        <input type="number" step="any" min="0" wire:model.lazy="{{ $p }}open_mileage" class="form-control input-sm">
                                        @error($p.'open_mileage') <small class="error" style="color:red">{{ $message }}</small> @enderror
                                    </div>
                                    <div class="col-md-1">
                                        <label>Close Km @if ($isTrips)<span style="color: red">*</span>@endif</label>
                                        <input type="number" step="any" min="0" wire:model.lazy="{{ $p }}close_mileage" class="form-control input-sm">
                                        @if ($row['live_source']) <small class="text-success">Live: {{ $row['live_source'] }}</small> @endif
                                        @error($p.'close_mileage') <small class="error" style="color:red">{{ $message }}</small> @enderror
                                    </div>
                                    @if ($isTrips)
                                        <div class="col-md-1">
                                            <label>Calc. Dist.</label>
                                            <input type="number" step="any" min="0" wire:model.lazy="{{ $p }}calculated_mileage" class="form-control input-sm">
                                        </div>
                                    @endif
                                </div>

                                @if (! $isTrips)
                                    <div class="row" style="margin-top: 6px;">
                                        @foreach (['depart_workshop_time' => 'Depart Workshop', 'arrive_location_time' => 'Arrive Worksite', 'depart_location_time' => 'Depart Worksite', 'arrive_workshop_time' => 'Arrive Workshop'] as $field => $label)
                                            <div class="col-md-2">
                                                <label>{{ $label }}</label>
                                                <input type="time" wire:model.lazy="{{ $p.$field }}" class="form-control input-sm">
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Trips --}}
                                @if ($isTrips)
                                    <div style="overflow-x: auto; margin-top: 8px;">
                                        <table class="table table-bordered table-condensed" style="margin-bottom: 4px; font-size: 12px;">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th style="min-width: 110px">Trip Ref</th>
                                                    <th style="min-width: 110px">Haulage*</th>
                                                    <th style="min-width: 140px">Cargo*</th>
                                                    <th style="min-width: 80px">Weight(t)</th>
                                                    <th style="min-width: 80px">Rate</th>
                                                    <th style="min-width: 170px">Qty / Litreage (Amb · @20)</th>
                                                    <th style="min-width: 100px">Measure</th>
                                                    <th style="min-width: 140px">From*</th>
                                                    <th style="min-width: 130px">Loading Pt*</th>
                                                    <th style="min-width: 140px">To*</th>
                                                    <th style="min-width: 130px">Offloading Pt*</th>
                                                    <th style="min-width: 190px">Arrive / Depart LP</th>
                                                    <th style="min-width: 190px">Arrive / Depart OP</th>
                                                    <th style="min-width: 160px">Start / End Km</th>
                                                    <th style="min-width: 160px">Start / End Hrs</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($row['trips'] as $t => $trip)
                                                    @php($tp = $p."trips.$t.")
                                                    @php($cargoType = $cargoTypes[$trip['cargo_id'] ?? ''] ?? null)
                                                    <tr wire:key="bulk-shift-trip-{{ $trip['key'] }}">
                                                        <td>{{ $t + 1 }}</td>
                                                        <td>
                                                            <input type="text" wire:model.lazy="{{ $tp }}trip_ref" class="form-control input-sm">
                                                            @error($tp.'trip_ref') <small style="color:red">{{ $message }}</small> @enderror
                                                        </td>
                                                        <td>
                                                            <select wire:model.lazy="{{ $tp }}haulage_type" class="form-control input-sm">
                                                                <option value=""></option>
                                                                <option value="short_haul">Short Haul</option>
                                                                <option value="long_haul">Long Haul</option>
                                                            </select>
                                                            @error($tp.'haulage_type') <small style="color:red">{{ $message }}</small> @enderror
                                                        </td>
                                                        <td>
                                                            <select wire:model="{{ $tp }}cargo_id" class="form-control input-sm">
                                                                <option value=""></option>
                                                                @foreach ($cargos as $cargo)
                                                                    <option value="{{ $cargo->id }}">{{ $cargo->name }}</option>
                                                                @endforeach
                                                            </select>
                                                            @error($tp.'cargo_id') <small style="color:red">{{ $message }}</small> @enderror
                                                        </td>
                                                        <td><input type="number" step="any" min="0" wire:model.lazy="{{ $tp }}weight" class="form-control input-sm"></td>
                                                        <td><input type="number" step="any" min="0" wire:model.lazy="{{ $tp }}rate" class="form-control input-sm"></td>
                                                        <td>
                                                            @if ($cargoType === 'Liquid')
                                                                <div style="display: flex; gap: 3px;">
                                                                    <input type="number" step="any" min="0" wire:model.lazy="{{ $tp }}litreage" class="form-control input-sm" placeholder="Ambient">
                                                                    <input type="number" step="any" min="0" wire:model.lazy="{{ $tp }}litreage_at_20" class="form-control input-sm" placeholder="@ 20°">
                                                                </div>
                                                            @elseif ($cargoType === 'Solid')
                                                                <input type="number" step="any" min="0" wire:model.lazy="{{ $tp }}quantity" class="form-control input-sm" placeholder="Quantity">
                                                            @else
                                                                <small class="text-muted">Pick cargo</small>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <select wire:model.lazy="{{ $tp }}measurement" class="form-control input-sm" @if (! $cargoType) disabled @endif>
                                                                <option value=""></option>
                                                                @foreach ($measurements->get($cargoType, collect()) as $measurement)
                                                                    <option value="{{ $measurement->name }}">{{ $measurement->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <select wire:model.lazy="{{ $tp }}from" class="form-control input-sm">
                                                                <option value=""></option>
                                                                @foreach ($destinations as $destination)
                                                                    <option value="{{ $destination->id }}">{{ optional($destination->country)->name }} {{ $destination->city }}</option>
                                                                @endforeach
                                                            </select>
                                                            @error($tp.'from') <small style="color:red">{{ $message }}</small> @enderror
                                                        </td>
                                                        <td>
                                                            <select wire:model.lazy="{{ $tp }}loading_point_id" class="form-control input-sm">
                                                                <option value=""></option>
                                                                @foreach ($loadingPoints as $point)
                                                                    <option value="{{ $point->id }}">{{ $point->name }}</option>
                                                                @endforeach
                                                            </select>
                                                            @error($tp.'loading_point_id') <small style="color:red">{{ $message }}</small> @enderror
                                                        </td>
                                                        <td>
                                                            <select wire:model.lazy="{{ $tp }}to" class="form-control input-sm">
                                                                <option value=""></option>
                                                                @foreach ($destinations as $destination)
                                                                    <option value="{{ $destination->id }}">{{ optional($destination->country)->name }} {{ $destination->city }}</option>
                                                                @endforeach
                                                            </select>
                                                            @error($tp.'to') <small style="color:red">{{ $message }}</small> @enderror
                                                        </td>
                                                        <td>
                                                            <select wire:model.lazy="{{ $tp }}offloading_point_id" class="form-control input-sm">
                                                                <option value=""></option>
                                                                @foreach ($offloadingPoints as $point)
                                                                    <option value="{{ $point->id }}">{{ $point->name }}</option>
                                                                @endforeach
                                                            </select>
                                                            @error($tp.'offloading_point_id') <small style="color:red">{{ $message }}</small> @enderror
                                                        </td>
                                                        <td>
                                                            <div style="display: flex; gap: 3px;">
                                                                <input type="time" wire:model.lazy="{{ $tp }}arrive_lp" class="form-control input-sm" title="Arrive @ Loading Point">
                                                                <input type="time" wire:model.lazy="{{ $tp }}depart_lp" class="form-control input-sm" title="Depart Loading Point">
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div style="display: flex; gap: 3px;">
                                                                <input type="time" wire:model.lazy="{{ $tp }}arrive_op" class="form-control input-sm" title="Arrive @ Offloading Point">
                                                                <input type="time" wire:model.lazy="{{ $tp }}depart_op" class="form-control input-sm" title="Depart Offloading Point">
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div style="display: flex; gap: 3px;">
                                                                <input type="number" step="any" min="0" wire:model.lazy="{{ $tp }}starting_mileage" class="form-control input-sm" placeholder="Start">
                                                                <input type="number" step="any" min="0" wire:model.lazy="{{ $tp }}ending_mileage" class="form-control input-sm" placeholder="End">
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div style="display: flex; gap: 3px;">
                                                                <input type="number" step="any" min="0" wire:model.lazy="{{ $tp }}starting_hours" class="form-control input-sm" placeholder="Start">
                                                                <input type="number" step="any" min="0" wire:model.lazy="{{ $tp }}ending_hours" class="form-control input-sm" placeholder="End">
                                                            </div>
                                                        </td>
                                                        <td><a href="#" wire:click.prevent="removeTrip({{ $i }}, {{ $t }})" class="text-danger" title="Remove trip"><i class="fa fa-times"></i></a></td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="17" class="text-muted text-center">No trips on this shift.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <a href="#" wire:click.prevent="addTrip({{ $i }})" class="btn btn-success btn-rounded btn-xs"><i class="fa fa-plus"></i> Trip</a>
                                @else
                                    {{-- Rehandling works --}}
                                    <div style="overflow-x: auto; margin-top: 8px;">
                                        <table class="table table-bordered table-condensed" style="margin-bottom: 4px; font-size: 12px;">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th style="min-width: 160px">Work*</th>
                                                    <th style="min-width: 140px">Location*</th>
                                                    <th style="min-width: 90px">Start Time*</th>
                                                    <th style="min-width: 80px">Start Hrs*</th>
                                                    <th style="min-width: 80px">Start Km</th>
                                                    <th style="min-width: 90px">Close Time*</th>
                                                    <th style="min-width: 80px">Close Hrs*</th>
                                                    <th style="min-width: 80px">Close Km</th>
                                                    <th style="min-width: 130px">Cargo</th>
                                                    <th style="min-width: 80px">Weight(t)</th>
                                                    <th style="min-width: 70px">Loads</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($row['works'] as $w => $work)
                                                    @php($wp = $p."works.$w.")
                                                    <tr wire:key="bulk-shift-work-{{ $work['key'] }}">
                                                        <td>{{ $w + 1 }}</td>
                                                        <td>
                                                            <select wire:model.lazy="{{ $wp }}work_id" class="form-control input-sm">
                                                                <option value=""></option>
                                                                @foreach ($works as $job)
                                                                    <option value="{{ $job->id }}">{{ $job->description }}</option>
                                                                @endforeach
                                                            </select>
                                                            @error($wp.'work_id') <small style="color:red">{{ $message }}</small> @enderror
                                                        </td>
                                                        <td>
                                                            <select wire:model.lazy="{{ $wp }}location_id" class="form-control input-sm">
                                                                <option value=""></option>
                                                                @foreach ($locations as $location)
                                                                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                                                                @endforeach
                                                            </select>
                                                            @error($wp.'location_id') <small style="color:red">{{ $message }}</small> @enderror
                                                        </td>
                                                        @foreach (['start_time' => 'time', 'starting_hours' => 'number', 'starting_mileage' => 'number', 'stop_time' => 'time', 'ending_hours' => 'number', 'ending_mileage' => 'number'] as $field => $inputType)
                                                            <td>
                                                                <input type="{{ $inputType }}" @if ($inputType === 'number') step="any" min="0" @endif wire:model.lazy="{{ $wp.$field }}" class="form-control input-sm">
                                                                @error($wp.$field) <small style="color:red">{{ $message }}</small> @enderror
                                                            </td>
                                                        @endforeach
                                                        <td>
                                                            <select wire:model.lazy="{{ $wp }}cargo_id" class="form-control input-sm">
                                                                <option value=""></option>
                                                                @foreach ($cargos as $cargo)
                                                                    <option value="{{ $cargo->id }}">{{ $cargo->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td><input type="number" step="any" min="0" wire:model.lazy="{{ $wp }}weight" class="form-control input-sm"></td>
                                                        <td><input type="number" step="any" min="0" wire:model.lazy="{{ $wp }}loads" class="form-control input-sm"></td>
                                                        <td><a href="#" wire:click.prevent="removeWork({{ $i }}, {{ $w }})" class="text-danger" title="Remove work"><i class="fa fa-times"></i></a></td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="13" class="text-muted text-center">No rehandling work on this shift.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <a href="#" wire:click.prevent="addWork({{ $i }})" class="btn btn-success btn-rounded btn-xs"><i class="fa fa-plus"></i> Work</a>
                                @endif

                                <div class="row" style="margin-top: 8px;">
                                    <div class="col-md-12">
                                        <input type="text" wire:model.lazy="{{ $p }}comments" class="form-control input-sm" placeholder="Comments about this shift">
                                    </div>
                                </div>

                                {{-- Fuel order --}}
                                <div style="margin-top: 8px;">
                                    <label style="font-weight: bold;"><input type="checkbox" wire:model="{{ $p }}fuel_order"> Fuel Order</label>
                                </div>
                                @if ($row['fuel_order'])
                                    @php($fp = $p.'fuel.')
                                    @php($fuel = $row['fuel'])
                                    @php($fromTruck = $row['equipment'] === 'Horse' && ($fuel['source'] ?? '') === 'truck')
                                    <div class="row" style="background: #f7f9fc; padding-top: 6px; margin: 0;">
                                        @if ($row['equipment'] === 'Horse')
                                            <div class="col-md-2">
                                                <label>Fuel Source*</label><br>
                                                <label class="radio-inline"><input type="radio" wire:model="{{ $fp }}source" value="station"> Station</label>
                                                <label class="radio-inline"><input type="radio" wire:model="{{ $fp }}source" value="truck"> Truck</label>
                                            </div>
                                        @endif
                                        <div class="col-md-2">
                                            @if ($fromTruck)
                                                <label>Source Truck*</label>
                                                <select wire:model="{{ $fp }}source_horse_id" class="form-control input-sm">
                                                    <option value=""></option>
                                                    @foreach ($sourceHorses as $horse)
                                                        @continue($horse->id == $row['horse_id'])
                                                        <option value="{{ $horse->id }}">{{ $horse->identifier_label }}</option>
                                                    @endforeach
                                                </select>
                                                @error($fp.'source_horse_id') <small style="color:red">{{ $message }}</small> @enderror
                                            @else
                                                <label>Fueling Station*</label>
                                                <select wire:model="{{ $fp }}container_id" class="form-control input-sm">
                                                    <option value=""></option>
                                                    @foreach ($containers as $container)
                                                        <option value="{{ $container->id }}">{{ $container->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error($fp.'container_id') <small style="color:red">{{ $message }}</small> @enderror
                                            @endif
                                        </div>
                                        <div class="col-md-1">
                                            <label>Category*</label>
                                            <select wire:model.lazy="{{ $fp }}category" class="form-control input-sm">
                                                <option value=""></option>
                                                <option value="Customer">Customer</option>
                                                <option value="Self">Self</option>
                                                <option value="Transporter">Transporter</option>
                                            </select>
                                            @error($fp.'category') <small style="color:red">{{ $message }}</small> @enderror
                                        </div>
                                        <div class="col-md-2">
                                            <label>Expense Account</label>
                                            <select wire:model.lazy="{{ $fp }}account_id" class="form-control input-sm">
                                                <option value=""></option>
                                                @foreach ($accounts as $account)
                                                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-1">
                                            <label>Fuel Type*</label>
                                            <select wire:model.lazy="{{ $fp }}fuel_type" class="form-control input-sm">
                                                <option value=""></option>
                                                <option value="Diesel">Diesel</option>
                                                <option value="Petrol">Petrol</option>
                                            </select>
                                            @error($fp.'fuel_type') <small style="color:red">{{ $message }}</small> @enderror
                                        </div>
                                        <div class="col-md-1">
                                            <label>Full Tank?*</label>
                                            <select wire:model.lazy="{{ $fp }}is_full_tank" class="form-control input-sm">
                                                <option value=""></option>
                                                <option value="1">Yes</option>
                                                <option value="0">No</option>
                                            </select>
                                            @error($fp.'is_full_tank') <small style="color:red">{{ $message }}</small> @enderror
                                        </div>
                                        @if ($isTrips)
                                            <div class="col-md-1">
                                                <label>Allocation To*</label>
                                                <select wire:model.lazy="{{ $fp }}allocation_to" class="form-control input-sm">
                                                    <option value=""></option>
                                                    <option value="long_haul">Long Haul</option>
                                                    <option value="short_haul">Short Haul</option>
                                                </select>
                                                @error($fp.'allocation_to') <small style="color:red">{{ $message }}</small> @enderror
                                            </div>
                                        @endif
                                        <div class="col-md-2">
                                            <label>Currency</label>
                                            <select wire:model="{{ $fp }}currency_id" class="form-control input-sm">
                                                <option value=""></option>
                                                @foreach ($currencies as $currency)
                                                    <option value="{{ $currency->id }}">{{ $currency->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row" style="background: #f7f9fc; padding-bottom: 6px; margin: 0;">
                                        @if ($this->isForeign($fuel['currency_id'] ?? null))
                                            <div class="col-md-1">
                                                <label>Conv. Rate*</label>
                                                <input type="number" step="any" min="0" wire:model.lazy="{{ $fp }}exchange_rate" class="form-control input-sm">
                                                @error($fp.'exchange_rate') <small style="color:red">{{ $message }}</small> @enderror
                                            </div>
                                        @endif
                                        <div class="col-md-1">
                                            <label>Quantity*</label>
                                            <input type="number" step="any" min="0" wire:model.lazy="{{ $fp }}quantity" class="form-control input-sm">
                                            @error($fp.'quantity') <small style="color:red">{{ $message }}</small> @enderror
                                        </div>
                                        <div class="col-md-1">
                                            <label>Rate</label>
                                            <input type="number" step="any" min="0" wire:model.lazy="{{ $fp }}unit_price" class="form-control input-sm">
                                        </div>
                                        <div class="col-md-1">
                                            <label>Total</label>
                                            <input type="number" step="any" min="0" wire:model.lazy="{{ $fp }}amount" class="form-control input-sm">
                                        </div>
                                        <div class="col-md-1">
                                            <label>Mileage @if ($isTrips)*@endif</label>
                                            <input type="number" step="any" min="0" wire:model.lazy="{{ $fp }}mileage" class="form-control input-sm">
                                            @error($fp.'mileage') <small style="color:red">{{ $message }}</small> @enderror
                                        </div>
                                        <div class="col-md-1">
                                            <label>Hours*</label>
                                            <input type="number" step="any" min="0" wire:model.lazy="{{ $fp }}hours" class="form-control input-sm">
                                            @error($fp.'hours') <small style="color:red">{{ $message }}</small> @enderror
                                        </div>
                                        <div class="col-md-5">
                                            <label>Fuel Comments</label>
                                            <input type="text" wire:model.lazy="{{ $fp }}comments" class="form-control input-sm">
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div class="modal-footer">
                        @if ($errors->any())
                            <span class="text-danger" style="float: left; padding-top: 8px;"><i class="fa fa-exclamation-triangle"></i> Fix the {{ $errors->count() }} highlighted field(s).</span>
                        @endif
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded" wire:loading.attr="disabled" @if (count($rows) === 0) disabled @endif>
                            <i class="fa fa-save"></i> Create {{ count($rows) ?: '' }} Shift(s)
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
