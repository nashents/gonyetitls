<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    <div class="panel">
                    
                        <div class="panel-body">

                            <form wire:submit.prevent="store()" class="p-20" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="exampleInputEmail13">Transporters</label>
                                            <select wire:model.debounce.300ms="selectedTransporter" class="form-control"  >
                                                <option value="">Select Transporter</option>
                                                @foreach ($transporters as $transporter)
                                                    <option value="{{$transporter->id}}">{{$transporter->name}}</option>
                                                @endforeach
                                            </select>
                                            @error('selectedTransporter') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        @if ($type == Null)
                                        <div class="mb-10">
                                            <label for="">Select Mode of Transport</label>
                                            <input type="radio" wire:model.debounce.300ms="type" value="Horse"  class="line-style"  />
                                            <label for="one" class="radio-label">Horse</label>
                                            <input type="radio" wire:model.debounce.300ms="type" value="Vehicle"  class="line-style" />
                                            <label for="one" class="radio-label">Vehicle</label>
                                        </div>      
                                        @endif
                                        @if ($type == "Horse")
                                        <div class="form-group">
                                            <label for="exampleInputEmail13">Horses</label>
                                            <input type="text" wire:model.debounce.300ms="searchHorse" placeholder="Search horse..." class="form-control" >
                                            <select wire:model.debounce.300ms="selectedHorse" class="form-control"  size="4">
                                                <option value="">Select Horse </option>
                                                @foreach ($horses as $horse)
                                                    <option value="{{$horse->id}}">{{$horse->registration_number}} {{$horse->horse_make ? $horse->horse_make->name : ""}} {{$horse->horse_model ? $horse->horse_model->name : ""}}</option>
                                                @endforeach
                                            </select>
                                            @error('selectedHorse') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                        @elseif ($type == "Vehicle")
                                        <div class="form-group">
                                            <label for="exampleInputEmail13">Vehicles</label>
                                            <input type="text" wire:model.debounce.300ms="searchVehicle" placeholder="Search vehicles..." class="form-control" >
                                            <select wire:model.debounce.300ms="selectedVehicle" class="form-control"  size="4">
                                                <option value="">Select Horse </option>
                                                @foreach ($vehicles as $vehicle)
                                                    <option value="{{$vehicle->id}}">{{$vehicle->registration_number}} {{$vehicle->vehicle_make ? $vehicle->vehicle_make->name : ""}} {{$vehicle->vehicle_model ? $vehicle->vehicle_model->name : ""}}</option>
                                                @endforeach
                                            </select>
                                            @error('selectedVehicle') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                        @endif
                                    </div>

                                    <div class="col-md-4">
                                        @if ($assigned_to == Null)
                                        <div class="mb-10">
                                            <label for="">Incident By</label>
                                            <input type="radio" wire:model.debounce.300ms="assigned_to" value="Driver"  class="line-style"  />
                                            <label for="one" class="radio-label">Driver</label>
                                            <input type="radio" wire:model.debounce.300ms="assigned_to" value="Employee"  class="line-style" />
                                            <label for="one" class="radio-label">Employee</label>
                                        </div>  
                                        @endif
                                        @if ($assigned_to == "Employee")
                                        <div class="form-group">
                                            <label for="exampleInputEmail13">Employees<span class="required" style="color: red">*</span></label>
                                            <input type="text" wire:model.debounce.300ms="searchEmployee" placeholder="Search employee..." class="form-control" >
                                            <select wire:model.debounce.300ms="employee_id" class="form-control"   size="4" required>
                                                <option value="">Select Employee</option>
                                                @foreach ($employees as $employee)
                                                    <option value="{{$employee->id}}">{{ucfirst($employee->name)}} {{ucfirst($employee->surname)}}</option>
                                                @endforeach
                                            </select>
                                            @error('employee_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                        @elseif ($assigned_to == "Driver")
                                        <div class="form-group">
                                            <label for="exampleInputEmail13">Drivers<span class="required" style="color: red">*</span></label>
                                            <input type="text" wire:model.debounce.300ms="searchDriver" placeholder="Search driver..." class="form-control" >
                                            <select wire:model.debounce.300ms="driver_id" class="form-control"   size="4" required>
                                                <option value="">Select Driver</option>
                                                @foreach ($drivers as $driver)
                                                    <option value="{{$driver->id}}">{{ucfirst($driver->employee ? $driver->employee->name : "")}} {{ucfirst($driver->employee ? $driver->employee->surname : "")}}</option>
                                                @endforeach
                                            </select>
                                            @error('driver_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                        @endif
                                    </div>
                       
                                </div>
                              
                                <div class="row">
                                    
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="exampleInputEmail13">Cargo</label>
                                            <select wire:model.debounce.300ms="selectedCargo" class="form-control"   >
                                                <option value="">Select Cargo</option>
                                                @foreach ($cargos as $cargo)
                                                    <option value="{{$cargo->id}}">{{$cargo->name}}</option>
                                                @endforeach
                                            </select>
                                            @error('selectedCargo') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="weight">Weight(Tons)</label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="weight" placeholder="Enter Cargo Weight" >
                                            @error('weight') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="exampleInputEmail13">Destination</label>
                                            <select wire:model.debounce.300ms="destination_id" class="form-control"   >
                                                <option value="">Select Destination</option>
                                                @foreach ($destinations as $destination)
                                                    <option value="{{$destination->id}}">{{$destination->country ? $destination->country->name : ""}} {{ucfirst($destination->city)}}</option>
                                                @endforeach
                                            </select>
                                            @error('destination_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                </div>
                                @if (!is_null($selectedCargo))
                                @php
                                    $liquid_measurements = App\Models\Measurement::where('cargo_type','Liquid')->get();
                                    $solid_measurements = App\Models\Measurement::where('cargo_type','Solid')->get();
                                @endphp
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
                                          <select class="form-control" wire:model.debounce.300ms="measurement_id" >
                                              <option value="">Select Measurement</option>
                                                  @foreach ($solid_measurements as $measurement)
                                                      <option value="{{ $measurement->id }}">{{ $measurement->name }}</option>
                                                  @endforeach
                                          </select>
                                            @error('measurement_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                            <small>  <a href="{{ route('measurements.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Measurement</a></small> 
                                        </div>
                                    </div>
                                    @elseif ($cargo_type == "Liquid")
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="quantity">Litreage @ Ambient</label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="litreage" placeholder="Enter Litreage @ Ambient Temperature" >
                                            @error('litreage') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="quantity">Litreage @ 20 Degrees</label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="litreage_at_20" placeholder="Enter Litreage @ 20 Degrees" >
                                            @error('litreage_at_20') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="customer"><a href="{{ route('measurements.index') }}" target="_blank" style="color: blue">Measurements</a></label>
                                          <select class="form-control" wire:model.debounce.300ms="measurement_id" >
                                              <option value="">Select Measurement</option>
                                              @foreach ($liquid_measurements as $measurement)
                                              <option value="{{ $measurement->id }}">{{ $measurement->name }}</option>
                                          @endforeach
                                          </select>
                                            @error('measurement_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                            <small>  <a href="{{ route('measurements.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Measurement</a></small> 
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                @endif
                              
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="number">Location of Incident<span class="required" style="color: red">*</span></label>
                                            <input type="text" class="form-control" wire:model.debounce.300ms="location" placeholder="Enter Incident Location" required>
                                            @error('location') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="number">Date of incident<span class="required" style="color: red">*</span></label>
                                            <input type="datetime-local" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter Incident Date" required>
                                            @error('date') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="number">Date of report<span class="required" style="color: red">*</span></label>
                                            <input type="datetime-local" class="form-control" wire:model.debounce.300ms="report_date" placeholder="Enter Report Date" required>
                                            @error('report_date') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <h5 class="underline mt-30">Injury or Illness</h5>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="number">Injured Person Fullname</label>
                                                    <input type="text" class="form-control" wire:model.debounce.300ms="name.0" placeholder="Enter injured fullname" >
                                                    @error('name.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="number">Taken to</label>
                                                    <input type="text" class="form-control" wire:model.debounce.300ms="taken_to.0" placeholder="Enter taken to location" >
                                                    @error('taken_to.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="number">Body Part</label>
                                                    <input type="text" class="form-control" wire:model.debounce.300ms="body_part.0" placeholder="Enter body part" >
                                                    @error('body_part.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="number">Days lost</label>
                                                    <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="days_lost.0" placeholder="Enter days lost" >
                                                    @error('days_lost.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="number">Nature of Injury</label>
                                                    <input type="text" class="form-control" wire:model.debounce.300ms="nature_of_injury.0" placeholder="Enter nature of injury" >
                                                    @error('nature_of_injury.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="number">Object inflicting harm</label>
                                                    <input type="text" class="form-control" wire:model.debounce.300ms="injury_object.0" placeholder="Object, Equipment, Substance inflicting harm" >
                                                    @error('injury_object.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                        </div>
                                        @foreach ($injuries_inputs as $key => $value)
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="number">Injured Person Fullname</label>
                                                    <input type="text" class="form-control" wire:model.debounce.300ms="name.{{ $value }}" placeholder="Enter injured fullname" >
                                                    @error('name.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="number">Taken to</label>
                                                    <input type="text" class="form-control" wire:model.debounce.300ms="taken_to.{{ $value }}" placeholder="Enter taken to location" >
                                                    @error('taken_to.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="number">Body Part</label>
                                                    <input type="text" class="form-control" wire:model.debounce.300ms="body_part.{{ $value }}" placeholder="Enter body part" >
                                                    @error('body_part.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="number">Days lost</label>
                                                    <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="days_lost.{{ $value }}" placeholder="Enter days lost" >
                                                    @error('days_lost.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-5">
                                                <div class="form-group">
                                                    <label for="number">Nature of Injury</label>
                                                    <input type="text" class="form-control" wire:model.debounce.300ms="nature_of_injury.{{ $value }}" placeholder="Enter nature of injury" >
                                                    @error('nature_of_injury.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                            <div class="col-md-5">
                                                <div class="form-group">
                                                    <label for="number">Object inflicting harm</label>
                                                    <input type="text" class="form-control" wire:model.debounce.300ms="injury_object.{{ $value }}" placeholder="Object, Equipment, Substance inflicting harm" >
                                                    @error('injury_object.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <div class="form-group">
                                                    <label for=""></label>
                                                    <button class="btn btn-danger btn-rounded xs" style="margin-top:23px"  wire:click.prevent="injuriesRemove({{$key}})"> <i class="fa fa-times"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                     @endforeach
                                     <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="injuriesAdd({{$k}})"> <i class="fa fa-plus"></i> Injury</button>
                                            </div>
                                        </div>
                                    </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h5 class="underline mt-30">Property Damage</h5>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="number">Property Damage</label>
                                                    <input type="text" class="form-control" wire:model.debounce.300ms="damage.0" placeholder="Enter property damage" >
                                                    @error('damage.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="number">Nature of damage</label>
                                                    <input type="text" class="form-control" wire:model.debounce.300ms="nature_of_damage.0" placeholder="Enter nature of damage" >
                                                    @error('nature_of_damage.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="number">Currencies</label>
                                                   <select  class="form-control" wire:model.debounce.300ms="damage_currency_id.0">
                                                        <option value="">Select Currency</option>
                                                        @foreach ($currencies as $currency)
                                                            <option value="{{ $currency->id }}">{{ $currency->name }}</option>
                                                        @endforeach
                                                   </select>
                                                    @error('damage_currency_id.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="number">Estimated Cost</label>
                                                    <input type="number" min="0" step="any" class="form-control" wire:model.debounce.300ms="estimated_cost.0" placeholder="Enter estimated cost" >
                                                    @error('estimated_cost.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="number">Actual Cost</label>
                                                    <input type="number" min="0" step="any" class="form-control" wire:model.debounce.300ms="actual_cost.0" placeholder="Enter actual" >
                                                    @error('actual_cost.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="number">Object, Equipment, Substance inflicting damage</label>
                                                    <input type="text" class="form-control" wire:model.debounce.300ms="damage_object.0" placeholder="Enter Object, Equipment, Substance inflicting damage" >
                                                    @error('damage_object.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                          
                                        </div>
                                        @foreach ($damages_inputs as $key => $value)
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="number">Property Damage</label>
                                                    <input type="text" class="form-control" wire:model.debounce.300ms="damage.{{ $value }}" placeholder="Enter property damage" >
                                                    @error('damage.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="number">Nature of damage</label>
                                                    <input type="text" class="form-control" wire:model.debounce.300ms="nature_of_damage.{{ $value }}" placeholder="Enter nature of damage" >
                                                    @error('nature_of_damage.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="number">Currencies</label>
                                                   <select  class="form-control" wire:model.debounce.300ms="damage_currency_id.{{ $value }}">
                                                        <option value="">Select Currency</option>
                                                        @foreach ($currencies as $currency)
                                                            <option value="{{ $currency->id }}">{{ $currency->name }}</option>
                                                        @endforeach
                                                   </select>
                                                    @error('damage_currency_id.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="number">Estimated Cost</label>
                                                    <input type="number" min="0" step="any" class="form-control" wire:model.debounce.300ms="estimated_cost.{{ $value }}" placeholder="Enter estimated cost" >
                                                    @error('estimated_cost.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="number">Actual Cost</label>
                                                    <input type="number" min="0" step="any" class="form-control" wire:model.debounce.300ms="actual_cost.{{ $value }}" placeholder="Enter actual" >
                                                    @error('actual_cost.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-10">
                                                <div class="form-group">
                                                    <label for="number">Object, Equipment, Substance inflicting damage</label>
                                                    <input type="text" class="form-control" wire:model.debounce.300ms="damage_object.{{ $value }}" placeholder="Enter Object, Equipment, Substance inflicting damage" >
                                                    @error('damage_object.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <div class="form-group">
                                                    <label for=""></label>
                                                    <button class="btn btn-danger btn-rounded xs" style="margin-top:23px"  wire:click.prevent="damagesRemove({{$key}})"> <i class="fa fa-times"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="damagesAdd({{$d}})"> <i class="fa fa-plus"></i> Damage</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <h5 class="underline mt-30">Other Incidents</h5>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="number">Type</label>
                                                    <input type="text" class="form-control" wire:model.debounce.300ms="other_type.0" placeholder="Enter Type" >
                                                    @error('other_type.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="number">Nature of loss</label>
                                                    <input type="text" class="form-control" wire:model.debounce.300ms="nature_of_loss.0" placeholder="Enter Nature of loss" >
                                                    @error('nature_of_loss.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                         
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="number">Currencies</label>
                                                   <select  class="form-control" wire:model.debounce.300ms="other_currency_id.0">
                                                        <option value="">Select Currency</option>
                                                        @foreach ($currencies as $currency)
                                                            <option value="{{ $currency->id }}">{{ $currency->name }}</option>
                                                        @endforeach
                                                   </select>
                                                    @error('other_currency_id.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="number">Cost</label>
                                                    <input type="number" min="0" step="any" class="form-control" wire:model.debounce.300ms="cost.0" placeholder="Enter cost" >
                                                    @error('cost.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                        </div>
                                   
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="number">Object, Equipment, Substance inflicting damage</label>
                                                    <input type="text" class="form-control" wire:model.debounce.300ms="other_object.0" placeholder="Enter Object, Equipment, Substance inflicting damage" >
                                                    @error('other_object.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                          
                                        </div>
                                        @foreach ($others_inputs as $key => $value)
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="number">Type</label>
                                                    <input type="text" class="form-control" wire:model.debounce.300ms="other_type.{{ $value }}" placeholder="Enter Type" >
                                                    @error('other_type.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="number">Nature of loss</label>
                                                    <input type="text" class="form-control" wire:model.debounce.300ms="nature_of_loss.{{ $value }}" placeholder="Enter Nature of loss" >
                                                    @error('nature_of_loss.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                         
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="number">Currencies</label>
                                                   <select  class="form-control" wire:model.debounce.300ms="other_currency_id.{{ $value }}">
                                                        <option value="">Select Currency</option>
                                                        @foreach ($currencies as $currency)
                                                            <option value="{{ $currency->id }}">{{ $currency->name }}</option>
                                                        @endforeach
                                                   </select>
                                                    @error('other_currency_id.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="number">Cost</label>
                                                    <input type="number" min="0" step="any" class="form-control" wire:model.debounce.300ms="cost.{{ $value }}" placeholder="Enter cost" >
                                                    @error('cost.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                        </div>
                                   
                                        <div class="row">
                                            <div class="col-md-10">
                                                <div class="form-group">
                                                    <label for="number">Object, Equipment, Substance inflicting damage</label>
                                                    <input type="text" class="form-control" wire:model.debounce.300ms="other_object.0" placeholder="Enter Object, Equipment, Substance inflicting damage" >
                                                    @error('other_object.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <div class="form-group">
                                                    <label for=""></label>
                                                    <button class="btn btn-danger btn-rounded xs" style="margin-top:23px"  wire:click.prevent="othersRemove({{$key}})"> <i class="fa fa-times"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="othersAdd({{$p}})"> <i class="fa fa-plus"></i> Other</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                 
                                   
                                </div>

                                <br>

                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="number">Occupation<span class="required" style="color: red">*</span></label>
                                            <input type="text" class="form-control" wire:model.debounce.300ms="occupation" placeholder="Enter Occupation" required>
                                            @error('occupation') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="number">Experience<span class="required" style="color: red">*</span></label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="experience" placeholder="Enter Experience" required>
                                            @error('experience') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="number">Person Controlling Activity at Occurrence<span class="required" style="color: red">*</span></label>
                                            <input type="text" class="form-control" wire:model.debounce.300ms="controling_activity" placeholder="Enter Person Controlling Activity at Occurrence" required>
                                            @error('controling_activity') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="number">Media Coverage<span class="required" style="color: red">*</span></label>
                                            <input type="radio" wire:model.debounce.300ms="media_coverage" value="Yes"  class="line-style"  required/>
                                            <label for="one" class="radio-label">Yes</label>
                                            <input type="radio" wire:model.debounce.300ms="media_coverage" value="No"  class="line-style"  required/>
                                            <label for="one" class="radio-label">No</label>
                                            @error('media_coverage') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-10">
                                            <label for="">Loss Potential<span class="required" style="color: red">*</span></label>
                                            <input type="radio" wire:model.debounce.300ms="loss_potential" value="Major"  class="line-style" required />
                                            <label for="one" class="radio-label">Major</label>
                                            <input type="radio" wire:model.debounce.300ms="loss_potential" value="Serious"  class="line-style"  required/>
                                            <label for="one" class="radio-label">Serious</label>
                                            <input type="radio" wire:model.debounce.300ms="loss_potential" value="Minor"  class="line-style" required/>
                                            <label for="one" class="radio-label">Minor</label>
                                        </div>     
                                    </div>
                                    <div class="col-md-5">
                                        <div class="mb-10">
                                            <label for="">Probability of Occurrence<span class="required" style="color: red">*</span></label>
                                            <input type="radio" wire:model.debounce.300ms="occurance" value="Frequent"  class="line-style"  required/>
                                            <label for="one" class="radio-label">Frequent</label>
                                            <input type="radio" wire:model.debounce.300ms="occurance" value="Occassional"  class="line-style"  required/>
                                            <label for="one" class="radio-label">Occassional</label>
                                            <input type="radio" wire:model.debounce.300ms="occurance" value="Seldom"  class="line-style" required/>
                                            <label for="one" class="radio-label">Seldom</label>
                                        </div>     
                                    </div>
                                   
                                </div>
                                <div class="form-group">
                                    <label for="number">Incident Description<span class="required" style="color: red">*</span></label>
                                   <textarea class="form-control" wire:model.debounce.300ms="description" cols="30" rows="3" placeholder="Describe How the Event Occurred" required></textarea>
                                    @error('description') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                                <div class="row">
                                  
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="number">Immediate Causes</label>
                                            <select class="form-control" wire:model.debounce.300ms="immediate_cause_id.0">
                                                <option value="">Select Immediate Cause</option>
                                                    @foreach ($losses as $loss)
                                                        <option value="{{$loss->id}}">{{$loss->name}}</option>
                                                    @endforeach
                                            </select>
                                            @error('immediate_cause_id.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                        @foreach ($immediate_causes_inputs as $key => $value)
                                        <div class="row">
                                            <div class="col-md-10">
                                                <div class="form-group">
                                                    <label for="number">Immediate Causes</label>
                                                    <select class="form-control" wire:model.debounce.300ms="immediate_cause_id.{{$value}}">
                                                        <option value="">Select Immediate Cause</option>
                                                            @foreach ($losses as $loss)
                                                                <option value="{{$loss->id}}">{{$loss->name}}</option>
                                                            @endforeach
                                                    </select>
                                                    @error('immediate_cause_id.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <div class="form-group">
                                                    <label for=""></label>
                                                    <button class="btn btn-danger btn-rounded xs" style="margin-top:23px"  wire:click.prevent="immediateRemove({{$key}})"> <i class="fa fa-times"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="immediateAdd({{$r}})"> <i class="fa fa-plus"></i> Immediate Cause</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="number">Basic Causes</label>
                                            <select class="form-control" wire:model.debounce.300ms="basic_cause_id.0">
                                                <option value="">Select Basic Cause</option>
                                                    @foreach ($losses as $loss)
                                                        <option value="{{$loss->id}}">{{$loss->name}}</option>
                                                    @endforeach
                                            </select>
                                            @error('basic_cause_id.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                        @foreach ($basic_causes_inputs as $key => $value)
                                        <div class="row">
                                            <div class="col-md-10">
                                                <div class="form-group">
                                                    <label for="number">Basic Causes</label>
                                                    <select class="form-control" wire:model.debounce.300ms="basic_cause_id.{{$value}}">
                                                        <option value="">Select Basic Cause</option>
                                                            @foreach ($losses as $loss)
                                                                <option value="{{$loss->id}}">{{$loss->name}}</option>
                                                            @endforeach
                                                    </select>
                                                    @error('basic_cause_id.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <div class="form-group">
                                                    <label for=""></label>
                                                    <button class="btn btn-danger btn-rounded xs" style="margin-top:23px"  wire:click.prevent="basicRemove({{$key}})"> <i class="fa fa-times"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                       
                                        @endforeach
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="basicAdd({{$z}})"> <i class="fa fa-plus"></i> Basic Cause</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="number">Corrective Actions<span class="required" style="color: red">*</span></label>
                                           <textarea class="form-control" wire:model.debounce.300ms="corrections" cols="30" rows="3" placeholder="What has and/or Should be Done to Control the Causes Listed?" required></textarea>
                                            @error('corrections') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="number">Where to lodge police report</label>
                                            <input type="text" class="form-control" wire:model.debounce.300ms="report" placeholder="Enter Where to lodge police report" >
                                            @error('report') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="number">Authorities Onsite?</label>
                                            <input type="radio" wire:model.debounce.300ms="authorities" value="Yes"  class="line-style"  />
                                            <label for="one" class="radio-label">Yes</label>
                                            <input type="radio" wire:model.debounce.300ms="authorities" value="No"  class="line-style"  />
                                            <label for="one" class="radio-label">No</label>
                                            @error('authorities') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                </div>
                              
                               
                                <div class="row">
                                    <div class="col-md-8">
                                        <label for="name">Authorities Contact Details</label>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="name">Name</label>
                                                    <input type="text" class="form-control" wire:model.debounce.300ms="contact_name.0" placeholder="Enter Name"  />
                                                    @error('contact_name.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="name">Surname</label>
                                                    <input type="text" class="form-control" wire:model.debounce.300ms="contact_surname.0" placeholder="Enter Surname"  />
                                                    @error('contact_surname.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="email">Email</label>
                                                    <input type="email" class="form-control" wire:model.debounce.300ms="contact_email.0" placeholder="Enter Email" />
                                                    @error('contact_email.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="phonenumber">Phonenumber</label>
                                                    <input type="number" class="form-control" wire:model.debounce.300ms="contact_phonenumber.0" placeholder="Enter Mobile Number" />
                                                    @error('contact_phonenumber.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="department">Department</label>
                                                    <input type="text" class="form-control" wire:model.debounce.300ms="department.0" placeholder="Enter Department" />
                                                    @error('department.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                        </div>
                                        @foreach ($contacts_inputs as $key => $value)
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="name">Name</label>
                                                    <input type="text" class="form-control" wire:model.debounce.300ms="contact_name.{{ $value }}" placeholder="Enter Name"  />
                                                    @error('contact_name.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="name">Surname</label>
                                                    <input type="text" class="form-control" wire:model.debounce.300ms="contact_surname.{{ $value }}" placeholder="Enter Surname" />
                                                    @error('contact_surname.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="email">Email</label>
                                                    <input type="email" class="form-control" wire:model.debounce.300ms="contact_email.{{ $value }}" placeholder="Enter Email"/>
                                                    @error('contact_email.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="phonenumber">Phonenumber</label>
                                                    <input type="number" class="form-control" wire:model.debounce.300ms="contact_phonenumber.{{ $value }}" placeholder="Enter Mobile Number"  />
                                                    @error('contact_phonenumber.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="department">Department</label>
                                                    <input type="text" class="form-control" wire:model.debounce.300ms="department.{{ $value }}" placeholder="Enter Department"  />
                                                    @error('department.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <div class="form-group">
                                                    <label for=""></label>
                                                    <button class="btn btn-danger btn-rounded xs" style="margin-top:23px"  wire:click.prevent="contactsRemove({{$key}})"> <i class="fa fa-times"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="contactsAdd({{$o}})"> <i class="fa fa-plus"></i> Contact</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                      
                                            <div class="form-group">
                                                <label for="name">Followup Date</label>
                                                <input type="date" class="form-control" wire:model.debounce.300ms="followup_date.0" placeholder="Enter Date"  />
                                                @error('followup_date.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        
                                       
                                        @foreach ($dates_inputs as $key => $value)
                                        <div class="row">
                                            <div class="col-md-9">
                                                <div class="form-group">
                                                    <label for="name">Next Followup</label>
                                                    <input type="date" class="form-control" wire:model.debounce.300ms="followup_date.{{ $value }}" placeholder="Enter Date"  />
                                                    @error('followup_date.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-1" style="margin-left: -20px">
                                                <div class="form-group">
                                                    <label for=""></label>
                                                    <button class="btn btn-danger btn-rounded xs" style="margin-top:23px"  wire:click.prevent="datesRemove({{$key}})"> <i class="fa fa-times"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="datesAdd({{$g}})"> <i class="fa fa-plus"></i> Date</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                   
                                   
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="btn-group pull-right mt-10" >
                                           <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                                            <button type="submit" class="btn bg-success btn-wide btn-rounded" > <i class="fa fa-save"></i>Save</button>
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

@section('extra-js')
<script>

    //define the variable globally
    var selectStr = "";

    function searchSelect(field) {

    key = window.event.keyCode;

    //Only allow the space and upper/lower case letters
    if ( key==32 || (key>=65 && key<=90) || (key>=97 && key<=122) ) {
        letter = String.fromCharCode(key);
        selectStr += letter;
    }

    for (i=0; i<field.options.length; i++) {
        if (field.options[i].text.substr(0,selectStr.length).toLowerCase() == selectStr.toLowerCase()) {
            field.selectedIndex = i;
            break;
        }
    }
    }

    </script>
@endsection
</div>
