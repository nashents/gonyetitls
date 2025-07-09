<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                                <div class="panel-title">
                                <div class="row">
                                        <div class="col-lg-3">
                                            <div class="input-group">
                                                <span class="input-group-addon">
                                                Filter By
                                                </span>
                                                <select wire:model.debounce.300ms="invoice_filter" class="form-control" aria-label="..." >
                                                    <option value="created_at">Shift Created At</option>
                                                    <option value="date">Shift Date</option>
                                                </select>
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                             
                                        <div class="col-lg-2" style="margin-right: 7px; margin-left:-15px;">
                                            <div class="input-group">
                                                <span class="input-group-addon">
                                        From
                                        </span>
                                        <input type="date" wire:model.debounce.300ms="from"  class="form-control" aria-label="...">
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                        <div class="col-lg-2" style="margin-left: 7px">
                                            <div class="input-group">
                                                <span class="input-group-addon">
                                        To
                                        </span>
                                        <input type="date" wire:model.debounce.300ms="to"  class="form-control" aria-label="...">
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                </div>
                            </div>
                            <div class="panel-title">
                                <a href="" data-toggle="modal" data-target="#shiftModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Shift</a>
                                <a href="" data-toggle="modal" data-target="#shiftsImportModal" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-upload"></i>Import Shifts</a>
                                <a href="" data-toggle="modal" data-target="#shiftTripsImportModal" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-upload"></i>Import Shift Trips</a>
                                <a href="#" wire:click="exportShiftsExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                <a href="#" wire:click="exportShiftsCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                                <a href="#" wire:click="exportShiftsPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                            <div class="col-md-5" style="float: right; padding-right:2px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search Shifts...">
                                </div>
                            </div>
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Shift#
                                    </th>
                                    <th class="th-sm">Type
                                    </th>
                                    <th class="th-sm">Narration
                                    </th>
                                    <th class="th-sm">When
                                    </th>
                                    <th class="th-sm">
                                           Timeline
                                    </th>
                                    <th class="th-sm" style="width:120px;">
                                        F/C Hours
                                        <hr style="margin-top:2px; margin-bottom:2px">
                                        F/C Mileage
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($shifts))
                                <tbody>
                                    @forelse($shifts as $shift)
                                  <tr>
                                    <td>
                                         {{$shift->shift_number}} <br>
                                         <small><strong>CreatedBy:</strong> {{$shift->user ? $shift->user->name : ""}} {{$shift->user ? $shift->user->surname : ""}}</small>
                                    </td>
                                    <td>
                                         {{ucfirst($shift->type)}}  {{ucfirst($shift->for)}} Shift
                                    </td>
                                     <td>
                                        <strong>Customer:</strong> {{$shift->customer ? $shift->customer->name : ""}} <br>
                                        <strong>Cargo:</strong> {{$shift->cargo ? $shift->cargo->name : ""}} <br>
                                        @if ($shift->driver)
                                              <strong>Driver:</strong>  {{$shift->driver->employee ? $shift->driver->employee->name : ""}} {{$shift->driver->employee ? $shift->driver->employee->surname : ""}} <br>        
                                        @endif
                                        @if ($shift->horse)
                                              <strong>Horse:</strong>  {{$shift->horse->registration_number}} <br>
                                        @elseif($shift->vehicle)
                                               <strong>Vehicle:</strong> {{$shift->horse->registration_number}} <br>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>Date:</strong> {{$shift->date}} <br>
                                        <strong>Duty Start Time:</strong> {{$shift->shift_start_time}} <br>
                                        <strong>Duty Close Time:</strong> {{$shift->shift_end_time}} <br>
                                    </td>
                                    <td>
                                        <strong>Depart Workshop: </strong> {{$shift->depart_workshop_time}} <br>
                                        <strong>Arrive Worksite: </strong> {{$shift->arrive_location_time}} <br>
                                        <strong>Depart Worksite: </strong> {{$shift->depart_location_time}} <br>
                                        <strong>Arrive Workshop: </strong> {{$shift->arrive_workshop_time}} <br>
                                    </td>
                                   <td>
                                        {{$shift->fuel_consumption_hours ? $shift->fuel_consumption_hours." L/H" : ""}}
                                            <hr style="margin-top:5px; margin-bottom:5px"> 
                                        {{$shift->fuel_consumption_mileage ? $shift->fuel_consumption_mileage." L/Km" : ""}}
                                    </td>
                                    <td><span class="badge bg-{{$shift->status == 1 ? "warning" : "success"}}">{{$shift->status == 1 ? "Open" : "Closed"}}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{ route('shifts.show', $shift->id) }}"   ><i class="fa fa-eye color-default"></i> View</a></li>
                                                <li><a href="#"  wire:click="edit({{$shift->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#shiftDeleteModal{{ $shift->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('shifts.delete')
                                    </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="12">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Shifts Found ....
                                        </div>
                                       
                                    </td>
                                  </tr>
                                  @endforelse
                                </tbody>
                                @else
                                    <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                                 @endif
                              </table>

                              <nav class="text-center" style="float: right">
                                <ul class="pagination rounded-corners">
                                    @if (isset($shifts))
                                        {{ $shifts->links() }} 
                                    @endif 
                                </ul>
                            </nav>   

                            <!-- /.col-md-12 -->
                        </div>
                    </div>
                </div>

            </div>
            <!-- /.row -->

        </div>
        <!-- /.container-fluid -->
    </section>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="shiftsImportModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-upload"></i>Import Shift(s) <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="importShifts()" method="POST" enctype="multipart/form-data">
                  
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                                <div class="form-group">
                                <label for="name">For<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="for">
                                    <option value="">Select Option</option>
                                    <option value="Rehandling">Rehandling Work</option>
                                    <option value="Trips">Trips</option>
                                </select>
                                @error('for') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Upload Shift(s) Excel File</label>
                                <input type="file" class="form-control" wire:model.debounce.300ms="importFile"placeholder="Upload Shifts File" required>
                                @error('importFile') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                 
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button  type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-upload"></i>Upload</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>
   
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="shiftTripsImportModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-upload"></i>Import Shift Trip(s) <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="importShiftTrips()" method="POST" enctype="multipart/form-data">
                  
                <div class="modal-body">
                   <div class="form-group">
                        <label for="name">Upload Shift(s) Excel File</label>
                        <input type="file" class="form-control" wire:model.debounce.300ms="shift_tripsimportFile"placeholder="Upload Shifts File" required>
                        @error('shift_tripsimportFile') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button  type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-upload"></i>Upload</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="shiftModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-60" role="document">
        <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Shift <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                             <div class="form-group">
                                <label for="name">Type<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="type">
                                    <option value="">Select Option</option>
                                    <option value="Day">Day</option>
                                    <option value="Night">Night</option>
                                </select>
                                @error('type') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">For<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="for">
                                    <option value="">Select Option</option>
                                    <option value="Rehandling">Rehandling Work</option>
                                    <option value="Trips">Trips</option>
                                </select>
                                @error('for') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                             <div class="form-group">
                                <label for="name">Customers<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="customer_id">
                                    <option value="">Select Customer</option>
                                    @foreach ($customers as $customer)
                                        <option value="{{$customer->id}}">{{$customer->name}}</option>
                                    @endforeach
                                </select>
                                @error('customer_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                             <div class="form-group">
                                <label for="name">Cargos<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="cargo_id">
                                    <option value="">Select Cargo</option>
                                    @foreach ($cargos as $cargo)
                                        <option value="{{$cargo->id}}">{{$cargo->name}}</option>
                                    @endforeach
                                </select>
                                @error('cargo_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
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
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="driver"><a href="{{ route('drivers.index') }}" target="_blank" style="color: blue">Driver(s)</a><span class="required" style="color: red">*</span></label> 
                                <input type="checkbox" wire:model.debounce.300ms="all_drivers"   class="line-style" />
                                <label for="one" class="radio-label">Select from all drivers</label>
                                <input type="text" wire:model.lazy="searchDriver" placeholder="Search with name..." class="form-control" >
                                <select class="form-control" wire:model.debounce.300ms="driver_id" required size="4">
                                    <option value="">Select Driver</option>
                                    @if (!is_null($selectedTransporter))
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
                        </div>
                        <div class="col-md-6">
                            @if (!isset($equipment))
                                <div class="form-group" >
                                    <label for="name">Equipment?</label>
                                    <label class="radio-inline">
                                        <input type="radio" wire:model.debounce.300ms="equipment" value="Horse" name="optradio" >Horse
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" wire:model.debounce.300ms="equipment" value="Vehicle" name="optradio">Vehicle
                                    </label>
                                </div>
                            @endif
                            @if (isset($equipment) && $equipment == "Horse")
                                <div class="form-group">
                                    <label for="horse"><a href="{{ route('horses.index') }}" target="_blank" style="color: blue">Horse(s)</a><span class="required" style="color: red">*</span></label>
                                    <input type="checkbox" wire:model.debounce.300ms="all_horses"   class="line-style" />
                                    <label for="one" class="radio-label">Select from all horses</label>
                                    <input type="text" wire:model.lazy="searchHorse" placeholder="Search with reg..." class="form-control">
                                    <select class="form-control" wire:model.debounce.300ms="selectedHorse"  required size="4">
                                        <option value="">Select Horse </option>
                                        @if (!is_null($selectedTransporter))
                                        @foreach ($horses as $horse)
                                        <option value="{{$horse->id}}"> {{$horse->registration_number}} {{$horse->fleet_number ? "(".$horse->fleet_number.")" : ""}} {{$horse->horse_make ? $horse->horse_make->name : ""}} {{$horse->horse_model ? $horse->horse_model->name : ""}}</option>
                                        @endforeach
                                        @endif

                                    </select>
                                    @error('selectedHorse') <span class="text-danger error">{{ $message }}</span>@enderror
                                    <small>  <a href="{{ route('horses.create') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Horse</a></small> <a href="#" wire:click.prevent="refresh('horses')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                </div>
                            @elseif (isset($equipment) && $equipment == "Vehicle")
                                <div class="form-group">
                                    <label for="horse"><a href="{{ route('vehicles.index') }}" target="_blank" style="color: blue">Vehicle(s)</a><span class="required" style="color: red">*</span></label>
                                    <input type="checkbox" wire:model.debounce.300ms="all_vehicles"   class="line-style" />
                                    <label for="one" class="radio-label">Select from all vehicles</label>
                                    <input type="text" wire:model.lazy="searchVehicle" placeholder="Search with reg..." class="form-control">
                                    <select class="form-control" wire:model.debounce.300ms="selectedVehicle"  required size="4">
                                        <option value="">Select Vehicle </option>
                                        @if (!is_null($selectedTransporter))
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
                    </div>
                    <div class="row">
                         <div class="col-md-3">
                            <div class="form-group">
                                <label for="name">Currencies</label>
                                <select class="form-control" wire:model.debounce.300ms="selectedCurrency">
                                    <option value="">Select Currency</option>
                                    @foreach ($currencies as $currency)
                                        <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                    @endforeach
                                </select>
                                @error('selectedCurrency') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                              @if (!is_null($selectedCurrency))
                                @if ($company)
                                    @if ($selectedCurrency != $company->currency_id)
                                    <div class="form-group">
                                        <label for="customer">Conversion Rate<span class="required" style="color: red">*</span></label>
                                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="exchange_rate"  placeholder="Exchange Rate {{$selected_currency ? "From ".$selected_currency->name : ""}} {{$company->currency ? "To ".$company->currency->name : ""}}" required>
                                        @error('exchange_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                        <small style="color: green">{{$selected_currency ? " 1 ".$selected_currency->name." is how much in" : ""}} {{$company->currency ? $company->currency->name." ?" : ""}}</small>
                                        <small>{{$exchange_amount ? "The converted amount is: ".$exchange_amount : ""}}</small> <br>
                                    </div> 
                                    @endif
                                @endif
                            @endif
                        </div>
                         <div class="col-md-3">
                            <div class="form-group">
                                <label for="name">Date<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter Shift Date" required/>
                                @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                         <div class="col-md-3">
                            <div class="form-group">
                                <label for="name">Duty Start Time</label>
                                <input type="time" class="form-control" wire:model.debounce.300ms="shift_start_time" placeholder="Enter Shift Start Time"/>
                                @error('shift_start_time') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                         <div class="col-md-3">
                            <div class="form-group">
                                <label for="name">Duty Close Time</label>
                                <input type="time" class="form-control" wire:model.debounce.300ms="shift_end_time" placeholder="Enter Shift End Time" />
                                @error('shift_end_time') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    
                @if (!is_null($for))
                @if ($for === "Rehandling")

                             <div class="row">
                         <div class="col-md-3">
                            <div class="form-group">
                                <label for="name">Depart Workshop</label>
                                <input type="time" class="form-control" wire:model.debounce.300ms="depart_workshop_time" placeholder="Enter Depart Workshop Time" />
                                @error('depart_workshop_time') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                         <div class="col-md-3">
                            <div class="form-group">
                                <label for="name">Arrive Worksite Time</label>
                                <input type="time" class="form-control" wire:model.debounce.300ms="arrive_location_time" placeholder="Enter Arrival Worksite Time" />
                                @error('arrive_location_time') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                         <div class="col-md-3">
                            <div class="form-group">
                                <label for="name">Depart Worksite Time</label>
                                <input type="time" class="form-control" wire:model.debounce.300ms="depart_location_time" placeholder="Enter Depart Worksite Time" />
                                @error('depart_location_time') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                          <div class="col-md-3">
                            <div class="form-group">
                                <label for="name">Arrive Worksite Time</label>
                                <input type="time" class="form-control" wire:model.debounce.300ms="arrive_workshop_time" placeholder="Enter Arrival Workshop Time" />
                                @error('arrive_workshop_time') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <h5 class="underline mt-30">Rehandling Work</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Start Time<span class="required" style="color: red">*</span></label>
                                <input type="time" class="form-control" wire:model.debounce.300ms="start_time.0" placeholder="Enter Start Time" required/>
                                @error('start_time.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Open Hours<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="open_hours.0" placeholder="Enter Open Engine Hours" required/>
                                @error('open_hours.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Open Mileage</label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="open_mileage.0" placeholder="Enter Open Mileage"/>
                                @error('open_mileage.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                              <div class="form-group">
                                <label for="name">Work Descriptions<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="work_id.0" required>
                                    <option value="">Select Work/Job</option>
                                    @foreach ($works as $work)
                                        <option value="{{$work->id}}">{{$work->description}}</option>
                                    @endforeach
                                </select>
                                @error('work_id.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                <small><a href="{{ route('works.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Work</a></small> <a href="#" wire:click.prevent="refresh('works')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                            </div>
                        </div>
                        <div class="col-md-6">
                              <div class="form-group">
                                <label for="name">Locations / Work Sites<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="location_id.0" required>
                                    <option value="">Select Site</option>
                                    @foreach ($locations as $location)
                                        <option value="{{$location->id}}">{{$location->name}}</option>
                                    @endforeach
                                </select>
                                @error('location_id.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                <small><a href="{{ route('locations.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Location / WorkSite</a></small> <a href="#" wire:click.prevent="refresh('locations')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                            </div>
                        </div>
                    </div>
               
                    <div class="row">
                         <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Close Time</label>
                                <input type="time" class="form-control" wire:model.debounce.300ms="stop_time.0" placeholder="Enter Close Time" />
                                @error('stop_time.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                         <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Close Hours</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="close_hours.0" placeholder="Enter Close Engine Hours" />
                                @error('close_hours.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                         <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Close Mileage</label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="close_mileage.0" placeholder="Enter Close Mileage" />
                                @error('close_mileage.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div> 
                    </div>
                    <div class="row">
                         <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Weight</label>
                                <input type="time" class="form-control" wire:model.debounce.300ms="weight.0" placeholder="Enter Work Weight" />
                                @error('weight.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                         <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Freight</label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="freight.0" placeholder="Enter Work Freight" />
                                @error('freight.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div> 
                    </div>
                    <br>  

                    @foreach ($inputs as $key => $value)
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Start Time<span class="required" style="color: red">*</span></label>
                                <input type="time" class="form-control" wire:model.debounce.300ms="start_time.{{$value}}" placeholder="Enter Start Time" required/>
                                @error('start_time.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Open Hours<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="open_hours.{{$value}}" placeholder="Enter Open Engine Hours" required/>
                                @error('open_hours.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Open Mileage</label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="open_mileage.{{$value}}" placeholder="Enter Open Mileage"/>
                                @error('open_mileage.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>   
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                              <div class="form-group">
                                <label for="name">Work Descriptions<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="work_id.{{$value}}" required>
                                    <option value="">Select Work/Job</option>
                                    @foreach ($works as $work)
                                        <option value="{{$work->id}}">{{$work->description}}</option>
                                    @endforeach
                                </select>
                                @error('work_id.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                <small><a href="{{ route('works.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Work</a></small> <a href="#" wire:click.prevent="refresh('works')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                            </div>
                        </div>
                        <div class="col-md-6">
                              <div class="form-group">
                                <label for="name">Locations / Work Sites<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="location_id.{{$value}}" required>
                                    <option value="">Select Site</option>
                                    @foreach ($locations as $location)
                                        <option value="{{$location->id}}">{{$location->name}}</option>
                                    @endforeach
                                </select>
                                @error('location_id.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                <small><a href="{{ route('locations.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Location / WorkSite</a></small> <a href="#" wire:click.prevent="refresh('locations')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                            </div>
                        </div>
                    </div>
               
                    <div class="row">
                         <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Close Time</label>
                                <input type="time" class="form-control" wire:model.debounce.300ms="stop_time.{{$value}}" placeholder="Enter Close Time"/>
                                @error('stop_time.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                         <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Close Hours</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="close_hours.{{$value}}" placeholder="Enter Close Engine Hours" />
                                @error('close_hours.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                         <div class="col-md-3">
                            <div class="form-group">
                                <label for="name">Close Mileage</label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="close_mileage.{{$value}}" placeholder="Enter Close Mileage" />
                                @error('close_mileage.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                          <div class="col-md-1">
                            <button class="btn btn-danger btn-rounded btn-sm" wire:click.prevent="remove({{ $key }})" style="margin-top:27px;">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                    </div>
                      <div class="row">
                         <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Weight</label>
                                <input type="time" class="form-control" wire:model.debounce.300ms="weight.{{$value}}" placeholder="Enter Work Weight" />
                                @error('weight.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                         <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Freight</label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="freight.{{$value}}" placeholder="Enter Work Freight" />
                                @error('freight.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div> 
                    </div>
                    <br>   
                    @endforeach
                        <div class="form-group text-end" style="float: right">
                        <button class="btn btn-success btn-rounded btn-sm" wire:click.prevent="add({{ $i }})">
                            <i class="fa fa-plus"></i> Work
                        </button>
                    </div>

                    @endif
                    @endif
            
                   
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
                                        <label for="date">Fillup Date<span class="required" style="color: red">*</span></label>
                                        <input type="date" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter FillUp Date" required/>
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
                                    @if (!is_null($selectedFuelCurrency))
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
                                        <label for="quantity">Quantity<span class="required" style="color: red">*</span></label>
                                       <input type="number" step="any" min="0"  class="form-control"  wire:model.debounce.300ms="fuel_quantity" placeholder="Enter Fillup Quantity" required />
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

                            <div class="row">
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="odometer">Mileage</label>
                                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="mileage" required placeholder="Enter Mileage" />
                                        @error('odometer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="odometer">Hours<span class="required" style="color: red">*</span></label>
                                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="hours" required placeholder="Enter Engine Hours" required/>
                                        @error('hours') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="comments">Comments</label>
                                       <input type="text" wire:model.debounce.300ms="fuel_comments" class="form-control" placeholder="Fuel Order Comments">
                                        @error('fuel_comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                           
                            @endif

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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="shiftEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-60" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit Shift <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >

                <div class="modal-body">
                             <div class="row">
                        <div class="col-md-6">
                             <div class="form-group">
                                <label for="name">Type<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="type">
                                    <option value="">Select Option</option>
                                    <option value="Day">Day</option>
                                    <option value="Night">Night</option>
                                </select>
                                @error('type') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">For<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="for">
                                    <option value="">Select Option</option>
                                    <option value="Rehandling">Rehandling Work</option>
                                    <option value="Trips">Trips</option>
                                </select>
                                @error('for') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                             <div class="form-group">
                                <label for="name">Customers<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="customer_id">
                                    <option value="">Select Customer</option>
                                    @foreach ($customers as $customer)
                                        <option value="{{$customer->id}}">{{$customer->name}}</option>
                                    @endforeach
                                </select>
                                @error('customer_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                             <div class="form-group">
                                <label for="name">Cargos<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="cargo_id">
                                    <option value="">Select Cargo</option>
                                    @foreach ($cargos as $cargo)
                                        <option value="{{$cargo->id}}">{{$cargo->name}}</option>
                                    @endforeach
                                </select>
                                @error('cargo_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
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
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="driver"><a href="{{ route('drivers.index') }}" target="_blank" style="color: blue">Driver(s)</a><span class="required" style="color: red">*</span></label> 
                                <input type="checkbox" wire:model.debounce.300ms="all_drivers"   class="line-style" />
                                <label for="one" class="radio-label">Select from all drivers</label>
                                <input type="text" wire:model.lazy="searchDriver" placeholder="Search with name..." class="form-control" >
                                <select class="form-control" wire:model.debounce.300ms="driver_id" required size="4">
                                    <option value="">Select Driver</option>
                                    @if (!is_null($selectedTransporter))
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
                        </div>
                        <div class="col-md-6">
                            @if (!isset($equipment))
                                <div class="form-group" >
                                    <label for="name">Equipment?</label>
                                    <label class="radio-inline">
                                        <input type="radio" wire:model.debounce.300ms="equipment" value="Horse" name="optradio" >Horse
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" wire:model.debounce.300ms="equipment" value="Vehicle" name="optradio">Vehicle
                                    </label>
                                </div>
                            @endif
                            @if (isset($equipment) && $equipment == "Horse")
                                <div class="form-group">
                                    <label for="horse"><a href="{{ route('horses.index') }}" target="_blank" style="color: blue">Horse(s)</a><span class="required" style="color: red">*</span></label>
                                    <input type="checkbox" wire:model.debounce.300ms="all_horses"   class="line-style" />
                                    <label for="one" class="radio-label">Select from all horses</label>
                                    <input type="text" wire:model.lazy="searchHorse" placeholder="Search with reg..." class="form-control">
                                    <select class="form-control" wire:model.debounce.300ms="selectedHorse"  required size="4">
                                        <option value="">Select Horse </option>
                                        @if (!is_null($selectedTransporter))
                                        @foreach ($horses as $horse)
                                        <option value="{{$horse->id}}"> {{$horse->registration_number}} {{$horse->fleet_number ? "(".$horse->fleet_number.")" : ""}} {{$horse->horse_make ? $horse->horse_make->name : ""}} {{$horse->horse_model ? $horse->horse_model->name : ""}}</option>
                                        @endforeach
                                        @endif

                                    </select>
                                    @error('selectedHorse') <span class="text-danger error">{{ $message }}</span>@enderror
                                    <small>  <a href="{{ route('horses.create') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Horse</a></small> <a href="#" wire:click.prevent="refresh('horses')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                </div>
                            @elseif (isset($equipment) && $equipment == "Vehicle")
                                <div class="form-group">
                                    <label for="horse"><a href="{{ route('vehicles.index') }}" target="_blank" style="color: blue">Vehicle(s)</a><span class="required" style="color: red">*</span></label>
                                    <input type="checkbox" wire:model.debounce.300ms="all_vehicles"   class="line-style" />
                                    <label for="one" class="radio-label">Select from all vehicles</label>
                                    <input type="text" wire:model.lazy="searchVehicle" placeholder="Search with reg..." class="form-control">
                                    <select class="form-control" wire:model.debounce.300ms="selectedVehicle"  required size="4">
                                        <option value="">Select Vehicle </option>
                                        @if (!is_null($selectedTransporter))
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
                    </div>
                    <div class="row">
                         <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Date<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter Shift Date" required/>
                                @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                         <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Duty Start Time</label>
                                <input type="time" class="form-control" wire:model.debounce.300ms="shift_start_time" placeholder="Enter Shift Start Time"/>
                                @error('shift_start_time') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                         <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Duty Close Time</label>
                                <input type="time" class="form-control" wire:model.debounce.300ms="shift_end_time" placeholder="Enter Shift End Time" />
                                @error('shift_end_time') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                         <div class="col-md-3">
                            <div class="form-group">
                                <label for="name">Depart Workshop</label>
                                <input type="time" class="form-control" wire:model.debounce.300ms="depart_workshop_time" placeholder="Enter Depart Workshop Time" />
                                @error('depart_workshop_time') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                         <div class="col-md-3">
                            <div class="form-group">
                                <label for="name">Arrive Worksite Time</label>
                                <input type="time" class="form-control" wire:model.debounce.300ms="arrive_location_time" placeholder="Enter Arrival Worksite Time" />
                                @error('arrive_location_time') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                         <div class="col-md-3">
                            <div class="form-group">
                                <label for="name">Depart Worksite Time</label>
                                <input type="time" class="form-control" wire:model.debounce.300ms="depart_location_time" placeholder="Enter Depart Worksite Time" />
                                @error('depart_location_time') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                          <div class="col-md-3">
                            <div class="form-group">
                                <label for="name">Arrive Worksite Time</label>
                                <input type="time" class="form-control" wire:model.debounce.300ms="arrive_workshop_time" placeholder="Enter Arrival Workshop Time" />
                                @error('arrive_workshop_time') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                @if (!is_null($for))
                @if ($for === "Rehandling")
                    <h5 class="underline mt-30">Rehandling Work</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Start Time<span class="required" style="color: red">*</span></label>
                                <input type="time" class="form-control" wire:model.debounce.300ms="start_time.0" placeholder="Enter Start Time" required/>
                                @error('start_time.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Open Hours<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="open_hours.0" placeholder="Enter Open Engine Hours" required/>
                                @error('open_hours.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Open Mileage</label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="open_mileage.0" placeholder="Enter Open Mileage"/>
                                @error('open_mileage.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                              <div class="form-group">
                                <label for="name">Work Descriptions<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="work_id.0" required>
                                    <option value="">Select Work/Job</option>
                                    @foreach ($works as $work)
                                        <option value="{{$work->id}}">{{$work->description}}</option>
                                    @endforeach
                                </select>
                                @error('work_id.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                <small><a href="{{ route('works.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Work</a></small> <a href="#" wire:click.prevent="refresh('works')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                            </div>
                        </div>
                        <div class="col-md-6">
                              <div class="form-group">
                                <label for="name">Locations / Work Sites<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="location_id.0" required>
                                    <option value="">Select Site</option>
                                    @foreach ($locations as $location)
                                        <option value="{{$location->id}}">{{$location->name}}</option>
                                    @endforeach
                                </select>
                                @error('location_id.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                <small><a href="{{ route('locations.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Location / WorkSite</a></small> <a href="#" wire:click.prevent="refresh('locations')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                            </div>
                        </div>
                    </div>
               
                    <div class="row">
                         <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Close Time</label>
                                <input type="time" class="form-control" wire:model.debounce.300ms="stop_time.0" placeholder="Enter Close Time" />
                                @error('stop_time.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                         <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Close Hours</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="close_hours.0" placeholder="Enter Close Engine Hours" />
                                @error('close_hours.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                         <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Close Mileage</label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="close_mileage.0" placeholder="Enter Close Mileage" />
                                @error('close_mileage.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div> 
                    </div>
                    <br>  

                    @foreach ($inputs as $key => $value)
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Start Time<span class="required" style="color: red">*</span></label>
                                <input type="time" class="form-control" wire:model.debounce.300ms="start_time.{{$value}}" placeholder="Enter Start Time" required/>
                                @error('start_time.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Open Hours<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="open_hours.{{$value}}" placeholder="Enter Open Engine Hours" required/>
                                @error('open_hours.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Open Mileage</label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="open_mileage.{{$value}}" placeholder="Enter Open Mileage"/>
                                @error('open_mileage.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>   
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                              <div class="form-group">
                                <label for="name">Work Descriptions<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="work_id.{{$value}}" required>
                                    <option value="">Select Work/Job</option>
                                    @foreach ($works as $work)
                                        <option value="{{$work->id}}">{{$work->description}}</option>
                                    @endforeach
                                </select>
                                @error('work_id.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                <small><a href="{{ route('works.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Work</a></small> <a href="#" wire:click.prevent="refresh('works')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                            </div>
                        </div>
                        <div class="col-md-6">
                              <div class="form-group">
                                <label for="name">Locations / Work Sites<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="location_id.{{$value}}" required>
                                    <option value="">Select Site</option>
                                    @foreach ($locations as $location)
                                        <option value="{{$location->id}}">{{$location->name}}</option>
                                    @endforeach
                                </select>
                                @error('location_id.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                <small><a href="{{ route('locations.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Location / WorkSite</a></small> <a href="#" wire:click.prevent="refresh('locations')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                            </div>
                        </div>
                    </div>
               
                    <div class="row">
                         <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Close Time</label>
                                <input type="time" class="form-control" wire:model.debounce.300ms="stop_time.{{$value}}" placeholder="Enter Close Time"/>
                                @error('stop_time.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                         <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Close Hours</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="close_hours.{{$value}}" placeholder="Enter Close Engine Hours" />
                                @error('close_hours.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                         <div class="col-md-3">
                            <div class="form-group">
                                <label for="name">Close Mileage</label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="close_mileage.{{$value}}" placeholder="Enter Close Mileage" />
                                @error('close_mileage.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                          <div class="col-md-1">
                            <button class="btn btn-danger btn-rounded btn-sm" wire:click.prevent="remove({{ $key }})" style="margin-top:27px;">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <br>   
                    @endforeach
                        <div class="form-group text-end" style="float: right">
                        <button class="btn btn-success btn-rounded btn-sm" wire:click.prevent="add({{ $i }})">
                            <i class="fa fa-plus"></i> Work
                        </button>
                    </div>

                    @endif
                    @endif
            
                   
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
                                        <label for="date">Fillup Date<span class="required" style="color: red">*</span></label>
                                        <input type="date" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter FillUp Date" required/>
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
                                    @if (!is_null($selectedFuelCurrency))
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
                                        <label for="quantity">Quantity<span class="required" style="color: red">*</span></label>
                                       <input type="number" step="any" min="0"  class="form-control"  wire:model.debounce.300ms="fuel_quantity" placeholder="Enter Fillup Quantity" required />
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

                            <div class="row">
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="odometer">Mileage</label>
                                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="mileage" required placeholder="Enter Mileage" />
                                        @error('odometer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="odometer">Hours<span class="required" style="color: red">*</span></label>
                                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="hours" required placeholder="Enter Engine Hours" required/>
                                        @error('hours') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="comments">Comments</label>
                                       <input type="text" wire:model.debounce.300ms="fuel_comments" class="form-control" placeholder="Fuel Order Comments">
                                        @error('fuel_comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                           
                            @endif

                             <div class="form-group">
                                <label for="name">Shift Status<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="status">
                                    <option value="">Select Option</option>
                                    <option value="1">Open</option>
                                    <option value="0">Close</option>
                                </select>
                                @error('status') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-refresh"></i>Update</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>


</div>

