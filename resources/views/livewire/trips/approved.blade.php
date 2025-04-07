<div>
    <style>
        .modal-lg {
        max-width: 80%;
    }
    </style>
        <section class="section">
            <x-loading/>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel">
                            <div class="panel-heading">
                                <div>
                                    @include('includes.messages')
                                </div>
                               
                            </div>
                            <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                       
                                <div class="panel-title">
                                    <h5>Trips Management</h5>
                                    <div class="row">
                                    <div class="col-lg-3">
                                    <div class="input-group">
                                      <span class="input-group-addon">Filter By</span>
                                      <select wire:model.debounce.300ms="trip_filter" class="form-control" aria-label="..." >
                                        <option value="created_at">Trip Created At</option>
                                        <option value="start_date">Trip Start Date</option>
                                      </select>
                                    </div>
                                        <!-- /input-group -->
                                    </div>
                                    @if ($trip_filter == "created_at")
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
                                    @elseif ($trip_filter == "start_date")
                                    <div class="col-lg-2" style="margin-right: 42px">
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                      From
                                      </span>
                                      <input type="datetime-local" wire:model.debounce.300ms="from" wire:change="dateRange()" class="form-control" aria-label="...">
                                        </div>
                                        <!-- /input-group -->
                                    </div>
                                    <div class="col-lg-2" style="margin-left: 42px">
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                      To
                                      </span>
                                      <input type="datetime-local" wire:model.debounce.300ms="to" wire:change="dateRange()" class="form-control" aria-label="...">
                                        </div>
                                        <!-- /input-group -->
                                    </div>
                                    @endif
                                   
                                    <!-- /input-group -->
                                </div>
                              
                                </div>
                                <br>

                                <div class="col-md-5" style="float: right; padding-right:2px">
                                    <div class="form-group">
                                        <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search Using: Trip#,Transporter,Customer,Reg#,CreatedBy,POD#,StartDate...">
                                    </div>
                                </div>
                                   
                                <table  class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%" style=" width:100%; height:100%;">
                                    <thead >
                                        <th class="th-sm">Trip#
                                        </th>
                                        <th class="th-sm">CreatedBy
                                        </th>
                                        <th class="th-sm">Customer
                                        </th>
                                        <th class="th-sm">Transporter
                                        </th>
                                        <th class="th-sm">Horse
                                        </th>
                                        <th class="th-sm">From
                                        </th>
                                        <th class="th-sm">To
                                        </th>
                                        <th class="th-sm">Status
                                        </th>
                                     
                                        <th class="th-sm">Auth
                                        </th>
                                        <th class="th-sm">AuthBy
                                        </th>
                                        <th class="th-sm">Actions
                                        </th>

                                      </tr>
                                    </thead>
                                    @if (isset($trips))
                                    <tbody>
                                        @forelse ($trips as $trip)
                                        @php
                                            $from = App\Models\Destination::find($trip->from);
                                            $to = App\Models\Destination::find($trip->to);
                                            $user = App\Models\User::find($trip->authorized_by_id);
                                        @endphp
                                        @if ($trip->trip_status == "Offloaded")
                                      <tr style="background-color: #5cb85c">
                                        
                                          <td>
                                            {{ucfirst($trip->trip_number)}}
                                            @if ($trip->trip_ref)
                                            /{{$trip->trip_ref}}
                                            @endif
                                        </td>
                                        <td>  {{ $trip->user->employee ? $trip->user->employee->name : "" }} {{ $trip->user->employee ? $trip->user->employee->surname : "" }}</td>
                                   
                                        <td>{{ucfirst($trip->customer ? $trip->customer->name : "")}}</td>
                                        <td>{{ucfirst($trip->transporter ? $trip->transporter->name : "")}}</td>
                                        <td>
                                            @if ($trip->horse)
                                                Horse | {{ucfirst($trip->horse->horse_make ? $trip->horse->horse_make->name : "")}} {{ucfirst($trip->horse->horse_model ? $trip->horse->horse_model->name : "")}} {{ucfirst($trip->horse ? $trip->horse->registration_number : "")}} {{$trip->horse ? "| ".$trip->horse->fleet_number : ""}}
                                            @elseif ($trip->vehicle)
                                               Vehicle | {{ucfirst($trip->vehicle->vehicle_make ? $trip->vehicle->vehicle_make->name : "")}} {{ucfirst($trip->vehicle->vehicle_model ? $trip->vehicle->vehicle_model->name : "")}} {{ucfirst($trip->vehicle ? $trip->vehicle->registration_number : "")}}
                                            @endif
                                         
                                            @if (isset($trip->trailers) && $trip->trailers->count()>0)
                                            <hr>
                                                @foreach ($trip->trailers as $trailer)
                                                    {{ $trailer->registration_number }} 
                                                @endforeach
                                            @endif
                                        </td>
                                        
                                        <td>
                                            @if (isset($from))
                                            {{$from->country ? $from->country->name : ""}} {{ $from->city }}
                                            @endif
                                            @if ($trip->loading_point)
                                                @if (isset($from))
                                                <hr> 
                                                @endif
                                                {{ $trip->loading_point ? $trip->loading_point->name : "" }}
                                            @endif
                                           
                                        </td>
                                        <td>
                                            @if (isset($to))
                                            {{$to->country ? $to->country->name : ""}} {{ $to->city }}
                                            @endif
                                            @if ($trip->offloading_point)
                                                @if (isset($to))
                                                <hr>  
                                                @endif
                                                {{ $trip->offloading_point ? $trip->offloading_point->name : "" }}
                                            @endif
                                        </td>
                                       
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
                                        @elseif($trip->trip_status == "Offloading Point")
                                        <td class="table-accent"><span class="label label-accent label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Cancelled")
                                        <td class="table-accent"><span class="label label-light label-wide">{{$trip->trip_status}}</span></td>
                                        @endif
                                        
                                         <td><span class="label label-{{($trip->authorization == 'approved') ? 'success' : (($trip->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($trip->authorization == 'approved') ? 'approved' : (($trip->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                         <td>  {{ $user->employee ? $user->employee->name : "" }} {{ $user->employee ? $user->employee->surname : "" }}</td>
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
                                      
                                          <td>
                                            {{ucfirst($trip->trip_number)}}
                                            @if ($trip->trip_ref)
                                            /{{$trip->trip_ref}}
                                            @endif
                                        </td>
                                        <td>  {{ $trip->user->employee ? $trip->user->employee->name : "" }} {{ $trip->user->employee ? $trip->user->employee->surname : "" }}</td>
                                
                                        <td>{{ucfirst($trip->customer ? $trip->customer->name : "")}}</td>
                                        <td>{{ucfirst($trip->transporter ? $trip->transporter->name : "")}}</td>
                                        <td>
                                            @if ($trip->horse)
                                                Horse | {{ucfirst($trip->horse->horse_make ? $trip->horse->horse_make->name : "")}} {{ucfirst($trip->horse->horse_model ? $trip->horse->horse_model->name : "")}} {{ucfirst($trip->horse ? $trip->horse->registration_number : "")}} {{$trip->horse ? "| ".$trip->horse->fleet_number : ""}}
                                            @elseif ($trip->vehicle)
                                               Vehicle | {{ucfirst($trip->vehicle->vehicle_make ? $trip->vehicle->vehicle_make->name : "")}} {{ucfirst($trip->vehicle->vehicle_model ? $trip->vehicle->vehicle_model->name : "")}} {{ucfirst($trip->vehicle ? $trip->vehicle->registration_number : "")}}
                                            @endif
                                         
                                            @if (isset($trip->trailers) && $trip->trailers->count()>0)
                                            <hr>
                                                @foreach ($trip->trailers as $trailer)
                                                    {{ $trailer->registration_number }} 
                                                @endforeach
                                            @endif
                                        </td>
                                        <td>
                                            @if (isset($from))
                                            {{$from->country ? $from->country->name : ""}} {{ $from->city }}
                                            @endif
                                            @if ($trip->loading_point)
                                                @if (isset($from))
                                                <hr> 
                                                @endif
                                                {{ $trip->loading_point ? $trip->loading_point->name : "" }}
                                            @endif
                                           
                                        </td>
                                        <td>
                                            @if (isset($to))
                                            {{$to->country ? $to->country->name : ""}} {{ $to->city }}
                                            @endif
                                            @if ($trip->offloading_point)
                                                @if (isset($to))
                                                <hr>  
                                                @endif
                                                {{ $trip->offloading_point ? $trip->offloading_point->name : "" }}
                                            @endif
                                        </td>
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
                                        @elseif($trip->trip_status == "Offloading Point")
                                        <td class="table-accent"><span class="label label-accent label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Cancelled")
                                        <td class="table-accent"><span class="label label-light label-wide">{{$trip->trip_status}}</span></td>
                                        @endif
                                        
                                         <td><span class="label label-{{($trip->authorization == 'approved') ? 'success' : (($trip->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($trip->authorization == 'approved') ? 'approved' : (($trip->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                         <td>  {{ $user->employee ? $user->employee->name : "" }} {{ $user->employee ? $user->employee->surname : "" }}</td>
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
                                     
                                      <td>
                                            {{ucfirst($trip->trip_number)}}
                                            @if ($trip->trip_ref)
                                            /{{$trip->trip_ref}}
                                            @endif
                                        </td>
                                    <td>  {{ $trip->user->employee ? $trip->user->employee->name : "" }} {{ $trip->user->employee ? $trip->user->employee->surname : "" }}</td>
                                 
                                    <td>{{ucfirst($trip->customer ? $trip->customer->name : "")}}</td>
                                    <td>{{ucfirst($trip->transporter ? $trip->transporter->name : "")}}</td>
                                    <td>
                                        @if ($trip->horse)
                                            Horse | {{ucfirst($trip->horse->horse_make ? $trip->horse->horse_make->name : "")}} {{ucfirst($trip->horse->horse_model ? $trip->horse->horse_model->name : "")}} {{ucfirst($trip->horse ? $trip->horse->registration_number : "")}} {{$trip->horse ? "| ".$trip->horse->fleet_number : ""}}
                                        @elseif ($trip->vehicle)
                                           Vehicle | {{ucfirst($trip->vehicle->vehicle_make ? $trip->vehicle->vehicle_make->name : "")}} {{ucfirst($trip->vehicle->vehicle_model ? $trip->vehicle->vehicle_model->name : "")}} {{ucfirst($trip->vehicle ? $trip->vehicle->registration_number : "")}}
                                        @endif
                                     
                                        @if (isset($trip->trailers) && $trip->trailers->count()>0)
                                        <hr>
                                            @foreach ($trip->trailers as $trailer)
                                                {{ $trailer->registration_number }} 
                                            @endforeach
                                        @endif
                                    </td>
                                    <td>
                                        @if (isset($from))
                                        {{$from->country ? $from->country->name : ""}} {{ $from->city }}
                                        @endif
                                        @if ($trip->loading_point)
                                            @if (isset($from))
                                            <hr> 
                                            @endif
                                            {{ $trip->loading_point ? $trip->loading_point->name : "" }}
                                        @endif
                                       
                                    </td>
                                    <td>
                                        @if (isset($to))
                                        {{$to->country ? $to->country->name : ""}} {{ $to->city }}
                                        @endif
                                        @if ($trip->offloading_point)
                                            @if (isset($to))
                                            <hr>  
                                            @endif
                                            {{ $trip->offloading_point ? $trip->offloading_point->name : "" }}
                                        @endif
                                    </td>
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
                                    @elseif($trip->trip_status == "Offloading Point")
                                    <td class="table-accent"><span class="label label-accent label-wide">{{$trip->trip_status}}</span></td>
                                    @elseif($trip->trip_status == "Cancelled")
                                    <td class="table-accent"><span class="label label-light label-wide">{{$trip->trip_status}}</span></td>
                                    @endif
                                   
                                     <td><span class="label label-{{($trip->authorization == 'approved') ? 'success' : (($trip->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($trip->authorization == 'approved') ? 'approved' : (($trip->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                     <td>  {{ $user->employee ? $user->employee->name : "" }} {{ $user->employee ? $user->employee->surname : "" }}</td>
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
                                       
                                          <td>
                                            {{ucfirst($trip->trip_number)}}
                                            @if ($trip->trip_ref)
                                            /{{$trip->trip_ref}}
                                            @endif
                                        </td>
                                        <td>  {{ $trip->user->employee ? $trip->user->employee->name : "" }} {{ $trip->user->employee ? $trip->user->employee->surname : "" }}</td>
                                 
                                        <td>{{ucfirst($trip->customer ? $trip->customer->name : "")}}</td>
                                        <td>{{ucfirst($trip->transporter ? $trip->transporter->name : "")}}</td>
                                        <td>
                                            @if ($trip->horse)
                                                Horse | {{ucfirst($trip->horse->horse_make ? $trip->horse->horse_make->name : "")}} {{ucfirst($trip->horse->horse_model ? $trip->horse->horse_model->name : "")}} {{ucfirst($trip->horse ? $trip->horse->registration_number : "")}} {{$trip->horse ? "| ".$trip->horse->fleet_number : ""}}
                                            @elseif ($trip->vehicle)
                                               Vehicle | {{ucfirst($trip->vehicle->vehicle_make ? $trip->vehicle->vehicle_make->name : "")}} {{ucfirst($trip->vehicle->vehicle_model ? $trip->vehicle->vehicle_model->name : "")}} {{ucfirst($trip->vehicle ? $trip->vehicle->registration_number : "")}}
                                            @endif
                                         
                                            @if (isset($trip->trailers) && $trip->trailers->count()>0)
                                            <hr>
                                                @foreach ($trip->trailers as $trailer)
                                                    {{ $trailer->registration_number }} 
                                                @endforeach
                                            @endif
                                        </td>
                                        <td>
                                            @if (isset($from))
                                            {{$from->country ? $from->country->name : ""}} {{ $from->city }}
                                            @endif
                                            @if ($trip->loading_point)
                                                @if (isset($from))
                                                <hr> 
                                                @endif
                                                {{ $trip->loading_point ? $trip->loading_point->name : "" }}
                                            @endif
                                           
                                        </td>
                                        <td>
                                            @if (isset($to))
                                            {{$to->country ? $to->country->name : ""}} {{ $to->city }}
                                            @endif
                                            @if ($trip->offloading_point)
                                                @if (isset($to))
                                                <hr>  
                                                @endif
                                                {{ $trip->offloading_point ? $trip->offloading_point->name : "" }}
                                            @endif
                                        </td>
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
                                        @elseif($trip->trip_status == "Offloading Point")
                                        <td class="table-accent"><span class="label label-accent label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Cancelled")
                                        <td class="table-accent"><span class="label label-light label-wide">{{$trip->trip_status}}</span></td>
                                        @endif
                                        
                                        <td>  {{ $user->employee ? $user->employee->name : "" }} {{ $user->employee ? $user->employee->surname : "" }}</td> 
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
                                
                                          <td>
                                            {{ucfirst($trip->trip_number)}}
                                            @if ($trip->trip_ref)
                                            /{{$trip->trip_ref}}
                                            @endif
                                        </td>
                                        <td>  {{ $trip->user->employee ? $trip->user->employee->name : "" }} {{ $trip->user->employee ? $trip->user->employee->surname : "" }}</td>
                                    
                                        <td>{{ucfirst($trip->customer ? $trip->customer->name : "")}}</td>
                                        <td>{{ucfirst($trip->transporter ? $trip->transporter->name : "")}}</td>
                                        <td>
                                            @if ($trip->horse)
                                                Horse | {{ucfirst($trip->horse->horse_make ? $trip->horse->horse_make->name : "")}} {{ucfirst($trip->horse->horse_model ? $trip->horse->horse_model->name : "")}} {{ucfirst($trip->horse ? $trip->horse->registration_number : "")}} {{$trip->horse ? "| ".$trip->horse->fleet_number : ""}}
                                            @elseif ($trip->vehicle)
                                               Vehicle | {{ucfirst($trip->vehicle->vehicle_make ? $trip->vehicle->vehicle_make->name : "")}} {{ucfirst($trip->vehicle->vehicle_model ? $trip->vehicle->vehicle_model->name : "")}} {{ucfirst($trip->vehicle ? $trip->vehicle->registration_number : "")}}
                                            @endif
                                         
                                            @if (isset($trip->trailers) && $trip->trailers->count()>0)
                                            <hr>
                                                @foreach ($trip->trailers as $trailer)
                                                    {{ $trailer->registration_number }} 
                                                @endforeach
                                            @endif
                                        </td>
                                        <td>
                                            @if (isset($from))
                                            {{$from->country ? $from->country->name : ""}} {{ $from->city }}
                                            @endif
                                            @if ($trip->loading_point)
                                                @if (isset($from))
                                                <hr> 
                                                @endif
                                                {{ $trip->loading_point ? $trip->loading_point->name : "" }}
                                            @endif
                                           
                                        </td>
                                        <td>
                                            @if (isset($to))
                                            {{$to->country ? $to->country->name : ""}} {{ $to->city }}
                                            @endif
                                            @if ($trip->offloading_point)
                                                @if (isset($to))
                                                <hr>  
                                                @endif
                                                {{ $trip->offloading_point ? $trip->offloading_point->name : "" }}
                                            @endif
                                        </td>
                                       
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
                                        @elseif($trip->trip_status == "Offloading Point")
                                        <td class="table-accent"><span class="label label-accent label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Cancelled")
                                        <td class="table-accent"><span class="label label-light label-wide">{{$trip->trip_status}}</span></td>
                                        @endif
                                       
                                         <td><span class="label label-{{($trip->authorization == 'approved') ? 'success' : (($trip->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($trip->authorization == 'approved') ? 'approved' : (($trip->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                         <td>  {{ $user->employee ? $user->employee->name : "" }} {{ $user->employee ? $user->employee->surname : "" }}</td>
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
                                
                                          <td>
                                            {{ucfirst($trip->trip_number)}}
                                            @if ($trip->trip_ref)
                                            /{{$trip->trip_ref}}
                                            @endif
                                        </td>
                                        <td>  {{ $trip->user->employee ? $trip->user->employee->name : "" }} {{ $trip->user->employee ? $trip->user->employee->surname : "" }}</td>
                                    
                                        <td>{{ucfirst($trip->customer ? $trip->customer->name : "")}}</td>
                                        <td>{{ucfirst($trip->transporter ? $trip->transporter->name : "")}}</td>
                                        <td>
                                            @if ($trip->horse)
                                                Horse | {{ucfirst($trip->horse->horse_make ? $trip->horse->horse_make->name : "")}} {{ucfirst($trip->horse->horse_model ? $trip->horse->horse_model->name : "")}} {{ucfirst($trip->horse ? $trip->horse->registration_number : "")}} {{$trip->horse ? "| ".$trip->horse->fleet_number : ""}}
                                            @elseif ($trip->vehicle)
                                               Vehicle | {{ucfirst($trip->vehicle->vehicle_make ? $trip->vehicle->vehicle_make->name : "")}} {{ucfirst($trip->vehicle->vehicle_model ? $trip->vehicle->vehicle_model->name : "")}} {{ucfirst($trip->vehicle ? $trip->vehicle->registration_number : "")}}
                                            @endif
                                         
                                            @if (isset($trip->trailers) && $trip->trailers->count()>0)
                                            <hr>
                                                @foreach ($trip->trailers as $trailer)
                                                    {{ $trailer->registration_number }} 
                                                @endforeach
                                            @endif
                                        </td>
                                        <td>
                                            @if (isset($from))
                                            {{$from->country ? $from->country->name : ""}} {{ $from->city }}
                                            @endif
                                            @if ($trip->loading_point)
                                                @if (isset($from))
                                                <hr> 
                                                @endif
                                                {{ $trip->loading_point ? $trip->loading_point->name : "" }}
                                            @endif
                                           
                                        </td>
                                        <td>
                                            @if (isset($to))
                                            {{$to->country ? $to->country->name : ""}} {{ $to->city }}
                                            @endif
                                            @if ($trip->offloading_point)
                                                @if (isset($to))
                                                <hr>  
                                                @endif
                                                {{ $trip->offloading_point ? $trip->offloading_point->name : "" }}
                                            @endif
                                        </td>
                                       
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
                                        @elseif($trip->trip_status == "Offloading Point")
                                        <td class="table-accent"><span class="label label-accent label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Cancelled")
                                        <td class="table-accent"><span class="label label-light label-wide">{{$trip->trip_status}}</span></td>
                                        @endif
                                       
                                         <td><span class="label label-{{($trip->authorization == 'approved') ? 'success' : (($trip->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($trip->authorization == 'approved') ? 'approved' : (($trip->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                         <td>  {{ $user->employee ? $user->employee->name : "" }} {{ $user->employee ? $user->employee->surname : "" }}</td>
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
                                       
                                          <td>
                                            {{ucfirst($trip->trip_number)}}
                                            @if ($trip->trip_ref)
                                            /{{$trip->trip_ref}}
                                            @endif
                                        </td>
                                        <td>  {{ $trip->user->employee ? $trip->user->employee->name : "" }} {{ $trip->user->employee ? $trip->user->employee->surname : "" }}</td>
                                    
                                        <td>{{ucfirst($trip->customer ? $trip->customer->name : "")}}</td>
                                        <td>{{ucfirst($trip->transporter ? $trip->transporter->name : "")}}</td>
                                        <td>
                                            @if ($trip->horse)
                                                Horse | {{ucfirst($trip->horse->horse_make ? $trip->horse->horse_make->name : "")}} {{ucfirst($trip->horse->horse_model ? $trip->horse->horse_model->name : "")}} {{ucfirst($trip->horse ? $trip->horse->registration_number : "")}} {{$trip->horse ? "| ".$trip->horse->fleet_number : ""}}
                                            @elseif ($trip->vehicle)
                                               Vehicle | {{ucfirst($trip->vehicle->vehicle_make ? $trip->vehicle->vehicle_make->name : "")}} {{ucfirst($trip->vehicle->vehicle_model ? $trip->vehicle->vehicle_model->name : "")}} {{ucfirst($trip->vehicle ? $trip->vehicle->registration_number : "")}}
                                            @endif
                                         
                                            @if (isset($trip->trailers) && $trip->trailers->count()>0)
                                            <hr>
                                                @foreach ($trip->trailers as $trailer)
                                                    {{ $trailer->registration_number }} 
                                                @endforeach
                                            @endif
                                        </td>
                                        <td>
                                            @if (isset($from))
                                            {{$from->country ? $from->country->name : ""}} {{ $from->city }}
                                            @endif
                                            @if ($trip->loading_point)
                                                @if (isset($from))
                                                <hr> 
                                                @endif
                                                {{ $trip->loading_point ? $trip->loading_point->name : "" }}
                                            @endif
                                           
                                        </td>
                                        <td>
                                            @if (isset($to))
                                            {{$to->country ? $to->country->name : ""}} {{ $to->city }}
                                            @endif
                                            @if ($trip->offloading_point)
                                                @if (isset($to))
                                                <hr>  
                                                @endif
                                                {{ $trip->offloading_point ? $trip->offloading_point->name : "" }}
                                            @endif
                                        </td>
                                      
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
                                        @elseif($trip->trip_status == "Offloading Point")
                                        <td class="table-accent"><span class="label label-accent label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Cancelled")
                                        <td class="table-accent"><span class="label label-light label-wide">{{$trip->trip_status}}</span></td>
                                        @endif
                                       
                                         <td><span class="label label-{{($trip->authorization == 'approved') ? 'success' : (($trip->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($trip->authorization == 'approved') ? 'approved' : (($trip->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                         <td>  {{ $user->employee ? $user->employee->name : "" }} {{ $user->employee ? $user->employee->surname : "" }}</td>
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
                                      @elseif($trip->trip_status == "Cancelled")
                                      <tr  style="background-color: #C4A484">
                                       
                                          <td>
                                            {{ucfirst($trip->trip_number)}}
                                            @if ($trip->trip_ref)
                                            /{{$trip->trip_ref}}
                                            @endif
                                        </td>
                                        <td>  {{ $trip->user->employee ? $trip->user->employee->name : "" }} {{ $trip->user->employee ? $trip->user->employee->surname : "" }}</td>
                                    
                                        <td>{{ucfirst($trip->customer ? $trip->customer->name : "")}}</td>
                                        <td>{{ucfirst($trip->transporter ? $trip->transporter->name : "")}}</td>
                                        <td>
                                            @if ($trip->horse)
                                                Horse | {{ucfirst($trip->horse->horse_make ? $trip->horse->horse_make->name : "")}} {{ucfirst($trip->horse->horse_model ? $trip->horse->horse_model->name : "")}} {{ucfirst($trip->horse ? $trip->horse->registration_number : "")}} {{$trip->horse ? "| ".$trip->horse->fleet_number : ""}}
                                            @elseif ($trip->vehicle)
                                               Vehicle | {{ucfirst($trip->vehicle->vehicle_make ? $trip->vehicle->vehicle_make->name : "")}} {{ucfirst($trip->vehicle->vehicle_model ? $trip->vehicle->vehicle_model->name : "")}} {{ucfirst($trip->vehicle ? $trip->vehicle->registration_number : "")}}
                                            @endif
                                         
                                            @if (isset($trip->trailers) && $trip->trailers->count()>0)
                                            <hr>
                                                @foreach ($trip->trailers as $trailer)
                                                    {{ $trailer->registration_number }} 
                                                @endforeach
                                            @endif
                                        </td>
                                        <td>
                                            @if (isset($from))
                                            {{$from->country ? $from->country->name : ""}} {{ $from->city }}
                                            @endif
                                            @if ($trip->loading_point)
                                                @if (isset($from))
                                                <hr> 
                                                @endif
                                                {{ $trip->loading_point ? $trip->loading_point->name : "" }}
                                            @endif
                                           
                                        </td>
                                        <td>
                                            @if (isset($to))
                                            {{$to->country ? $to->country->name : ""}} {{ $to->city }}
                                            @endif
                                            @if ($trip->offloading_point)
                                                @if (isset($to))
                                                <hr>  
                                                @endif
                                                {{ $trip->offloading_point ? $trip->offloading_point->name : "" }}
                                            @endif
                                        </td>
                                      
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
                                        @elseif($trip->trip_status == "Offloading Point")
                                        <td class="table-accent"><span class="label label-accent label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Cancelled")
                                        <td class="table-accent"><span class="label label-light label-wide">{{$trip->trip_status}}</span></td>
                                        @endif
                                       
                                         <td><span class="label label-{{($trip->authorization == 'approved') ? 'success' : (($trip->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($trip->authorization == 'approved') ? 'approved' : (($trip->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                         <td>  {{ $user->employee ? $user->employee->name : "" }} {{ $user->employee ? $user->employee->surname : "" }}</td>
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
                                      
                                          <td>
                                            {{ucfirst($trip->trip_number)}}
                                            @if ($trip->trip_ref)
                                            /{{$trip->trip_ref}}
                                            @endif
                                        </td>
                                        <td>  {{ $trip->user->employee ? $trip->user->employee->name : "" }} {{ $trip->user->employee ? $trip->user->employee->surname : "" }}</td>
                                     
                                        <td>{{ucfirst($trip->customer ? $trip->customer->name : "")}}</td>
                                        <td>{{ucfirst($trip->transporter ? $trip->transporter->name : "")}}</td>
                                        <td>
                                            @if ($trip->horse)
                                                Horse | {{ucfirst($trip->horse->horse_make ? $trip->horse->horse_make->name : "")}} {{ucfirst($trip->horse->horse_model ? $trip->horse->horse_model->name : "")}} {{ucfirst($trip->horse ? $trip->horse->registration_number : "")}} {{$trip->horse ? "| ".$trip->horse->fleet_number : ""}}
                                            @elseif ($trip->vehicle)
                                               Vehicle | {{ucfirst($trip->vehicle->vehicle_make ? $trip->vehicle->vehicle_make->name : "")}} {{ucfirst($trip->vehicle->vehicle_model ? $trip->vehicle->vehicle_model->name : "")}} {{ucfirst($trip->vehicle ? $trip->vehicle->registration_number : "")}}
                                            @endif
                                         
                                            @if (isset($trip->trailers) && $trip->trailers->count()>0)
                                            <hr>
                                                @foreach ($trip->trailers as $trailer)
                                                    {{ $trailer->registration_number }} 
                                                @endforeach
                                            @endif
                                        </td>
                                        <td>
                                            @if (isset($from))
                                            {{$from->country ? $from->country->name : ""}} {{ $from->city }}
                                            @endif
                                            @if ($trip->loading_point)
                                                @if (isset($from))
                                                <hr> 
                                                @endif
                                                {{ $trip->loading_point ? $trip->loading_point->name : "" }}
                                            @endif
                                           
                                        </td>
                                        <td>
                                            @if (isset($to))
                                            {{$to->country ? $to->country->name : ""}} {{ $to->city }}
                                            @endif
                                            @if ($trip->offloading_point)
                                                @if (isset($to))
                                                <hr>  
                                                @endif
                                                {{ $trip->offloading_point ? $trip->offloading_point->name : "" }}
                                            @endif
                                        </td>
                                       
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
                                        @elseif($trip->trip_status == "Offloading Point")
                                        <td class="table-accent"><span class="label label-accent label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Cancelled")
                                        <td class="table-accent"><span class="label label-light label-wide">{{$trip->trip_status}}</span></td>
                                        @endif      
                                       
                                         <td><span class="label label-{{($trip->authorization == 'approved') ? 'success' : (($trip->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($trip->authorization == 'approved') ? 'approved' : (($trip->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                         <td>  {{ $user->employee ? $user->employee->name : "" }} {{ $user->employee ? $user->employee->surname : "" }}</td>
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
                                        <td colspan="13">
                                            <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                                No Approved Trips Found ....
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
                                            {{ $trips->links() }} 
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
        <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="authorizationModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modal4Label"><i class="fas fa-gavel"></i> Authorize Trip<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                    </div>
                    <form wire:submit.prevent="update()" >
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Authorize</label>
                        <select class="form-control" wire:model.debounce.300ms="authorize" required>
                            <option value="">Select Decision</option>
                            <option value="approved">Approve</option>
                            <option value="rejected">Reject</option>
                        </select>
                            @error('authorize') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="reason">Comments</label>
                           <textarea wire:model.debounce.300="comments" class="form-control" cols="30" rows="5"></textarea>
                            @error('comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
