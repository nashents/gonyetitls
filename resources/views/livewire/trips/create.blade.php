<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>New Trip Schedule</h5>
                                <small style="color: green">After you insert your search query press enter for the search results to appear.</small> <br>
                                <small style="color: green">Asterisk <span style="color: red">(*)</span> sign indicates all mandatory fields. You should and cannot save trip form without completing those fields.</small>
                            </div>
                            
                        </div>
                        <div class="panel-body">

                            <form wire:submit.prevent="store()" class="p-20" enctype="multipart/form-data">
                              
                                <h5 class="underline mt-n">Trip Info</h5>
                                <div class="row">
                                    <!-- Trip Type -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="trip_type">
                                                <a href="{{ route('trip_types.index') }}" target="_blank" style="color: blue">Trip Types</a>
                                                <span class="required text-danger">*</span>
                                            </label>
                                            <select class="form-control" wire:model.debounce.300ms="selectedTripType" required>
                                                <option value="">Select Trip Type</option>
                                                @foreach ($trip_types as $trip_type)
                                                    <option value="{{ $trip_type->id }}">{{ $trip_type->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('selectedTripType') <span class="text-danger error">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                
                                    <!-- Trip Reference -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="trip_ref">Reference#</label>
                                            <input type="text" class="form-control" wire:model.debounce.300ms="trip_ref" placeholder="Trip Reference#" {{ $trip_type_name == 'Return' ? 'disabled' : '' }} />
                                            @error('trip_ref') <span class="text-danger error">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                
                                    <!-- Trip Group -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="trip_group">
                                                <a href="{{ route('trip_groups.index') }}" target="_blank" style="color: blue">Trips Tracking Groups</a>
                                            </label>
                                            <select class="form-control" wire:model.debounce.300ms="trip_group_id">
                                                <option value="">Select Trips Tracking Group</option>
                                                @foreach ($trip_groups as $trip_group)
                                                    <option value="{{ $trip_group->id }}">{{ $trip_group->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('trip_group_id') <span class="text-danger error">{{ $message }}</span> @enderror
                                            <small>
                                                <a href="{{ route('trip_groups.index') }}" target="_blank">
                                                    <i class="fa fa-plus-square-o"></i> New Trips Tracking Group
                                                </a>
                                            </small>
                                            <a href="#" wire:click.prevent="refresh('tracking_groups')" class="float-end">
                                                <i class="fa fa-refresh" aria-hidden="true"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Return Trip Search -->
                                @if ($trip_type_name == 'Return')
                                    <div class="form-group">
                                        <label for="trips">All Trips</label>
                                        <input type="text" wire:model.lazy="searchTrip" placeholder="Search with trip#, trip reference, horse reg#..." class="form-control">
                                        <select class="form-control" wire:model.debounce.300ms="selectedTrip" size="4">
                                            <option value="">Select Initial Trip</option>
                                            @foreach ($trips as $trip)
                                                <option value="{{ $trip->id }}">
                                                    {{ $trip->trip_number }}{{ $trip->trip_ref ? '/' . $trip->trip_ref : '' }} | {{ $trip->start_date }} | {{ $trip->customer->name ?? '' }} | {{ $trip->horse->registration_number ?? '' }} |
                                                    @if ($from = $this->getDestination($trip->from))
                                                        {{ $from->country->name ?? '' }} {{ $from->city ?? '' }}
                                                    @endif
                                                    {{ $trip->loading_point ? '(' . $trip->loading_point->name . ')' : '' }} -
                                                    @if ($to = $this->getDestination($trip->to))
                                                        {{ $to->country->name ?? '' }} {{ $to->city ?? '' }}
                                                    @endif
                                                    {{ $trip->offloading_point ? '(' . $trip->offloading_point->name . ')' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small>Select the initial trip to link with the return trip</small>
                                        @error('selectedTrip') <span class="text-danger error">{{ $message }}</span> @enderror
                                    </div>
                                @endif
                                
                                <!-- Borders & Clearing Agents Section -->
                                @if (in_array($trip_type_name, ['Intransit', 'Cross Border', 'Inward', 'Outward', 'Return']))
                                    <div class="row">
                                        <!-- First Border & Agent -->
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="borders">
                                                            <a href="{{ route('borders.index') }}" target="_blank" style="color: blue">Border(s)</a>
                                                        </label>
                                                        <select class="form-control" wire:model.debounce.300ms="selectedBorder.0">
                                                            <option value="">Select Border</option>
                                                            @if (!is_null($selectedTripType))
                                                                @foreach ($borders as $border)
                                                                    <option value="{{ $border->id }}">{{ $border->name }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                        <small>
                                                            <a href="{{ route('borders.index') }}" target="_blank">
                                                                <i class="fa fa-plus-square-o"></i> New Border
                                                            </a>
                                                        </small>
                                                        <a href="#" wire:click.prevent="refresh('borders')" class="float-end">
                                                            <i class="fa fa-refresh"></i>
                                                        </a>
                                                        @error('selectedBorder.0') <span class="text-danger error">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="clearing_agents">
                                                            <a href="{{ route('clearing_agents.index') }}" target="_blank" style="color: blue">Clearing Agent(s)</a>
                                                        </label>
                                                        <select class="form-control" wire:model.debounce.300ms="clearing_agent_id.0">
                                                            <option value="">Select Agent</option>
                                                            @if (!is_null($selectedBorder))
                                                                @foreach ($clearing_agents as $clearing_agent)
                                                                    <option value="{{ $clearing_agent->id }}">{{ $clearing_agent->name }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                        @error('clearing_agent_id.0') <span class="text-danger error">{{ $message }}</span> @enderror
                                                        <small>
                                                            <a href="{{ route('clearing_agents.index') }}" target="_blank">
                                                                <i class="fa fa-plus-square-o"></i> New Clearing Agent
                                                            </a>
                                                        </small>
                                                        <a href="#" wire:click.prevent="refresh('clearing_agents')" class="float-end">
                                                            <i class="fa fa-refresh"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                
                                            <!-- Dynamic Borders -->
                                            @foreach ($border_inputs as $key => $value)
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <select class="form-control" wire:model.debounce.300ms="selectedBorder.{{ $value }}">
                                                            <option value="">Select Border</option>
                                                            @if (!is_null($selectedTripType))
                                                                @foreach ($borders as $border)
                                                                    <option value="{{ $border->id }}">{{ $border->name }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                        @error('selectedBorder.' . $value) <span class="text-danger error">{{ $message }}</span> @enderror
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-control" wire:model.debounce.300ms="clearing_agent_id.{{ $value }}">
                                                            <option value="">Select Agent</option>
                                                            @if (!is_null($selectedBorder))
                                                                @foreach ($clearing_agents as $clearing_agent)
                                                                    <option value="{{ $clearing_agent->id }}">{{ $clearing_agent->name }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                        @error('clearing_agent_id.' . $value) <span class="text-danger error">{{ $message }}</span> @enderror
                                                    </div>
                                                    <div class="col-md-1">
                                                        <button class="btn btn-danger btn-rounded btn-sm" wire:click.prevent="borderRemove({{ $key }})">
                                                            <i class="fa fa-times"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                
                                            <!-- Add Border Button -->
                                            <div class="form-group text-end">
                                                <button class="btn btn-success btn-rounded btn-sm" wire:click.prevent="borderAdd({{ $b }})">
                                                    <i class="fa fa-plus"></i> Border
                                                </button>
                                            </div>
                                        </div>
                                
                                        <!-- CD Numbers -->
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="cd3_number">CD3 Number</label>
                                                        <input type="text" class="form-control" wire:model.debounce.300ms="cd3_number" placeholder="Enter CD3 Number">
                                                        @error('cd3_number') <span class="text-danger error">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                
                                                <div class="col-md-6">
                                                    @if ($trip_type_name == 'Outward')
                                                        <div class="form-group">
                                                            <label for="cd1_number">CD1 Number</label>
                                                            <input type="text" class="form-control" wire:model.debounce.300ms="cd1_number" placeholder="Enter CD1 Number">
                                                            @error('cd1_number') <span class="text-danger error">{{ $message }}</span> @enderror
                                                        </div>
                                                    @else
                                                        <div class="form-group">
                                                            <label for="manifest_number">Manifest Number</label>
                                                            <input type="text" class="form-control" wire:model.debounce.300ms="manifest_number" placeholder="Enter Manifest Number">
                                                            @error('manifest_number') <span class="text-danger error">{{ $message }}</span> @enderror
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="customer">Trip Status<span class="required" style="color: red">*</span></label>
                                          <select class="form-control" wire:model.debounce.300ms="selectedStatus" required>
                                              <option value="">Select Status</option>
                                                  <option value="Scheduled">Scheduled</option>
                                                  <option value="Started">Started</option>
                                                  <option value="Loading Point">Loading Point</option>
                                                  <option value="Loaded">Loaded</option>
                                                  <option value="InTransit">InTransit</option>
                                                  <option value="Offloading Point">Offloading Point</option>
                                                  <option value="Offloaded">Offloaded</option>
                                                  <option value="OnHold">OnHold</option>
                                                  <option value="Cancelled">Cancelled</option>
                                          </select>
                                            @error('selectedStatus') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="exampleInputEmail13"><a href="{{ route('transporters.index') }}" target="_blank" style="color: blue">Transporter(s)</a></label>
                                       <select wire:model.debounce.300ms="selectedTransporter" class="form-control" >
                                           <option value="">Select Transporter</option>
                                           @foreach ($transporters as $transporter)
                                           <option value="{{$transporter->id}}">{{$transporter->name}}</option>
                                           @endforeach
                                       </select>
                                            @error('selectedTransporter') <span class="text-danger error">{{ $message }}</span>@enderror
                                            <small>  <a href="{{ route('transporters.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Transporter</a></small> <a href="#" wire:click.prevent="refresh('transporters')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="customer"><a href="{{ route('brokers.index') }}" target="_blank" style="color: blue">Broker(s)</a></label>
                                          <select class="form-control" wire:model.debounce.300ms="selectedBroker">
                                              <option value="">Select Broker</option>
                                              @foreach ($brokers as $broker)
                                                  <option value="{{$broker->id}}">{{$broker->name}}</option>
                                              @endforeach
                                          </select>
                                            @error('selectedBroker') <span class="text-danger error">{{ $message }}</span>@enderror
                                            <small>  <a href="{{ route('brokers.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Broker</a></small> <a href="#" wire:click.prevent="refresh('brokers')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                               
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        @if (!isset($mode_of_transport))
                                            <div class="form-group" >
                                                <label for="name">Mode of Transport?</label>
                                                <label class="radio-inline">
                                                    <input type="radio" wire:model.debounce.300ms="mode_of_transport" value="Horse" name="optradio" >Horse
                                                </label>
                                                <label class="radio-inline">
                                                    <input type="radio" wire:model.debounce.300ms="mode_of_transport" value="Vehicle" name="optradio">Vehicle
                                                </label>
                                            </div>
                                        @endif
                                        @if (isset($mode_of_transport) && $mode_of_transport == "Horse")
                                        <div class="form-group">
                                            <label for="horse"><a href="{{ route('horses.index') }}" target="_blank" style="color: blue">Horse(s)</a><span class="required" style="color: red">*</span></label>
                                            <input type="checkbox" wire:model.debounce.300ms="all_horses"   class="line-style" />
                                            <label for="one" class="radio-label">Select from all horses</label>
                                            <input type="text" wire:model.lazy="searchHorse" placeholder="Search with reg..." class="form-control">
                                            <select class="form-control" wire:model.debounce.300ms="selectedHorse"  required size="4">
                                                <option value="">Select Horse </option>
                                              @if (!is_null($selectedTransporter) || !is_null($selectedBroker))
                                              @foreach ($horses as $horse)
                                              <option value="{{$horse->id}}"> {{$horse->registration_number}} {{$horse->fleet_number ? "(".$horse->fleet_number.")" : ""}} {{$horse->horse_make ? $horse->horse_make->name : ""}} {{$horse->horse_model ? $horse->horse_model->name : ""}}</option>
                                                @endforeach
                                              @endif

                                          </select>
                                            @error('selectedHorse') <span class="text-danger error">{{ $message }}</span>@enderror
                                            <small>  <a href="{{ route('horses.create') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Horse</a></small> <a href="#" wire:click.prevent="refresh('horses')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        </div>
                                        @elseif (isset($mode_of_transport) && $mode_of_transport == "Vehicle")
                                        
                                        <div class="form-group">
                                            <label for="horse"><a href="{{ route('vehicles.index') }}" target="_blank" style="color: blue">Vehicle(s)</a><span class="required" style="color: red">*</span></label>
                                            <input type="checkbox" wire:model.debounce.300ms="all_vehicles"   class="line-style" />
                                            <label for="one" class="radio-label">Select from all vehicles</label>
                                            <input type="text" wire:model.lazy="searchVehicle" placeholder="Search with reg..." class="form-control">
                                            <select class="form-control" wire:model.debounce.300ms="selectedVehicle"  required size="4">
                                                <option value="">Select Vehicle </option>
                                                @if (!is_null($selectedTransporter) || !is_null($selectedBroker))
                                              @foreach ($vehicles as $vehicle)
                                              <option value="{{$vehicle->id}}"> {{$vehicle->registration_number}} {{$vehicle->vehicle_make ? $vehicle->vehicle_make->name : ""}} {{$vehicle->vehicle_model ? $vehicle->vehicle_model->name : ""}}</option>
                                                @endforeach
                                              @endif

                                          </select>
                                            @error('selectedVehicle') <span class="text-danger error">{{ $message }}</span>@enderror
                                            <small>  <a href="{{ route('vehicles.create') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Vehicle</a></small> <a href="#" wire:click.prevent="refresh('vehicles')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        </div>
                                        @endif
                                    </div>
                                    <div class="col-md-3">
                                        @if (!isset($with_trailer))
                                            <div class="row">
                                                <div class="mb-10">
                                                    <input type="checkbox" wire:model.debounce.300ms="with_trailer"   class="line-style" />
                                                    <label for="one" class="radio-label">With Trailer</label>
                                                    @error('with_trailer') <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                        @endif
                                       
                                        @if ($with_trailer == True)
                                        <div class="row">  
                                            <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="trailers"><a href="{{ route('trailers.index') }}" target="_blank" style="color: blue">Trailer(s)</a><span class="required" style="color: red">*</span></label>
                                                <input type="checkbox" wire:model.debounce.300ms="all_trailers"   class="line-style" />
                                                <label for="one" class="radio-label">Select from all trailers</label>
                                                    <input type="text" wire:model.lazy="searchTrailer" placeholder="Search with reg..." class="form-control">
                                                    <select class="form-control" wire:model.debounce.300ms="trailer_id.0" size="4" required>
                                                      <option value="">Select Trailer </option>
                                                      @if (!is_null($selectedTransporter) || !is_null($selectedBroker))
                                                        @foreach ($trailers as $trailer)
                                                            <option value="{{$trailer->id}}">{{$trailer->registration_number}} {{$trailer->make}} {{$trailer->model}} </option>
                                                        @endforeach
                                                        @endif
                                                    </select>
                                                    <small> <a href="{{ route('trailers.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Trailer</a></small> <a href="#" wire:click.prevent="refresh('trailers')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                                    @error('trailer_id.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                        </div>
                                        </div>
                                        {{-- <br> --}}
                                      
                                            @foreach ($trailer_inputs as $key => $value)
                                            <div class="row">
                                                <div class="col-md-10">
                                                    <select class="form-control" wire:model.debounce.300ms="trailer_id.{{ $value }}" size="4" required>
                                                        <option value="">Select Trailer {{ $value }}</option>
                                                        @if (!is_null($selectedTransporter) || !is_null($selectedBroker))
                                                          @foreach ($trailers as $trailer)
                                                              <option value="{{$trailer->id}}">{{$trailer->registration_number}} {{$trailer->make}} {{$trailer->model}} </option>
                                                          @endforeach
                                                          @endif
                                                      </select>
                                                    @error('trailer_id.'. $value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                                <div class="col-md-1">
                                                    <div class="form-group">
                                                        <label for=""></label>
                                                        <button class="btn btn-danger btn-rounded btn-sm" style="margin-left:-25px;"   wire:click.prevent="trailerRemove({{$key}})"> <i class="fa fa-times"></i></button>
                                                    </div>
                                                </div>
                                           
                                            </div>
                                            <br>
                                            @endforeach
                                      
                                       
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <button class="btn btn-success btn-rounded btn-xs" style="float: right" wire:click.prevent="trailerAdd({{$t}})"> <i class="fa fa-plus"></i>Trailer</button>
                                                </div>
                                            </div>
                                        </div>
                                        <br>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row">
                                            <div class="form-group">
                                                <label for="driver"><a href="{{ route('drivers.index') }}" target="_blank" style="color: blue">Driver(s)</a><span class="required" style="color: red">*</span></label> 
                                                <input type="checkbox" wire:model.debounce.300ms="all_drivers"   class="line-style" />
                                                <label for="one" class="radio-label">Select from all drivers</label>
                                                <input type="text" wire:model.lazy="searchDriver" placeholder="Search with name..." class="form-control" >
                                                <select class="form-control" wire:model.debounce.300ms="driver_id" required size="4">
                                                    <option value="">Select Driver</option>
                                                    @if (!is_null($selectedTransporter) || !is_null($selectedBroker))
                                                        @foreach ($drivers as $driver)
                                                        @if (isset($driver->employee))
                                                        <option value="{{$driver->id}}">{{$driver->employee->name}} {{$driver->employee->surname}}</option>
                                                        @endif
                                                        @endforeach
                                                    @endif
                                                </select>
                                                @error('driver_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                                <small>  <a href="{{ route('drivers.create') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Driver</a></small> <a href="#" wire:click.prevent="refresh('drivers')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                            </div>
                                            <div class="form-group">
                                                <label for="notes">Driver Notes</label>
                                              <input type="text" wire:model.debounce.300ms="notes"  class="form-control" placeholder="Driver Notes / Instructions">
                                                @error('notes') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                            @foreach ($allowance_inputs as $key => $value)
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label for="driver">Allowance(s)</label>
                                                    <select class="form-control" wire:model.debounce.300ms="allowance_id.{{ $value }}">
                                                        <option value="">Select Allowance</option>
                                                            @foreach ($allowances as $allowance)
                                                            <option value="{{$allowance->id}}">{{$allowance->name}}</option>
                                                            @endforeach
                                                    </select>
                                                        @error('allowance_id.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                        <small>  <a href="#" data-toggle="modal" data-target="#allowanceModal" ><i class="fa fa-plus-square-o"></i> Allowance</a></small> <a href="#" wire:click.prevent="refresh('allowances')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label for="driver">Category</label>
                                                    <select class="form-control" wire:model.debounce.300ms="allowance_category.{{ $value }}">
                                                        <option value="">Select Category</option>
                                                        <option value="Customer">Customer</option>
                                                        <option value="Self">Self</option>
                                                        <option value="Transporter">Transporter</option>
                                                    </select>
                                                        @error('allowance_category.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label for="currency">Currency </label>
                                                        <select class="form-control" wire:model.debounce.300ms="selectedAllowanceCurrency.{{ $value }}" >
                                                            <option value="">Select Ccy</option>
                                                                @foreach ($currencies as $currency)
                                                                <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                                                @endforeach
                                                        </select>
                                                        @error('selectedAllowanceCurrency.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label for="">Amt</label>
                                                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="allowance_amount.{{ $value }}"  />
                                                        @error('allowance_amount.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-1">
                                                    <div class="form-group" style="margin-top:7px;">
                                                        <button class="btn btn-danger btn-rounded btn-xs" style="margin-left:-25px; margin-top:26px;"   wire:click.prevent="removeAllowance({{$key}})"> <i class="fa fa-times" ></i></button>
                                                    </div>
                                                </div>
                                                
                                               
                                            </div>
                                            @if (isset($selectedAllowanceCurrency[$value]))
                                            @if ($selectedAllowanceCurrency[$value] != $company->currency_id)
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="customer">Conversion Rate</label>
                                                            <input type="number" step="any" min="0"  class="form-control" wire:model.debounce.300ms="allowance_exchange_rate.{{$value}}" wire:key="{{ $key }}"  placeholder="Conversion Rate" >
                                                            <small style="color: green">The conversion is from the selected currency to {{$company->currency ? $company->currency->name : ""}}</small>
                                                            @error('allowance_exchange_rate.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                        </div> 
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="customer">Amount in {{ $company->currency ? $company->currency->name : "" }}</label>
                                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="allowance_exchange_amount.{{$value}}" wire:key="{{ $key }}" placeholder="Converted Amount" disabled>
                                                            @error('allowance_exchange_amount.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                        </div> 
                                                    </div>
                                            
                                                </div>
                                            @endif
                                        @endif 
                                           
                                            @endforeach
                                    </div>
                                     
                                            
                                       
                                       
                                            <div class="row">
                                                    <div class="form-group">
                                                        <button class="btn btn-success btn-rounded btn-xs" style="float: right" wire:click.prevent="addAllowance({{$x}})"> <i class="fa fa-plus"></i>Add Allowance</button>
                                                    </div>
                                            </div>
                                       
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="customer"><a href="{{ route('customers.index') }}" target="_blank" style="color: blue">Customer(s)</a><span class="required" style="color: red">*</span></label>
                                          <select class="form-control" wire:model.debounce.300ms="customer_id" required>
                                              <option value="">Select Customer</option>
                                              @foreach ($customers as $customer)
                                                  <option value="{{$customer->id}}">{{$customer->name}}</option>
                                              @endforeach
                                          </select>
                                            @error('customer_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                            <small><a href="{{ route('customers.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Customer</a></small> <a href="#" wire:click.prevent="refresh('customers')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="name"><a href="{{route('consignees.index')}}" style="color: blue" target="_blank">Consignees</a></label>
                                            <select class="form-control" wire:model.debounce.300ms="consignee_id">
                                                <option value="">Select Consignee</option>
                                                @foreach ($consignees as $consignee)
                                                    <option value="{{$consignee->id}}">{{$consignee->name}}</option>
                                                @endforeach
                                            </select>
                                            <small>  <a href="{{ route('consignees.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Consignee</a></small> <a href="#" wire:click.prevent="refresh('consignees')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                            @error('consignee_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="row">
                                            <div class="form-group">
                                                <label for="exampleInputEmail13"><a href="{{ route('agents.index') }}" target="_blank" style="color: blue">Agent(s)</a></label>
                                           <select wire:model.debounce.300ms="agent_id" class="form-control" >
                                               <option value="">Select Agent</option>
                                               @foreach ($agents as $agent)
                                                   <option value="{{$agent->id}}">{{$agent->name}} {{$agent->surname}} {{$agent->idnumber}}</option>
                                               @endforeach
                                           </select>
                                                @error('agent_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                                <small>  <a href="{{ route('agents.index') }}" target="_blank" ><i class="fa fa-plus-square-o"></i> New Agent</a></small> <a href="#" wire:click.prevent="refresh('agents')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                            </div>
                                        </div>
                                        @if ($agent_id != "")
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="">Commission</label>
                                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="commission" placeholder="%"  />
                                                    @error('commission') <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="">Amount</label>
                                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="commission_amount" placeholder="$" />
                                                    @error('commission_amount') <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                        </div>
                                        @endif    
                                    </div>
                                </div>

                                <h5 class="underline mt-30">Empty Runs /  Dead Head </h5>
                                <div class="mb-10">
                                   <input type="checkbox" wire:model.debounce.300ms="emptyrun_origin"   class="line-style" />
                                   <label for="one" class="radio-label">Empty Run - Origin</label>
                                   @error('emptyrun_origin') <span class="text-danger error">{{ $message }}</span>@enderror
                               </div>
                               @if (isset($emptyrun_origin) &&  $emptyrun_origin == True)
                               <small style="color: red">Empty Run - Origin  is the distance from your starting point to your loading point to load.</small>
                               <br>
                               <br>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="">Starting Mileage</label>
                                            <input type="number" step="any" class="form-control" wire:model.debounce.300ms="emptyrun_origin_starting_mileage"  />
                                            @error('emptyrun_origin_starting_mileage') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="">Ending Mileage</label>
                                            <input type="number" step="any" class="form-control" wire:model.debounce.300ms="emptyrun_origin_ending_mileage"  />
                                            @error('emptyrun_origin_ending_mileage') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="">Distance<span class="required" style="color: red">*</span></label>
                                            <input type="number" step="any" class="form-control" wire:model.debounce.300ms="emptyrun_origin_distance"  required/>
                                            @error('emptyrun_origin_distance') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="">Fuel Quantity</label>
                                            <input type="number" step="any" class="form-control" wire:model.debounce.300ms="emptyrun_origin_fuel_quantity"  />
                                            @error('emptyrun_origin_fuel_quantity') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="customer"><a href="{{ route('currencies.index') }}" target="_blank" style="color: blue">Currencies</a></label>
                                          <select class="form-control" wire:model.debounce.300ms="emptyrun_origin_currency_id"  >
                                              <option value="">Select Currency</option>
                                              @foreach ($currencies as $currency)
                                                    <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                              @endforeach
                                          </select>
                                            @error('emptyrun_origin_currency_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                            <small>  <a href="{{ route('currencies.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Currency</a></small> <a href="#" wire:click.prevent="refresh('currencies')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="">Fuel Amount</label>
                                            <input type="number" step="any" class="form-control" wire:model.debounce.300ms="emptyrun_origin_fuel_amount"  />
                                            @error('emptyrun_origin_fuel_amount') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                </div>
                               @endif
                              
                                <div class="mb-10">
                                   <input type="checkbox" wire:model.debounce.300ms="emptyrun_destination"   class="line-style" />
                                   <label for="one" class="radio-label">Empty Run - Destination</label>
                                   @error('emptyrun_destination') <span class="text-danger error">{{ $message }}</span>@enderror
                               </div>
                               @if (isset($emptyrun_destination) && $emptyrun_destination == True)
                               <small style="color: red">Empty Run - Destination is the distance from your offloading point to your  next load or back to the starting point.</small>
                               <br>
                               <br>
                               <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Starting Mileage</label>
                                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="emptyrun_destination_starting_mileage"  />
                                        @error('emptyrun_destination_starting_mileage') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Ending Mileage</label>
                                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="emptyrun_destination_ending_mileage"  />
                                        @error('emptyrun_destination_ending_mileage') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Distance<span class="required" style="color: red">*</span></label>
                                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="emptyrun_destination_distance"  required/>
                                        @error('emptyrun_destination_distance') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Fuel Quantity</label>
                                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="emptyrun_destination_fuel_quantity"  />
                                        @error('emptyrun_destination_fuel_quantity') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="customer"><a href="{{ route('currencies.index') }}" target="_blank" style="color: blue">Currencies</a></label>
                                      <select class="form-control" wire:model.debounce.300ms="emptyrun_destination_currency_id"  >
                                          <option value="">Select Currency</option>
                                          @foreach ($currencies as $currency)
                                                <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                          @endforeach
                                      </select>
                                        @error('emptyrun_destination_currency_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                        <small>  <a href="{{ route('currencies.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Currency</a></small> <a href="#" wire:click.prevent="refresh('currencies')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Fuel Amount</label>
                                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="emptyrun_destination_fuel_amount"  />
                                        @error('emptyrun_destination_fuel_amount') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </div>
                               @endif
          
                               <h5 class="underline mt-30">Location Details</h5>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="customer"><a href="{{ route('destinations.index') }}" target="_blank" style="color: blue">From</a><span class="required" style="color: red">*</span></label>
                                          <select class="form-control" wire:model.debounce.300ms="selectedFrom" required>
                                              <option value="">Select From Location</option>
                                              @foreach ($destinations as $destination)
                                                  <option value="{{$destination->id}}">{{$destination->country ? $destination->country->name : ""}} {{$destination->city}}</option>
                                              @endforeach
                                          </select>
                                            @error('selectedFrom') <span class="text-danger error">{{ $message }}</span>@enderror
                                            <small>  <a href="{{ route('destinations.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Destination</a></small> <a href="#" wire:click.prevent="refresh('destinations')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="customer"><a href="{{ route('loading_points.index') }}" target="_blank" style="color: blue">Loading Point(s)</a></label>
                                              <select class="form-control" wire:model.debounce.300ms="loading_point_id" >
                                              <option value="">Select Loading Point</option>
                                              @foreach ($loading_points as $loading_point)
                                              <option value="{{$loading_point->id}}">{{ucfirst($loading_point->name)}}</option>
                                              @endforeach
                                          </select>
                                            @error('loading_point_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                            <small>  <a href="{{ route('loading_points.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Loading Point</a></small> <a href="#" wire:click.prevent="refresh('loading_points')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="destination"><a href="{{ route('destinations.index') }}" target="_blank" style="color: blue">To</a><span class="required" style="color: red">*</span></label>
                                          <select class="form-control" wire:model.debounce.300ms="selectedTo" required>
                                              <option value="">Select To Location</option>
                                              @foreach ($destinations as $destination)
                                                  <option value="{{$destination->id}}">{{$destination->country ? $destination->country->name : ""}} {{ucfirst($destination->city)}}</option>
                                              @endforeach
                                          </select>
                                            @error('selectedTo') <span class="text-danger error">{{ $message }}</span>@enderror
                                            <small>  <a href="{{ route('destinations.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Destination</a></small> <a href="#" wire:click.prevent="refresh('destinations')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="destination"><a href="{{ route('offloading_points.index') }}" target="_blank" style="color: blue">Offloading Point(s)</a></label>
                                          <select class="form-control" wire:model.debounce.300ms="offloading_point_id" >
                                              <option value="">Select Offloading Point</option>
                                              @foreach ($offloading_points as $offloading_point)
                                                  <option value="{{$offloading_point->id}}">{{ucfirst($offloading_point->name)}}</option>
                                              @endforeach
                                          </select>
                                            @error('offloading_point_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                            <small>  <a href="{{ route('offloading_points.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Offloading Points</a></small> <a href="#" wire:click.prevent="refresh('offloading_points')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="route"><a href="{{ route('routes.index') }}" target="_blank" style="color: blue">Route(s)</a></label>
                                          <select class="form-control" wire:model.debounce.300ms="selectedRoute" >
                                              <option value="">Select Route</option>
                                              @foreach ($routes as $route)
                                                  <option value="{{$route->id}}">{{ucfirst($route->name)}}</option>
                                              @endforeach
                                          </select>
                                            @error('selectedRoute') <span class="text-danger error">{{ $message }}</span>@enderror
                                            <small>  <a href="{{ route('routes.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Route</a></small> <a href="#" wire:click.prevent="refresh('routes')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="row">  
                                            <div class="form-group">
                                                <label for="truck_stops"><a href="{{ route('truck_stops.index') }}" target="_blank" style="color: blue">Truck Stop(s)</a></label>
                                                <div class="col-md-12">
                                                    <select wire:model.debounce.300ms="truck_stop_id.0" class="form-control">
                                                        <option value="">Select Truck Stop</option>
                                                        @if (!is_null($selectedRoute))
                                                        @foreach ($truck_stops as $truck_stop)
                                                            <option value="{{ $truck_stop->id }}">{{ $truck_stop->name }}</option>
                                                        @endforeach
                                                        @endif
                                                    </select>
                                                    @error('truck_stop_id.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                                    <small>  <a href="{{ route('truck_stops.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Truck Stop</a></small> <a href="#" wire:click.prevent="refresh('truck_stops')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            @foreach ($inputs as $key => $value)
                                            
                                                <div class="col-md-9">
                                                    <select wire:model.debounce.300ms="truck_stop_id.{{ $value }}" class="form-control">
                                                        <option value="">Select Truck Stop </option>
                                                       @if (!is_null($selectedRoute))
                                                            @foreach ($truck_stops as $truck_stop)
                                                                 <option value="{{ $truck_stop->id }}">{{ $truck_stop->name }}</option>
                                                            @endforeach
                                                       @endif
                                                       
                                                    </select>
                                                    @error('truck_stop_id.'. $value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                                <div class="col-md-1">
                                                    <div class="form-group">
                                                        <label for=""></label>
                                                        <button class="btn btn-danger btn-rounded btn-xs" style="marging-left:-25px"   wire:click.prevent="remove({{$key}})"> <i class="fa fa-times"></i></button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                       
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <button class="btn btn-success btn-rounded btn-xs" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i>Truck Stop</button>
                                                </div>
                                            </div>
                                        </div>
                                        <br>
                                        </div>
                                  
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="start_date">Trip Start Date<span class="required" style="color: red">*</span></label>
                                            <input type="datetime-local" class="form-control" wire:model.debounce.300ms="start_date" placeholder="Enter Start Date" required>
                                            @error('start_date') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="end_date">Estimated Trip End Date</label>
                                            <input type="datetime-local" class="form-control" wire:model.debounce.300ms="end_date" placeholder="Enter End Date" >
                                            @error('end_date') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                    
                                </div>
                
                                <div class="row">
                            
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="end_date">Starting Mileage</label>
                                            <input type="number" step="any" min="1" class="form-control" wire:model.debounce.300ms="starting_mileage" placeholder="Mileage @ Loading Point" >
                                            @error('starting_mileage') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        @if (isset($starting_mileage))
                                        <div class="form-group">
                                            <label for="end_date">Ending Mileage</label>
                                            <input type="number" step="any" min="{{$starting_mileage}}" class="form-control" wire:model.debounce.300ms="ending_mileage" placeholder="Mileage @ Offloading Point" >
                                            @error('ending_mileage') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                        @else
                                        <div class="form-group">
                                            <label for="end_date">Ending Mileage</label>
                                            <input type="number" step="any" class="form-control" wire:model.debounce.300ms="ending_mileage" placeholder="Mileage @ Offloading Point" disabled >
                                            @error('ending_mileage') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                        @endif
                                       
                                    </div>

                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="distance">Distance</label>
                                            <input type="number" min="1" step="any" class="form-control" wire:model.debounce.300ms="distance" placeholder="Trip Approx Distance"  >
                                            @error('distance') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="end_date">Fuel Qty</label>
                                            <input type="number" step="any" class="form-control" wire:model.debounce.300ms="trip_fuel" placeholder="Trip Approx Fuel">
                                            @error('trip_fuel') <span class="text-danger error">{{ $message }}</span>@enderror
                                            @if ($horse_selected)
                                                <small> <a href="{{ route('horses.show',$horse_selected->id) }}" target="_blank" style="color: blue">Horse {{ $horse_selected->registration_number }}</a> Fuel Tank Balance: {{ $fuel_balance }} Litres</small>
                                            @endif
                                            <br>
                                            @if (isset($fuel_balance) && isset($trip_fuel))
                                                @if ($trip_fuel > $fuel_balance)
                                                <small style="color: red">Order fuel for horse. Estimated trip fuel is greater than available fuel in horse fuel tank.</small>
                                                @endif
                                            @endif
                                           
                                          
                                        </div>
                                    </div>
                                    <!-- /.col-md-6 -->
                                </div>

                                <h5 class="underline mt-30">Cargo Details</h5>
                                <div class="mb-10">
                                   <input type="checkbox" wire:model.debounce.300ms="with_cargos"   class="line-style" />
                                   <label for="one" class="radio-label">With Cargo</label>
                                   @error('with_cargos') <span class="text-danger error">{{ $message }}</span>@enderror
                               </div>
                               @if ($with_cargos == True)
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="customer"><a href="{{ route('cargos.index') }}" target="_blank" style="color: blue">Cargo(s)</a><span class="required" style="color: red">*</span></label>
                                          <select class="form-control" wire:model.debounce.300ms="selectedCargo" required>
                                              <option value="">Select Cargo</option>
                                              @foreach ($cargos as $cargo)
                                                  <option value="{{$cargo->id}}">{{$cargo->name}}</option>
                                              @endforeach
                                          </select>
                                            @error('selectedCargo') <span class="text-danger error">{{ $message }}</span>@enderror
                                            <small>  <a href="{{ route('cargos.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Cargo</a></small> <a href="#" wire:click.prevent="refresh('cargos')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="weight">Additional Info</label>
                                            <input type="text"  class="form-control" wire:model.debounce.300ms="cargo_details" placeholder="Additional Cargo Details">
                                            @error('cargo_details') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="weight">Weight/Tonnage</label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="weight" placeholder="Measured in Tons" >
                                            @error('weight') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    
                                    </div>
                                  

                                </div>
                               
                          
                                @if (!is_null($selectedCargo))
                             
                                <div class="row">
                                    @if ($cargo_type == "Solid")
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="quantity">Quantity</label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="quantity" placeholder="Enter Cargo Quantity" >
                                            @error('quantity') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="customer"><a href="{{ route('measurements.index') }}" target="_blank" style="color: blue">Measurements</a></label>
                                          <select class="form-control" wire:model.debounce.300ms="measurement" >
                                              <option value="">Select Measurement</option>
                                                  @foreach ($solid_measurements as $measurement)
                                                      <option value="{{ $measurement->name }}">{{ $measurement->name }}</option>
                                                  @endforeach
                                          </select>
                                            @error('measurement') <span class="text-danger error">{{ $message }}</span>@enderror
                                            <small>  <a href="{{ route('measurements.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Measurement</a></small> <a href="#" wire:click.prevent="refresh('measurements')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                    @elseif ($cargo_type == "Liquid")
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="litreage">Litreage @ Ambient</label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="litreage" placeholder="Enter Litreage @ Ambient Temperature" >
                                            @error('litreage') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="litreage">Litreage @ 20 Degrees</label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="litreage_at_20" placeholder="Enter Litreage @ 20 Degrees">
                                            @error('litreage_at_20') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="customer"><a href="{{ route('measurements.index') }}" target="_blank" style="color: blue">Measurements</a></label>
                                          <select class="form-control" wire:model.debounce.300ms="measurement" >
                                              <option value="">Select Measurement</option>
                                              @foreach ($liquid_measurements as $measurement)
                                              <option value="{{ $measurement->name }}">{{ $measurement->name }}</option>
                                          @endforeach
                                          </select>
                                            @error('measurement') <span class="text-danger error">{{ $message }}</span>@enderror
                                            <small>  <a href="{{ route('measurements.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Measurement</a></small> <a href="#" wire:click.prevent="refresh('measurements')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                               
                                @endif
                                @endif
                               
                                @if (
                                    $company->rates_managed_by_finance == 0 || 
                                    ($company->rates_managed_by_finance == 1 && 
                                    (in_array('Finance', $department_names) || in_array('Super Admin', $role_names)))
                                )
                                    <h5 class="underline mt-30">Freight Calculation Method<span class="required" style="color: red">*</span></h5>
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="mb-10">
                                                <input type="radio" wire:model.debounce.300ms="freight_calculation" value="flat_rate"  class="line-style" required />
                                                <label for="one" class="radio-label">Flat Rate</label>
                                                <input type="radio" wire:model.debounce.300ms="freight_calculation" value="rate_weight"  class="line-style" required />
                                                <label for="one" class="radio-label">Rate * Weight/Litreage</label>
                                                <input type="radio" wire:model.debounce.300ms="freight_calculation" value="rate_weight_distance"  class="line-style" required />
                                                <label for="one" class="radio-label">Rate * Distance * Weight/Litreage</label>
                                                <input type="radio" wire:model.debounce.300ms="freight_calculation" value="rate_distance"  class="line-style" required />
                                                <label for="one" class="radio-label">Rate * Distance</label>
                                                @error('freight_calculation') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                            @if (isset($freight_calculation) && isset($cargo_type) )
                                                @if ($freight_calculation == "rate_weight" || $freight_calculation == "rate_weight_distance")
                                                    <div class="mb-10">
                                                        <caption style="color: green">Select what to use to calculate freight<span class="required" style="color: red">*</span>.</caption> <br>
                                                        @if ($cargo_type == "Solid")
                                                        <input type="radio" wire:model.debounce.300ms="calculation_measurement" value="weight"  class="line-style" required />
                                                        <label for="one" class="radio-label">Weight</label>
                                                        @endif
                                                        @if ($cargo_type == "Liquid")
                                                        <input type="radio" wire:model.debounce.300ms="calculation_measurement" value="litreage_at_ambient"  class="line-style" required />
                                                        <label for="one" class="radio-label">Litreage @ Ambient Temp</label>
                                                        <input type="radio" wire:model.debounce.300ms="calculation_measurement" value="litreage_at_20"  class="line-style" required />
                                                        <label for="one" class="radio-label">Litreage @ 20 Degrees</label>  
                                                        @endif
                                                        @error('calculation_measurement') <span class="text-danger error">{{ $message }}</span>@enderror
                                                    </div>
                                                @endif
                                            @endif
                                           
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="customer"><a href="{{ route('currencies.index') }}" target="_blank" style="color: blue">Currencies</a><span class="required" style="color: red">*</span></label>
                                              <select class="form-control" wire:model.debounce.300ms="selectedCurrency" required {{ !isset($company->currency_id) ? "disabled" : ""  }} >
                                                  <option value="">Select Currency</option>
                                                  @foreach ($currencies as $currency)
                                                  <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                                  @endforeach
                                              </select>
                                                @error('selectedCurrency') <span class="text-danger error">{{ $message }}</span>@enderror
                                                @if (!isset($company->currency_id))
                                                <small style="color:red">Default company trading currency not set</small>
                                                <br>
                                                @endif
                                                <small>  <a href="{{ route('currencies.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Currency</a></small> <a href="#" wire:click.prevent="refresh('currencies')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                            </div>
                                            @if (!is_null($selectedCurrency))
                                            @if ($company)
                                                @if ($selectedCurrency != $company->currency_id)
                                                <div class="form-group">
                                                    <label for="customer">Conversion Rate</label>
                                                    <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="exchange_rate"  placeholder="Exchange Rate {{$selected_currency ? "From ".$selected_currency->name : ""}} {{$company->currency ? "To ".$company->currency->name : ""}}" >
                                                    @error('exchange_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                                    <small style="color: green">{{$selected_currency ? " 1 ".$selected_currency->name." is how much in" : ""}} {{$company->currency ? $company->currency->name." ?" : ""}}</small>
                                                    <small>{{$exchange_customer_freight ? "The customer converted amount is: ".$exchange_customer_freight : ""}}</small> <br>
                                                    <small>{{$exchange_transporter_freight ? "The transporter converted amount is: ".$exchange_transporter_freight : ""}}</small>
                                                </div> 
                                                @endif
                                            @endif
                                            @endif
                                        </div>
                                    </div>
                                   
                                    <h5 class="underline mt-10">Customer Freight Agreement</h5>
                                    <div class="form-group" >
                                        <label for="name">Customer Rates</label>
                                        <label class="radio-inline">
                                            <input type="radio" wire:model.debounce.300ms="with_customer_rates" value="rates" name="optradio_customer" >Predefined Rates
                                          </label>
                                          <label class="radio-inline">
                                            <input type="radio" wire:model.debounce.300ms="with_customer_rates" value="custom" name="optradio_customer">Custom Rate
                                          </label>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            @if (!is_null($with_customer_rates))
                                                @if ($with_customer_rates == "rates")
                                                <div class="form-group">
                                                    <label for="customer"><a href="{{ route('rates.index') }}" target="_blank" style="color: blue">Rates</a><span class="required" style="color: red">*</span></label>
                                                  <select class="form-control" wire:model.debounce.300ms="selectedDefinedCustomerRate" required>
                                                      <option value="">Select Rate</option>
                                                      @foreach ($defined_customer_rates as $rate)
                                                          {{ $this->getDestination($rate->from) ? $this->getDestination($rate->from)->country->name : ""}}
                                                          <option value="{{$rate->id}}">{{ $rate->freight_calculation }} {{ $rate->cargo ? $rate->cargo->name : "" }} {{ $rate->weight ? $rate->weight."tons" : ""}} {{ $rate->litreage ? $rate->litreage."litres" : ""}} | {{ $this->getDestination($rate->from)->country ? $this->getDestination($rate->from)->country->name : "" }} {{ $this->getDestination($rate->from)->city}} {{ $rate->loading_point ? $rate->loading_point->name : "" }}  - {{ $this->getDestination($rate->to)->country ? $this->getDestination($rate->to)->country->name : "" }} {{ $this->getDestination($rate->to)->city}} {{$rate->offloading_point ? $rate->offloading_point->name : ""}} {{$rate->distance ? $rate->distance."Kms" : ""}} | {{$rate->currency ? $rate->currency->name : ""}} {{$rate->currency ? $rate->currency->symbol : ""}}{{$rate->rate}}</option>
                                                      @endforeach
                                                  </select>
                                                    @error('selectedDefinedCustomerRate') <span class="text-danger error">{{ $message }}</span>@enderror
                                                  
                                                    @if (in_array('Finance', $department_names) || in_array('Management', $rank_names) || in_array('Super Admin', $role_names))
                                                    <small>  <a href="{{ route('rates.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Rate</a></small> <a href="#" wire:click.prevent="refresh('rates')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                                    @endif
                                                </div>
                                                @elseif($with_customer_rates == "custom")
                                                <div class="form-group">
                                                    <label for="weight">Rate</label>
                                                    <input type="number" step="any" min="0" class="form-control"  wire:model.debounce.300ms="rate" placeholder="Enter Rate" >
                                                    @error('rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                                @endif
                                            @endif
                                           
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="weight">Freight</label>
                                                <input type="number" step="any" min="0" class="form-control"  wire:model.debounce.300ms="freight" placeholder="Enter Freight"  >
                                                @error('freight') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                        </div>
    
                                        <!-- /.col-md-6 -->
                                    </div>
                                   
                                    <h5 class="underline mt-10">Transporter Freight Agreement</h5>
                                    <div class="mb-10">
                                        <input type="checkbox" wire:model.debounce.300ms="transporter_agreement"   class="line-style" />
                                        <label for="one" class="radio-label">Transporter</label>
                                        @error('transporter_agreement') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                    @if ($transporter_agreement == True)
                                    <div class="form-group" >
                                        <label for="name">Transporter Rates</label>
                                        <label class="radio-inline">
                                            <input type="radio" wire:model.debounce.300ms="with_transporter_rates" value="rates" name="optradio_transporter" >Predefined Rates
                                          </label>
                                          <label class="radio-inline">
                                            <input type="radio" wire:model.debounce.300ms="with_transporter_rates" value="custom" name="optradio_transporter">Custom Rate
                                          </label>
                                    </div>
                                    <div class="row">
                                      
                                        <div class="col-md-6">
                                                  @if (!is_null($with_transporter_rates))
                                                @if ($with_transporter_rates == "rates")
                                                <div class="form-group">
                                                    <label for="customer"><a href="{{ route('rates.index') }}" target="_blank" style="color: blue">Rates</a><span class="required" style="color: red">*</span></label>
                                                  <select class="form-control" wire:model.debounce.300ms="selectedDefinedTransporterRate" required>
                                                      <option value="">Select Rate</option>
                                                      @foreach ($defined_transporter_rates as $rate)
                                                        <option value="{{$rate->id}}">{{ $rate->freight_calculation }} {{ $rate->cargo ? $rate->cargo->name : "" }} {{ $rate->weight ? $rate->weight."tons" : ""}} {{ $rate->litreage ? $rate->litreage."litres" : ""}} | {{ $this->getDestination($rate->from)->country ? $this->getDestination($rate->from)->country->name : "" }} {{ $this->getDestination($rate->from)->city}} {{ $rate->loading_point ? $rate->loading_point->name : "" }}  - {{ $this->getDestination($rate->to)->country ? $this->getDestination($rate->to)->country->name : "" }} {{ $this->getDestination($rate->to)->city}} {{$rate->offloading_point ? $rate->offloading_point->name : ""}} {{$rate->distance ? $rate->distance."Kms" : ""}} | {{$rate->currency ? $rate->currency->name : ""}} {{$rate->currency ? $rate->currency->symbol : ""}}{{$rate->rate}}</option>
                                                      @endforeach
                                                  </select>
                                                    @error('selectedDefinedTransporterRate') <span class="text-danger error">{{ $message }}</span>@enderror
                                                    <small>  <a href="{{ route('rates.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Rate</a></small> <a href="#" wire:click.prevent="refresh('rates')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                                </div>
                                                @elseif($with_transporter_rates == "custom")
                                                <div class="form-group">
                                                    <label for="weight">Rate</label>
                                                    <input type="number" step="any" min="0" max="{{ $rate }}"  class="form-control"  wire:model.debounce.300ms="transporter_rate" placeholder="Enter Transporter Rate" >
                                                    @error('transporter_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                                    @if ($transporter_rate > $rate)
                                                    <small style="color: red"> Transporter agreed rate cannot be greater than customer agreed rate.</small>
                                                @endif
                                                </div>
                                                @endif
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="weight">Freight</label>
                                                <input type="number" step="any" min="0"  max="{{ $freight }}" class="form-control"  wire:model.debounce.300ms="transporter_freight" placeholder=" Transporter Freight" />
                                                @error('transporter_freight') <span class="text-danger error">{{ $message }}</span>@enderror
                                                @if ($transporter_freight > $freight)
                                                    <small style="color: red"> Transporter agreed freight cannot be greater than customer agreed freight.</small>
                                                @endif
                                            </div>
                                        </div>
    
                                        <!-- /.col-md-6 -->
                                    </div>
                                   
                                    @endif
                                 
                         
                                @endif
                                <div class="row">
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="customer">Payment Status</label>
                                          <select class="form-control" wire:model.debounce.300ms="payment_status">
                                              <option value="">Select Status</option>
                                                  <option value="Pending">Pending</option>
                                                  <option value="Partial Payment">Partial Payment</option>
                                                  <option value="Half Payment">Half Payment</option>
                                                  <option value="Full Payment">Full Payment</option>
                                                  <option value="No Payment">No Payment</option>
                                          </select>
                                            @error('payment_status') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    

                                    <div class="col-md-7">
                                        <div class="form-group">
                                            <label for="street_address">Comments</label>
                                           <input type="text" wire:model.debounce.300ms="comments" class="form-control" placeholder="Enter Trip Comments">
                                            @error('comments') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    </div>
                                    <hr>

                                    <h5 class="underline mt-30"><a href="{{ route('expenses.index') }}" target="_blank" style="color: blue">Trip Expense(s)</a></h5>
                                    <div class="mb-10">
                                        <input type="checkbox" wire:model.debounce.300ms="trip_expenses"   class="line-style" />
                                        <label for="one" class="radio-label">Trip Expenses</label>
                                        @error('trip_expenses') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                    @if ($trip_expenses == True)
                                    <div style="height: 400px; overflow: auto">
                                        <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                            <caption>  <small>  <a href="{{ route('expenses.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Expense</a></small>  </caption> <a href="#" wire:click.prevent="refresh('expenses')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                            <thead>
                                              <tr>
                                                <th class="th-sm">Expense
                                                </th>
                                                <th class="th-sm">Category
                                                </th>
                                                <th class="th-sm">Currency
                                                </th>
                                                <th class="th-sm">Amount
                                                </th>
                                              </tr>
                                            </thead>
                                            @if ($expenses->count()>0)
                                            <tbody>
                                                @foreach ($expenses as $key => $expense)
                                              <tr>
                                                <td>
                                                    <div class="mb-10">
                                                        <input type="checkbox" wire:model.debounce.300ms="expense_id.{{$key}}"  wire:key="{{ $key }}" value="{{ $expense->id }}" class="line-style" />
                                                        <label for="one" class="radio-label">{{$expense->name}}</label>
                                                        @error('expense_id.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                                    </div>
                                                </td>
                                                <td>
                                                    @if (isset($expense_id[$key]) && ($expense_id[$key] == $expense->id))
                                                    <div class="form-group">
                                                        <select class="form-control" wire:model.debounce.300ms="category.{{$key}}"  wire:key="{{ $key }}" required>
                                                            <option value="">Select Category</option>
                                                           <option value="Customer">Customer</option>
                                                           <option value="Self">Self</option>
                                                           <option value="Transporter">Transporter</option>
                                                        </select>
                                                        @error('category.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                                    </div>
                                                    @else
                                                    <div class="form-group">
                                                        <select class="form-control" wire:model.debounce.300ms="category.{{$key}}"  wire:key="{{ $key }}" >
                                                            <option value="">Select Category</option>
                                                           <option value="Customer">Customer</option>
                                                           <option value="Self">Self</option>
                                                           <option value="Transporter">Transporter</option>
                                                        </select>
                                                        @error('category.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                                    </div>
                                                    @endif
                                                   
                                                </td>
                                                <td>
                                                    @if (isset($expense_id[$key]) && ($expense_id[$key] == $expense->id))
                                                    <div class="form-group">
                                                        <select class="form-control" wire:model.debounce.300ms="expense_currency_id.{{$key}}"  wire:key="{{ $key }}"  {{ !isset($company->currency_id) ? "disabled" : ""  }} required>
                                                            <option value="">Select Currency </option>
                                                            @foreach ($currencies as $currency)
                                                            <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('expense_currency_id.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                                    </div>
                                                    @else
                                                    <div class="form-group">
                                                        <select class="form-control" wire:model.debounce.300ms="expense_currency_id.{{$key}}"  wire:key="{{ $key }}"  {{ !isset($company->currency_id) ? "disabled" : ""  }}>
                                                            <option value="">Select Currency </option>
                                                            @foreach ($currencies as $currency)
                                                            <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('expense_currency_id.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                                    </div>
                                                    @endif
                                                   
                                                    @if (isset($expense_currency_id[$key]))
                                                    @if ($company)
                                                        @if ($expense_currency_id[$key] != $company->currency_id)
                                                        <div class="row">
                                                            @if (isset($expense_id[$key]) && ($expense_id[$key] == $expense->id))
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="customer">Conversion Rate</label>
                                                                    <input type="number" step="any" min="0"  class="form-control" wire:model.debounce.300ms="expense_exchange_rate.{{$key}}" wire:key="{{ $key }}"  placeholder="Conversion Rate" >
                                                                    <small style="color: green">The conversion is from the selected currency to {{$company->currency ? $company->currency->name : ""}}</small>
                                                                    @error('expense_exchange_rate.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                                                </div> 
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="customer">Amount in {{ $company->currency ? $company->currency->name : "" }}</label>
                                                                    <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="expense_exchange_amount.{{$key}}" wire:key="{{ $key }}" placeholder="Converted Amount" disabled>
                                                                    @error('expense_exchange_amount.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                                                </div> 
                                                            </div>
                                                            @else
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="customer">Conversion Rate</label>
                                                                    <input type="number" step="any" min="0" class="form-control"  wire:model.debounce.300ms="expense_exchange_rate.{{$key}}" wire:key="{{ $key }}"  placeholder="Conversion Rate" >
                                                                    <small style="color: green">The conversion is from the selected currency to {{$company->currency ? $company->currency->name : ""}}</small>
                                                                    @error('expense_exchange_rate.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                                                </div> 
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="customer">Amount in{{ $company->currency ? $company->currency->name : "" }}</label>
                                                                    <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="expense_exchange_amount.{{$key}}" wire:key="{{ $key }}" placeholder="Converted Amount" disabled>
                                                                    @error('expense_exchange_amount.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                                                </div> 
                                                            </div>
                                                            @endif
                                                        </div>
                                                        @endif
                                                    @endif
                                                @endif  
                                                </td>
                                                <td> 
                                                    @if (isset($expense_id[$key]) && ($expense_id[$key] == $expense->id))
                                                    <div class="form-group">
                                                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="amount.{{$key}}"  wire:key="{{ $key }}"  placeholder="Enter Expense Amount" required/>
                                                        @error('amount.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                                        </div>
                                                    @else
                                                    <div class="form-group">
                                                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="amount.{{$key}}"  wire:key="{{ $key }}"  placeholder="Enter Expense Amount" />
                                                        @error('amount.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                                        </div>
                                                    @endif
                                                   
                                            </td>
                                              </tr>
                                              @endforeach
                                            </tbody>
                                            @else
                                                <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                                             @endif
                                          </table>
                                        </div>
                                          @endif
                                
                             <hr>

                             <h5 class="underline mt-30">Fuel Order Details</h5>
                             <div class="mb-10">
                                <input type="checkbox" wire:model.debounce.300ms="fuel_order"   class="line-style" />
                                <label for="one" class="radio-label">Fuel Order</label>
                                @error('fuel_order') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                            @if ($fuel_order == True)
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="vendors">Fueling Station<span class="required" style="color: red">*</span></label>
                                       <select wire:model.debounce.300ms="selectedContainer" class="form-control" required>
                                           <option value="">Select Fueling Station</option>
                                          @foreach ($containers as $container)
                                              <option value="{{$container->id}}">{{$container->name}}</option>
                                          @endforeach
                                       </select>
                                        @error('selectedContainer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        <small>  <a href="{{ route('containers.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Fueling Station</a></small> <a href="#" wire:click.prevent="refresh('stations')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        @if (!is_null($selectedContainer) && isset($selected_container) )
                                            @if ($selected_container->purchase_type == "Bulk Buy")
                                                @if (isset($container_balance))
                                                    <br>
                                                    <small style="color:green">Available fuel balance is {{ $container_balance }}Litres</small>    
                                                @endif
                                            @endif 
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="vendors">Categories<span class="required" style="color: red">*</span></label>
                                        <select class="form-control" wire:model.debounce.300ms="fuel_category" required>
                                            <option value="">Select Category</option>
                                           <option value="Customer">Customer</option>
                                           <option value="Self">Self</option>
                                           <option value="Transporter">Transporter</option>
                                        </select>
                                        @error('fuel_category') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="date">Fillup Date</label>
                                        <input type="date" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter FillUp Date"/>
                                        @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="currencies">Currencies</label>
                                        <select class="form-control" wire:model.debounce.300ms="selectedFuelCurrency" {{ !isset($company->currency_id) ? "disabled" : ""  }}>
                                            <option value="">Select Currency </option>
                                            @foreach ($currencies as $currency)
                                            <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                            @endforeach
                                        </select>
                                        @error('selectedFuelCurrency') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                    @if (!is_null($selectedCurrency))
                                    @if ($company)
                                        @if ($selectedFuelCurrency != $company->currency_id)
                                        <div class="form-group">
                                            <label for="customer">Conversion Rate</label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="fuel_exchange_rate"  placeholder="Exchange Rate {{$selected_fuel_currency ? "From ".$selected_fuel_currency->name : ""}} {{$company->currency ? "To ".$company->currency->name : ""}}" >
                                            @error('fuel_exchange_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                            <small style="color: green">{{$selected_fuel_currency ? " 1 ".$selected_fuel_currency->name." is how much in" : ""}} {{$company->currency ? $company->currency->name." ?" : ""}}</small>
                                            <small>{{$fuel_exchange_amount ? "The fuel converted amount is: ".$fuel_exchange_amount : ""}}</small> <br>       
                                        </div> 
                                        @endif
                                    @endif
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="quantity">Quantity</label>
                                        @if (isset($selected_container))
                                            @if ($selected_container->purchase_type == "Bulk Buy")
                                                <input type="number" step="any" min="0"  max="{{$container_balance}}" class="form-control"  wire:model.debounce.300ms="fuel_quantity" placeholder="Enter Fillup Quantity"  />
                                                @if (isset($horse_selected))
                                                @if (isset($fuel_tank_capacity))
                                                    <small style="color: green">{{$horse_selected->registration_number}} Tank Capacity: {{$fuel_tank_capacity}} Litres. </small> <br>
                                                @endif
                                                @if (isset($fuel_balance))
                                                    <small style="color: green">{{$horse_selected->registration_number}} Available Fuel: {{$fuel_balance}} Litres. </small> <br>
                                                @endif 
                                            @endif
                                            @else
                                                <input type="number" step="any" min="0"  class="form-control"  wire:model.debounce.300ms="fuel_quantity" placeholder="Enter Fillup Quantity"  />
                                                @if (isset($horse_selected))
                                                    @if (isset($fuel_tank_capacity))
                                                        <small style="color: green">{{$horse_selected->registration_number}} Tank Capacity: {{$fuel_tank_capacity}} Litres. </small> <br>
                                                    @endif
                                                    @if (isset($fuel_balance))
                                                        <small style="color: green">{{$horse_selected->registration_number}} Available Fuel: {{$fuel_balance}} Litres. </small> <br>
                                                    @endif 
                                                @endif
                                              
                                            @endif

                                            @error('fuel_quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            @if (isset($trip_fuel) && isset($horse_fuel_total))
                                                @if ($trip_fuel > $horse_fuel_total)
                                                    <small style="color: red">Total horse fuel is less than trip fuel.</small> <br>
                                                @endif
                                                @if ($horse_fuel_total > $fuel_tank_capacity)
                                                    <small style="color: red">{{$horse_fuel_total ? $horse_fuel_total." Litres" : ""}} of fuel exceeds horse tank capacity of {{$fuel_tank_capacity ? $fuel_tank_capacity." Litres" : ""}}.</small> <br>
                                                @endif
                                            @endif

                                            @if ($selected_container->purchase_type == "Bulk Buy")
                                                @if (isset($container_balance) && isset($fuel_quantity))
                                                    @if ($container_balance < $fuel_quantity)
                                                    <small style="color: red">Fuel order exceeds {{ $container_balance }} litres, which is the fueling station balance.</small> <br>
                                                    @endif
                                                @endif
                                            @endif

                                        @endif
                                      

                                        @if (isset($fuel_tank_capacity) && $fuel_tank_capacity > 0)
                                            @if ($fuel_tank_capacity < $fuel_quantity)
                                                <small style="color: red">Fuel order exceeds {{ $fuel_tank_capacity }} litres, which is fuel tank capacity.</small>
                                            @endif
                                        @else   
                                            @if ($horse_selected)
                                            <small style="color: green">Horse <a href="{{ route('horses.show',$horse_selected->id) }}" target="_blank" style="color: blue">Horse {{ $horse_selected->registration_number }}</a> fuel tank capacity not set.</small> 
                                            @endif
                                        @endif
                                       
                                    </div>
                                </div>
                               
                            </div>
                            <div class="row">
                      
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="unit_price">Rate</label>
                                        <input type="number" step="any" min="0" class="form-control" step="any" min="0"  wire:model.debounce.300ms="unit_price" placeholder="Enter Pump Price/Litre" />
                                        @error('unit_price') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="amount">Total</label>
                                        <input type="number" step="any" min="0" class="form-control"  wire:model.debounce.300ms="fuel_amount" placeholder="Enter Fuel Total" />
                                        @error('fuel_amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                          
                            </div>
                            @if ($transporter_agreement == True)
                            <div class="row">
                      
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="unit_price">Transporter Rate</label>
                                        <input type="number" step="any" min="0" class="form-control" step="any" min="0"  wire:model.debounce.300ms="transporter_price" placeholder="Enter Transporter Price/Litre" />
                                        @error('transporter_price') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="amount">Transporter Total</label>
                                        <input type="number" step="any" min="0" class="form-control"  wire:model.debounce.300ms="transporter_total" placeholder="Enter Transporter Fuel Total" />
                                        @error('transporter_total') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                          
                            </div>
                            @endif
                            <div class="row">
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="odometer">Horse Mileage<span class="required" style="color: red">*</span></label>
                                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="odometer" required placeholder="Enter Horse Mileage" required/>
                                        @error('odometer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="comments">Comments</label>
                                       <input type="text" wire:model.debounce.300ms="fuel_comments" class="form-control" placeholder="Fuel Order Comments">
                                        @error('fuel_comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                           
                            @endif
                            
                            <hr>

                            <h5 class="underline mt-30">Send trip updates to customer(s)</h5>
                            <div class="mb-10">
                                <input type="checkbox" wire:model.debounce.300ms="customer_updates"   class="line-style" />
                                <label for="one" class="radio-label">Customer notifications</label>
                                @error('customer_updates') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                            <br>
                            <br>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="btn-group pull-right mt-10" >
                                    <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                                    @if (isset($horse_fuel_total) &&  $fuel_tank_capacity)
                                        @if ($horse_fuel_total < $fuel_tank_capacity)
                                            <button type="submit" class="btn bg-success btn-wide btn-rounded"  wire:loading.attr="disabled" 
                                            wire:loading.class="opacity-50 cursor-not-allowed" > <i class="fa fa-save"></i>Create</button>
                                        @endif
                                    @else    
                                    <button type="submit" class="btn bg-success btn-wide btn-rounded"   > <i class="fa fa-save"></i>Create</button>
                                    @endif
                                   
                                       
                                    </div>
                                </div>
                            </div>
                            </form>

                            <!-- /.col-md-12 -->
                        </div>
                    </div>
                </div>
                <!-- /.col-md-6 -->


            </div>

        </div>
        <!-- /.container-fluid -->
    </section>

    

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="allowanceModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> New Driver Allowance<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="storeAllowance()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="comment">Name<span class="required" style="color: red">*</span></label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="allowance_title" placeholder="New Driver Allowance" required>
                        @error('allowance_title') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="comment">Currency</label>
                              <select class="form-control" wire:model.debounce.300ms="allowance_currency_id">
                                <option value="">Select Currency</option>
                                @foreach ($currencies as $currency)
                                <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                @endforeach
                              </select>
                                @error('allowance_currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="comment">Amount</label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="allowance_amount"  >
                                @error('allowance_amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="comment">Description</label>
                      <textarea name="" class="form-control" wire:model.debounce.300ms="allowance_description" placeholder="Allowance explanation..."  cols="30" rows="3"></textarea>
                        @error('allowance_description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>

                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Save</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>


</div>
