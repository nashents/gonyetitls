<div>
    <style>
        .modal-lg {
        max-width: 80%;
    }
    </style>
        <section class="section">
            {{-- <x-loading/> --}}
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel">
                            <div class="panel-heading">
                                <div>
                                    @include('includes.messages')
                                </div>
                                <div class="panel-title">
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="panel-title">
                                                <h5>Reports Date Range</h5>
                                                <div class="row">
                                    
                                                    <div class="col-lg-3">
                                                        <div class="input-group">
                                                            <span class="input-group-addon">
                                                      Filter By
                                                      </span>
                                                      <select wire:model.debounce.300ms="trip_filter" class="form-control" aria-label="..." >
                                                        <option value="created_at">Trip Created At</option>
                                                        {{-- <option value="offloading_date">Trip Offloading Date</option> --}}
                                                        <option value="start_date">Trip Start Date</option>
                                                  </select>
                                                        </div>
                                                        <!-- /input-group -->
                                                    </div>
                                                    <div class="col-lg-2" style="margin-right: 7px">
                                                        <div class="input-group">
                                                            <span class="input-group-addon">
                                                      From
                                                      </span>
                                                      <input type="date" wire:model.debounce.300ms="from" wire:change="dateRange()" class="form-control" aria-label="...">
                                                        </div>
                                                        <!-- /input-group -->
                                                    </div>
                                                    <div class="col-lg-2" style="margin-left: 7px">
                                                        <div class="input-group">
                                                            <span class="input-group-addon">
                                                      To
                                                      </span>
                                                      <input type="date" wire:model.debounce.300ms="to" wire:change="dateRange()" class="form-control" aria-label="...">
                                                        </div>
                                                        <!-- /input-group -->
                                                    </div>
                                                   
                                                    <!-- /input-group -->
                                                </div>
                                                <h5>Filter reports by</h5>
                                                <div class="row">
                                                    
                                                    <div class="col-md-3">
                                                		<div class="input-group">
                                                			<span class="input-group-addon">
                                                       Transporters
                                                      </span>
                                                      <select wire:model.debounce.300ms="selectedTransporter" class="form-control" aria-label="..." >
                                                            <option value="">Select Transporter</option>
                                                            @foreach ($transporters as $transporter)
                                                                 <option value="{{ $transporter->id }}"  >{{ ucfirst($transporter->name) }}</option>
                                                            @endforeach
                                                      </select>
                                                			
                                                		</div>
                                                		<!-- /input-group -->
                                                	</div>
                                                    <div class="col-md-3">
                                                	  <div class="input-group">
                                                			<span class="input-group-addon">
                                                       Horses
                                                      </span>
                                                      <select wire:model.debounce.300ms="selectedHorse" class="form-control" aria-label="..." >
                                                            <option value="">Select Horse</option>
                                                            @foreach ($horses as $horse)
                                                                <option value="{{ $horse->id }}"  > {{ $horse->registration_number }} {{ $horse->fleet_number ? "(".$horse->fleet_number.")" : "" }} </option>
                                                            @endforeach
                                                      </select>
                                                			
                                                		</div>
                                                		<!-- /input-group -->
                                                	</div>
                                                    <div class="col-md-3">
                                                		<div class="input-group">
                                                			<span class="input-group-addon">
                                                       Drivers
                                                      </span>
                                                      <select wire:model.debounce.300ms="selectedDriver" class="form-control" aria-label="..." >
                                                            <option value="">Select Driver</option>
                                                            @foreach ($drivers as $driver)
                                                                <option value="{{ $driver->id }}"  >{{ ucfirst($driver->employee ? $driver->employee->name : " employee") }} {{ ucfirst($driver->employee ? $driver->employee->surname : "") }}</option>
                                                            @endforeach
                                                      </select>
                                                			
                                                		</div>
                                                		<!-- /input-group -->
                                                	</div>
                                                    <div class="col-md-3">
                                                		<div class="input-group">
                                                			<span class="input-group-addon">
                                                       Customers
                                                      </span>
                                                      <select wire:model.debounce.300ms="selectedCustomer" class="form-control" aria-label="..." >
                                                            <option value="">Select Customer</option>
                                                            @foreach ($customers as $customer)
                                                                <option value="{{ $customer->id }}" >{{ ucfirst($customer->name) }}</option>
                                                            @endforeach
                                                      </select>
                                                			
                                                		</div>
                                                		<!-- /input-group -->
                                                	</div>
                                                   
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-3">
                                                		<div class="input-group ">
                                                			<span class="input-group-addon">
                                                       Routes
                                                      </span>
                                                      <select wire:model.debounce.300ms="selectedRoute" class="form-control  " aria-label="..." >
                                                            <option value="">Select Route</option>
                                                            @foreach ($customers as $customer)
                                                                <option value="{{ $customer->id }}" >{{ ucfirst($customer->name) }}</option>
                                                            @endforeach
                                                      </select>
                                                			
                                                		</div>
                                                		<!-- /input-group -->
                                                	</div>
                                                    <div class="col-md-3">
                                                		<div class="input-group ">
                                                			<span class="input-group-addon">
                                                       Agents
                                                      </span>
                                                      <select wire:model.debounce.300ms="selectedAgent" class="form-control  " aria-label="..." >
                                                            <option value="">Select Agent</option>
                                                            @foreach ($agents as $agent)
                                                                <option value="{{ $agent->id }}"  >{{ ucfirst($agent->name) }} {{ ucfirst($agent->surname) }}</option>
                                                            @endforeach
                                                      </select>
                                                			
                                                		</div>
                                                		<!-- /input-group -->
                                                	</div>
                                                    <div class="col-md-3">
                                                		<div class="input-group ">
                                                			<span class="input-group-addon">
                                                       Cargos
                                                      </span>
                                                      <select wire:model.debounce.300ms="selectedCargo" class="form-control  " aria-label="..." >
                                                            <option value="">Select Cargo</option>
                                                            @foreach ($cargos as $cargo)
                                                              <option value="{{ $cargo->id }}"  >{{ ucfirst($cargo->name) }}</option>
                                                             @endforeach
                                                      </select>
                                                			
                                                		</div>
                                                		<!-- /input-group -->
                                                	</div>
                                                    <div class="col-md-3">
                                                		<div class="input-group ">
                                                			<span class="input-group-addon">
                                                       Destinations
                                                      </span>
                                                      <select wire:model.debounce.300ms="selectedDestination" class="form-control  " aria-label="..." >
                                                            <option value="">Select Destination</option>
                                                            @foreach ($destinations as $destination)
                                                                 <option value="{{ $destination->id }}"  >{{ ucfirst($destination->country ? $destination->country->name : "") }} {{ ucfirst($destination->city) }}</option>
                                                            @endforeach
                                                      </select>
                                                			
                                                		</div>
                                                		<!-- /input-group -->
                                                	</div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-3">
                                                		<div class="input-group ">
                                                			<span class="input-group-addon">
                                                       Trip Statuses
                                                      </span>
                                                      <select wire:model.debounce.300ms="selectedStatus" class="form-control  " aria-label="..." >
                                                            <option value="">Select Status</option>
                                                            <option value="Scheduled">Scheduled</option>
                                                            <option value="Loading Point">Loading Point</option>
                                                            <option value="Loaded">Loaded</option>
                                                            <option value="Instransit">Instransit</option>
                                                            <option value="Offloading Point">Offloading Point</option>
                                                            <option value="Offloaded">Offloaded</option>
                                                            <option value="Onhold">Onhold</option>
                                                      </select>
                                                			
                                                		</div>
                                                		<!-- /input-group -->
                                                	</div>
                                                    <div class="col-md-3">
                                                		<div class="input-group ">
                                                			<span class="input-group-addon">
                                                       Trip Type
                                                      </span>
                                                      <select wire:model.debounce.300ms="selectedTripType" class="form-control  " aria-label="..." >
                                                            <option value="">Select Trip Type</option>
                                                            @foreach ($trip_types as $trip_type)
                                                                <option value="{{ $trip_type->id }}">{{ $trip_type->name }}</option> 
                                                            @endforeach
                                                            
                                                      </select>
                                                			
                                                		</div>
                                                		<!-- /input-group -->
                                                	</div>
                                                    <div class="col-md-3">
                                                		<div class="input-group ">
                                                			<span class="input-group-addon">
                                                      Employee
                                                      </span>
                                                      <select wire:model.debounce.300ms="selectedUser" class="form-control  " aria-label="..." >
                                                            <option value="">Select Employee</option>
                                                            @foreach ($users as $user)
                                                        
                                                            <option value="{{ $user->id }}">{{ $user->employee ? $user->employee->name : "" }} {{ $user->employee ? $user->employee->surname : "" }}</option> 
                                                          
                                                              
                                                            @endforeach
                                                            
                                                      </select>
                                                			
                                                		</div>
                                                		<!-- /input-group -->
                                                	</div>
                                                    <div class="col-md-3">
                                                		<div class="input-group ">
                                                			<span class="input-group-addon">
                                                      Currency
                                                      </span>
                                                      <select wire:model.debounce.300ms="selectedCurrency" class="form-control  " aria-label="..." >
                                                            <option value="">Select Currency</option>
                                                            @foreach ($currencies as $currency)
                                                        
                                                            <option value="{{ $currency->id }}">{{ $currency->name}}</option> 
                                                          
                                                              
                                                            @endforeach
                                                            
                                                      </select>
                                                			
                                                		</div>
                                                		<!-- /input-group -->
                                                	</div>
                                                </div>
                                               
                                                <div class="row">
                                                    <div class="col-md-4">
                                                    <h5>Compact Trips Reports</h5>
                                                    <a href="#" wire:click.prevent="exportCompactTripsExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                                    <a href="#" wire:click.prevent="exportCompactTripsCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                                                    <a href="#" wire:click.prevent="exportCompactTripsPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a>
                                                    </div>
                                                    <div class="col-md-4" style="margin-left: -60px">
                                                        <h5>Extended Trips Reports</h5>
                                                    <a href="#" wire:click.prevent="exportTripsExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                                    <a href="#" wire:click.prevent="exportTripsCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                                                    <a href="#" wire:click.prevent="exportTripsPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a>
                                                    </div>
                                                    <div class="col-md-4">
                                                    </div>
                                                </div>
                                               
                                               
                                            </div>
                                        </div>
                                  
                                   
                                </div>
                            </div>
                        </div>
                            <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                               
                                <table  class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%" style=" width:100%; height:100%;">
                                    <thead >
                                        <th class="th-sm">Trip#
                                        </th>
                                        <th class="th-sm">Customer
                                        </th>
                                        <th class="th-sm">Transporter
                                        </th>
                                        <th class="th-sm">Horse
                                        </th>
                                        <th class="th-sm">LP
                                        </th>
                                        <th class="th-sm">OP
                                        </th>
                                        <th class="th-sm">Status
                                        </th>
                                        <th class="th-sm">Invoice
                                        </th>
                                        <th class="th-sm">PODS
                                        </th>
                                        <th class="th-sm">Authorization
                                        </th>
                                        <th class="th-sm">Actions
                                        </th>
                                      </tr>
                                    </thead>
                                    @if ($trips->count()>0)
                                    <tbody>
                                        @forelse ($trips as $trip)
                                        @if ($trip->trip_status == "Offloaded")
                                      <tr style="background-color: #5cb85c">
                                        @php
                                            $from = App\Models\Destination::find($trip->from);
                                            $to = App\Models\Destination::find($trip->to);
                                         
                                        @endphp
                                        <td>{{ucfirst($trip->trip_number)}}</td>
                                        <td>{{ucfirst($trip->customer ? $trip->customer->name : "")}}</td>
                                        <td>{{ucfirst($trip->transporter ? $trip->transporter->name : "")}}</td>
                                        @if ($trip->horse)
                                        <td>{{ucfirst($trip->horse->horse_make ? $trip->horse->horse_make->name : "")}} {{ucfirst($trip->horse->horse_model ? $trip->horse->horse_model->name : "")}} ({{ucfirst($trip->horse ? $trip->horse->registration_number : "")}})</td>
                                        @else
                                            <td></td>
                                        @endif
                                        
                                        <td>{{$trip->loading_point ? $trip->loading_point->name : ""}}</td>
                                        <td>{{$trip->offloading_point ? $trip->offloading_point->name : ""}}</td>
                                       
                                        @if ($trip->trip_status == "Offloaded")
                                        <td class="table-success"><span class="label label-success label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Scheduled")
                                        <td class="table-warning" ><span class="label label-warning label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Loading Point")
                                        <td class="table-gray" ><span class="label label-gray label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Loaded")
                                        <td class="table-info"><span class="label label-info label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "InTransit")
                                        <td class="table-primary"><span class="label label-primary label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Started")
                                        <td class="table-primary"><span class="label label-primary label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "OnHold")
                                        <td class="table-danger"><span class="label label-danger label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Cancelled")
                                        <td class="table-light"><span class="label label-light label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Offloading Point")
                                        <td class="table-accent"><span class="label label-accent label-wide">{{$trip->trip_status}}</span></td>
                                        @endif
                                         <td> <span class="label label-{{isset($trip->invoice) ? "success" : "warning"}}"> {{isset($trip->invoice) ? "issued" : "pending"}}</span></td>
                                         <td> <span class="label label-{{isset($trip->delivery_note->offloaded) ? "success" : "warning"}}"> {{isset($trip->delivery_note->offloaded) ? "submitted" : "pending"}}</span></td>
                                         <td><span class="label label-{{($trip->authorization == 'approved') ? 'success' : (($trip->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($trip->authorization == 'approved') ? 'approved' : (($trip->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                         <td class="w-10 line-height-35 table-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="{{route('trips.show', $trip->id)}}"><i class="fas fa-eye color-default"></i>View</a></li>
                                                </ul>
                                            </div>
                                            @include('trips.delete')

                                    </td>
                                      </tr>
                                      @elseif($trip->trip_status == "Scheduled")
                                      <tr style="background-color: #f0ad4e" >
                                        @php
                                            $from = App\Models\Destination::find($trip->from);
                                            $to = App\Models\Destination::find($trip->to);
                                         
                                        @endphp
                                        <td>{{ucfirst($trip->trip_number)}}</td>
                                        <td>{{ucfirst($trip->customer ? $trip->customer->name : "")}}</td>
                                        <td>{{ucfirst($trip->transporter ? $trip->transporter->name : "")}}</td>
                                        @if ($trip->horse)
                                        <td>{{ucfirst($trip->horse->horse_make ? $trip->horse->horse_make->name : "")}} {{ucfirst($trip->horse->horse_model ? $trip->horse->horse_model->name : "")}} ({{ucfirst($trip->horse ? $trip->horse->registration_number : "")}})</td>
                                        @else
                                            <td></td>
                                        @endif
                                        <td>{{$trip->loading_point ? $trip->loading_point->name : ""}}</td>
                                        <td>{{$trip->offloading_point ? $trip->offloading_point->name : ""}}</td>
                                        @if ($trip->trip_status == "Offloaded")
                                        <td class="table-success"><span class="label label-success label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Scheduled")
                                        <td class="table-warning" ><span class="label label-warning label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Loading Point")
                                        <td class="table-gray" ><span class="label label-gray label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Loaded")
                                        <td class="table-info"><span class="label label-info label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "InTransit")
                                        <td class="table-primary"><span class="label label-primary label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Started")
                                        <td class="table-primary"><span class="label label-primary label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "OnHold")
                                        <td class="table-danger"><span class="label label-danger label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Cancelled")
                                        <td class="table-light"><span class="label label-light label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Offloading Point")
                                        <td class="table-accent"><span class="label label-accent label-wide">{{$trip->trip_status}}</span></td>
                                        @endif
                                        <td> <span class="label label-{{isset($trip->invoice) ? "success" : "warning"}}"> {{isset($trip->invoice) ? "issued" : "pending"}}</span></td>
                                        
                                        <td> <span class="label label-{{isset($trip->delivery_note->offloaded) ? "success" : "warning"}}"> {{isset($trip->delivery_note->offloaded) ? "submitted" : "pending"}}</span></td>
                                         <td><span class="label label-{{($trip->authorization == 'approved') ? 'success' : (($trip->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($trip->authorization == 'approved') ? 'approved' : (($trip->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                         <td class="w-10 line-height-35 table-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="{{route('trips.show', $trip->id)}}"><i class="fas fa-eye color-default"></i>View</a></li>
                                               
                                                </ul>
                                            </div>
                                            @include('trips.delete')

                                    </td>
                                      </tr>
                                      @elseif($trip->trip_status == "Loading Point")
                                      <tr  style="background-color: #adb5bd" >
                                        @php
                                        $from = App\Models\Destination::find($trip->from);
                                        $to = App\Models\Destination::find($trip->to);
                                     
                                    @endphp
                                    <td>{{ucfirst($trip->trip_number)}}</td>
                                    <td>{{ucfirst($trip->customer ? $trip->customer->name : "")}}</td>
                                    <td>{{ucfirst($trip->transporter ? $trip->transporter->name : "")}}</td>
                                    @if ($trip->horse)
                                    <td>{{ucfirst($trip->horse->horse_make ? $trip->horse->horse_make->name : "")}} {{ucfirst($trip->horse->horse_model ? $trip->horse->horse_model->name : "")}} ({{ucfirst($trip->horse ? $trip->horse->registration_number : "")}})</td>
                                    @else
                                        <td></td>
                                    @endif
                                    <td>{{$trip->loading_point ? $trip->loading_point->name : ""}}</td>
                                    <td>{{$trip->offloading_point ? $trip->offloading_point->name : ""}}</td>
                                    @if ($trip->trip_status == "Offloaded")
                                    <td class="table-success"><span class="label label-success label-wide">{{$trip->trip_status}}</span></td>
                                    @elseif($trip->trip_status == "Scheduled")
                                    <td class="table-warning" ><span class="label label-warning label-wide">{{$trip->trip_status}}</span></td>
                                    @elseif($trip->trip_status == "Loading Point")
                                    <td class="table-gray" ><span class="label label-gray label-wide">{{$trip->trip_status}}</span></td>
                                    @elseif($trip->trip_status == "Loaded")
                                    <td class="table-info"><span class="label label-info label-wide">{{$trip->trip_status}}</span></td>
                                    @elseif($trip->trip_status == "InTransit")
                                    <td class="table-primary"><span class="label label-primary label-wide">{{$trip->trip_status}}</span></td>
                                    @elseif($trip->trip_status == "Started")
                                    <td class="table-primary"><span class="label label-primary label-wide">{{$trip->trip_status}}</span></td>
                                    @elseif($trip->trip_status == "OnHold")
                                    <td class="table-danger"><span class="label label-danger label-wide">{{$trip->trip_status}}</span></td>
                                    @elseif($trip->trip_status == "Cancelled")
                                    <td class="table-light"><span class="label label-light label-wide">{{$trip->trip_status}}</span></td>
                                    @elseif($trip->trip_status == "Offloading Point")
                                    <td class="table-accent"><span class="label label-accent label-wide">{{$trip->trip_status}}</span></td>
                                    @endif
                                    <td> <span class="label label-{{isset($trip->invoice) ? "success" : "warning"}}"> {{isset($trip->invoice) ? "issued" : "pending"}}</span></td>
                                   
                                    <td> <span class="label label-{{isset($trip->delivery_note->offloaded) ? "success" : "warning"}}"> {{isset($trip->delivery_note->offloaded) ? "submitted" : "pending"}}</span></td>
                                     <td><span class="label label-{{($trip->authorization == 'approved') ? 'success' : (($trip->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($trip->authorization == 'approved') ? 'approved' : (($trip->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                     <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{route('trips.show', $trip->id)}}"><i class="fas fa-eye color-default"></i>View</a></li>
                                            </ul>
                                        </div>
                                        @include('trips.delete')

                                </td>
                                      </tr>
                                      @elseif($trip->trip_status == "Loaded")
                                      <tr  style="background-color: #5bc0de" >
                                        @php
                                            $from = App\Models\Destination::find($trip->from);
                                            $to = App\Models\Destination::find($trip->to);
                                         
                                        @endphp
                                        <td>{{ucfirst($trip->trip_number)}}</td>
                                        <td>{{ucfirst($trip->customer ? $trip->customer->name : "")}}</td>
                                        <td>{{ucfirst($trip->transporter ? $trip->transporter->name : "")}}</td>
                                        @if ($trip->horse)
                                        <td>{{ucfirst($trip->horse->horse_make ? $trip->horse->horse_make->name : "")}} {{ucfirst($trip->horse->horse_model ? $trip->horse->horse_model->name : "")}} ({{ucfirst($trip->horse ? $trip->horse->registration_number : "")}})</td>
                                        @else
                                            <td></td>
                                        @endif
                                        <td>{{$trip->loading_point ? $trip->loading_point->name : ""}}</td>
                                        <td>{{$trip->offloading_point ? $trip->offloading_point->name : ""}}</td>
                                        @if ($trip->trip_status == "Offloaded")
                                        <td class="table-success"><span class="label label-success label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Scheduled")
                                        <td class="table-warning" ><span class="label label-warning label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Loading Point")
                                        <td class="table-gray" ><span class="label label-gray label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Loaded")
                                        <td class="table-info"><span class="label label-info label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "InTransit")
                                        <td class="table-primary"><span class="label label-primary label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Started")
                                        <td class="table-primary"><span class="label label-primary label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "OnHold")
                                        <td class="table-danger"><span class="label label-danger label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Cancelled")
                                        <td class="table-light"><span class="label label-light label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Offloading Point")
                                        <td class="table-accent"><span class="label label-accent label-wide">{{$trip->trip_status}}</span></td>
                                        @endif
                                        <td> <span class="label label-{{isset($trip->invoice) ? "success" : "warning"}}"> {{isset($trip->invoice) ? "issued" : "pending"}}</span></td>
                                       
                                        <td> <span class="label label-{{isset($trip->delivery_note->offloaded) ? "success" : "warning"}}"> {{isset($trip->delivery_note->offloaded) ? "submitted" : "pending"}}</span></td>
                                         <td><span class="label label-{{($trip->authorization == 'approved') ? 'success' : (($trip->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($trip->authorization == 'approved') ? 'approved' : (($trip->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                         <td class="w-10 line-height-35 table-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="{{route('trips.show', $trip->id)}}"><i class="fas fa-eye color-default"></i>View</a></li>
                                                </ul>
                                            </div>
                                            @include('trips.delete')

                                    </td>
                                      </tr>
                                      @elseif($trip->trip_status == "InTransit")
                                      <tr  style="background-color: #1976D2">
                                        @php
                                            $from = App\Models\Destination::find($trip->from);
                                            $to = App\Models\Destination::find($trip->to);
                                         
                                        @endphp
                                        <td>{{ucfirst($trip->trip_number)}}</td>
                                        <td>{{ucfirst($trip->customer ? $trip->customer->name : "")}}</td>
                                        <td>{{ucfirst($trip->transporter ? $trip->transporter->name : "")}}</td>
                                        @if ($trip->horse)
                                        <td>{{ucfirst($trip->horse->horse_make ? $trip->horse->horse_make->name : "")}} {{ucfirst($trip->horse->horse_model ? $trip->horse->horse_model->name : "")}} ({{ucfirst($trip->horse ? $trip->horse->registration_number : "")}})</td>
                                        @else
                                            <td></td>
                                        @endif
                                        <td>{{$trip->loading_point ? $trip->loading_point->name : ""}}</td>
                                        <td>{{$trip->offloading_point ? $trip->offloading_point->name : ""}}</td>
                                       
                                        @if ($trip->trip_status == "Offloaded")
                                        <td class="table-success"><span class="label label-success label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Scheduled")
                                        <td class="table-warning" ><span class="label label-warning label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Loading Point")
                                        <td class="table-gray" ><span class="label label-gray label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Loaded")
                                        <td class="table-info"><span class="label label-info label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "InTransit")
                                        <td class="table-primary"><span class="label label-primary label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Started")
                                        <td class="table-primary"><span class="label label-primary label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "OnHold")
                                        <td class="table-danger"><span class="label label-danger label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Cancelled")
                                        <td class="table-light"><span class="label label-light label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Offloading Point")
                                        <td class="table-accent"><span class="label label-accent label-wide">{{$trip->trip_status}}</span></td>
                                        @endif
                                        <td> <span class="label label-{{isset($trip->invoice) ? "success" : "warning"}}"> {{isset($trip->invoice) ? "issued" : "pending"}}</span></td>
                                        <td> <span class="label label-{{isset($trip->delivery_note->offloaded) ? "success" : "warning"}}"> {{isset($trip->delivery_note->offloaded) ? "submitted" : "pending"}}</span></td>
                                         <td><span class="label label-{{($trip->authorization == 'approved') ? 'success' : (($trip->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($trip->authorization == 'approved') ? 'approved' : (($trip->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                         <td class="w-10 line-height-35 table-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="{{route('trips.show', $trip->id)}}"><i class="fas fa-eye color-default"></i>View</a></li>
                                                </ul>
                                            </div>
                                            @include('trips.delete')

                                    </td>
                                      </tr>
                                      @elseif($trip->trip_status == "Started")
                                      <tr  style="background-color: #1976D2">
                                        @php
                                            $from = App\Models\Destination::find($trip->from);
                                            $to = App\Models\Destination::find($trip->to);
                                         
                                        @endphp
                                        <td>{{ucfirst($trip->trip_number)}}</td>
                                        <td>{{ucfirst($trip->customer ? $trip->customer->name : "")}}</td>
                                        <td>{{ucfirst($trip->transporter ? $trip->transporter->name : "")}}</td>
                                        @if ($trip->horse)
                                        <td>{{ucfirst($trip->horse->horse_make ? $trip->horse->horse_make->name : "")}} {{ucfirst($trip->horse->horse_model ? $trip->horse->horse_model->name : "")}} ({{ucfirst($trip->horse ? $trip->horse->registration_number : "")}})</td>
                                        @else
                                            <td></td>
                                        @endif
                                        <td>{{$trip->loading_point ? $trip->loading_point->name : ""}}</td>
                                        <td>{{$trip->offloading_point ? $trip->offloading_point->name : ""}}</td>
                                       
                                        @if ($trip->trip_status == "Offloaded")
                                        <td class="table-success"><span class="label label-success label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Scheduled")
                                        <td class="table-warning" ><span class="label label-warning label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Loading Point")
                                        <td class="table-gray" ><span class="label label-gray label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Loaded")
                                        <td class="table-info"><span class="label label-info label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "InTransit")
                                        <td class="table-primary"><span class="label label-primary label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Started")
                                        <td class="table-primary"><span class="label label-primary label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "OnHold")
                                        <td class="table-danger"><span class="label label-danger label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Cancelled")
                                        <td class="table-light"><span class="label label-light label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Offloading Point")
                                        <td class="table-accent"><span class="label label-accent label-wide">{{$trip->trip_status}}</span></td>
                                        @endif
                                        <td> <span class="label label-{{isset($trip->invoice) ? "success" : "warning"}}"> {{isset($trip->invoice) ? "issued" : "pending"}}</span></td>
                                        <td> <span class="label label-{{isset($trip->delivery_note->offloaded) ? "success" : "warning"}}"> {{isset($trip->delivery_note->offloaded) ? "submitted" : "pending"}}</span></td>
                                         <td><span class="label label-{{($trip->authorization == 'approved') ? 'success' : (($trip->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($trip->authorization == 'approved') ? 'approved' : (($trip->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                         <td class="w-10 line-height-35 table-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="{{route('trips.show', $trip->id)}}"><i class="fas fa-eye color-default"></i>View</a></li>
                                                </ul>
                                            </div>
                                            @include('trips.delete')

                                    </td>
                                      </tr>
                                      @elseif($trip->trip_status == "OnHold")
                                      <tr  style="background-color: #d9534f">
                                        @php
                                            $from = App\Models\Destination::find($trip->from);
                                            $to = App\Models\Destination::find($trip->to);
                                         
                                        @endphp
                                        <td>{{ucfirst($trip->trip_number)}}</td>
                                        <td>{{ucfirst($trip->customer ? $trip->customer->name : "")}}</td>
                                        <td>{{ucfirst($trip->transporter ? $trip->transporter->name : "")}}</td>
                                        @if ($trip->horse)
                                        <td>{{ucfirst($trip->horse->horse_make ? $trip->horse->horse_make->name : "")}} {{ucfirst($trip->horse->horse_model ? $trip->horse->horse_model->name : "")}} ({{ucfirst($trip->horse ? $trip->horse->registration_number : "")}})</td>
                                        @else
                                            <td></td>
                                        @endif
                                        <td>{{$trip->loading_point ? $trip->loading_point->name : ""}}</td>
                                        <td>{{$trip->offloading_point ? $trip->offloading_point->name : ""}}</td>
                                      
                                        @if ($trip->trip_status == "Offloaded")
                                        <td class="table-success"><span class="label label-success label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Scheduled")
                                        <td class="table-warning" ><span class="label label-warning label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Loading Point")
                                        <td class="table-gray" ><span class="label label-gray label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Loaded")
                                        <td class="table-info"><span class="label label-info label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "InTransit")
                                        <td class="table-primary"><span class="label label-primary label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Started")
                                        <td class="table-primary"><span class="label label-primary label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "OnHold")
                                        <td class="table-danger"><span class="label label-danger label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Cancelled")
                                        <td class="table-light"><span class="label label-light label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Offloading Point")
                                        <td class="table-accent"><span class="label label-accent label-wide">{{$trip->trip_status}}</span></td>
                                        @endif
                                        <td> <span class="label label-{{isset($trip->invoice) ? "success" : "warning"}}"> {{isset($trip->invoice) ? "issued" : "pending"}}</span></td>
                                        <td> <span class="label label-{{isset($trip->delivery_note->offloaded) ? "success" : "warning"}}"> {{isset($trip->delivery_note->offloaded) ? "submitted" : "pending"}}</span></td>
                                         <td><span class="label label-{{($trip->authorization == 'approved') ? 'success' : (($trip->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($trip->authorization == 'approved') ? 'approved' : (($trip->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                         <td class="w-10 line-height-35 table-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="{{route('trips.show', $trip->id)}}"><i class="fas fa-eye color-default"></i>View</a></li>
                                                </ul>
                                            </div>
                                            @include('trips.delete')

                                    </td>
                                      </tr>
                                      @elseif($trip->trip_status == "Offloading Point")
                                      <tr  style="background-color: #82B1FF">
                                        @php
                                            $from = App\Models\Destination::find($trip->from);
                                            $to = App\Models\Destination::find($trip->to);
                                         
                                        @endphp
                                        <td>{{ucfirst($trip->trip_number)}}</td>
                                        <td>{{ucfirst($trip->customer ? $trip->customer->name : "")}}</td>
                                        <td>{{ucfirst($trip->transporter ? $trip->transporter->name : "")}}</td>
                                       @if ($trip->horse)
                                       <td>{{ucfirst($trip->horse->horse_make ? $trip->horse->horse_make->name : "")}} {{ucfirst($trip->horse->horse_model ? $trip->horse->horse_model->name : "")}} ({{ucfirst($trip->horse ? $trip->horse->registration_number : "")}})</td>
                                       @else
                                           <td></td>
                                       @endif
                                        <td>{{$trip->loading_point ? $trip->loading_point->name : ""}}</td>
                                        <td>{{$trip->offloading_point ? $trip->offloading_point->name : ""}}</td>
                                       
                                        @if ($trip->trip_status == "Offloaded")
                                        <td class="table-success"><span class="label label-success label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Scheduled")
                                        <td class="table-warning" ><span class="label label-warning label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Loading Point")
                                        <td class="table-gray" ><span class="label label-gray label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Loaded")
                                        <td class="table-info"><span class="label label-info label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "InTransit")
                                        <td class="table-primary"><span class="label label-primary label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Started")
                                        <td class="table-primary"><span class="label label-primary label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "OnHold")
                                        <td class="table-danger"><span class="label label-danger label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Cancelled")
                                        <td class="table-light"><span class="label label-light label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Offloading Point")
                                        <td class="table-accent"><span class="label label-accent label-wide">{{$trip->trip_status}}</span></td>
                                        @endif      
                                        <td> <span class="label label-{{isset($trip->invoice) ? "success" : "warning"}}"> {{isset($trip->invoice) ? "issued" : "pending"}}</span></td>
                                        <td> <span class="label label-{{isset($trip->delivery_note->offloaded) ? "success" : "warning"}}"> {{isset($trip->delivery_note->offloaded) ? "submitted" : "pending"}}</span></td>
                                         <td><span class="label label-{{($trip->authorization == 'approved') ? 'success' : (($trip->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($trip->authorization == 'approved') ? 'approved' : (($trip->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                         <td class="w-10 line-height-35 table-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="{{route('trips.show', $trip->id)}}"><i class="fas fa-eye color-default"></i>View</a></li>
                                                </ul>
                                            </div>
                                            @include('trips.delete')

                                    </td>
                                      </tr>
                                      @endif
                                      @empty
                                      <tr>
                                        <td colspan="11">
                                            <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                                No Trips Found ....
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
                                        @if (isset($trips))
                                            @if ($trips->count()>0)
                                                 {{ $trips->links() }} 
                                            @endif
                                           
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

          <!-- Modal -->


    </div>
