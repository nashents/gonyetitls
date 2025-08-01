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
                               
                         <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Shift
                                    </th>
                                     <th class="th-sm" style="width: 13%;">
                                        Duty
                                    </th>
                                    <th class="th-sm" style="width: 20%;">Narration
                                    </th>
                                   
                                    <th class="th-sm" style="width:120px;">
                                        Hours
                                        <hr style="margin-top:2px; margin-bottom:2px">
                                        Distance
                                    </th>
                                    <th class="th-sm" style="width: 5%;">
                                        Fuel
                                    </th>
                                    <th class="th-sm" style="width:120px;">
                                        F/C (H)
                                        <hr style="margin-top:2px; margin-bottom:2px">
                                        F/C (M)
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
                                         <strong>{{ucfirst($shift->type)}}  {{ucfirst($shift->for)}}</strong>
                                        <br>
                                         <small><strong>CreatedBy:</strong> {{$shift->user ? $shift->user->name : ""}} {{$shift->user ? $shift->user->surname : ""}}</small>
                                    </td>
                                      <td>
                                        <strong>Date:</strong> {{$shift->date}} <br>
                                        <strong>Start:</strong> {{$shift->shift_start_time}} <br>
                                        <strong>Close:</strong> {{$shift->shift_end_time}} <br>
                                    </td>
                                     <td>
                                        <strong>Customer:</strong> {{$shift->customer ? $shift->customer->name : ""}} <br>
                                        <strong>Cargo:</strong> {{$shift->cargo ? $shift->cargo->name : ""}} <br>
                                        @if ($shift->driver)
                                              <strong>Driver:</strong>  {{$shift->driver->employee ? $shift->driver->employee->name : ""}} {{$shift->driver->employee ? $shift->driver->employee->surname : ""}} <br>        
                                        @endif
                                        @if ($shift->horse)
                                              <strong>Horse:</strong>  {{$shift->horse->registration_number}} {{$shift->horse->fleet_number ? "(".$shift->horse->fleet_number.")" : ""}} <br>
                                        @elseif($shift->vehicle)
                                               <strong>Vehicle:</strong> {{$shift->horse->registration_number}} <br>
                                        @endif
                                       @if ($shift->loading_points->isNotEmpty() && $shift->loading_points->count()>0)
                                            <strong>Loading Points: </strong>
                                            @foreach ($shift->loading_points as $loading_point)
                                                {{ $loading_point->name }} @if (!$loop->last), @endif
                                            @endforeach
                                        @endif
                                        <br>
                                        @if ($shift->offloading_points->isNotEmpty())
                                            <strong>Offloading Points: </strong>
                                            @foreach ($shift->offloading_points as $offloading_point)
                                                {{ $offloading_point->name }}@if (!$loop->last), @endif
                                            @endforeach
                                        @endif
                                    </td>
                                  
                                     <td>
                                        {{$shift->hours ? $shift->hours." Hrs" : ""}}
                                            <hr style="margin-top:5px; margin-bottom:5px"> 
                                         {{$shift->actual_mileage ? $shift->actual_mileage." Kms" : ""}}
                                    </td>
                                    <td>
                                        {{$shift->total_fuel ? $shift->total_fuel. " l" : ""}}
                                    </td>
                                   <td>
                                        {{$shift->fuel_consumption_hours ? number_format($shift->fuel_consumption_hours,2)." H/l" : ""}}
                                            <hr style="margin-top:5px; margin-bottom:5px"> 
                                        {{$shift->fuel_consumption_mileage ? number_format($shift->fuel_consumption_mileage,2)." Km/l" : ""}}
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

          <!-- Modal -->


    </div>
