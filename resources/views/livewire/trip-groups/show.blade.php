<div>
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

                            <div class="panel-title">
                                <a href="" data-toggle="modal" data-target="#addTripsModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Trips</a>
                                <a href="#" wire:click="exportTripsTrackingExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table class="table  table-striped table-bordered table-sm table-responsive sortable" cellspacing="0" width="100%" style=" width:100%; height:100%;">
                                <caption>Tracking Group Name: {{ $trip_group->name }}</caption>
                                <thead >
                                    <th class="th-sm">Trip#
                                    </th>
                                    <th class="th-sm">Customer
                                    </th>
                                    <th class="th-sm">Transporter
                                    </th>
                                    <th class="th-sm">MOT
                                    </th>
                                    <th class="th-sm">Departure
                                    </th>
                                    <th class="th-sm">Offloaded
                                    </th>
                                    <th class="th-sm">From
                                    </th>
                                    <th class="th-sm">To
                                    </th>
                                    <th class="th-sm">Status
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
                                    @endphp
                                  
                                    @if ($trip->trip_status == "Offloaded")
                                  <tr style="background-color: #5cb85c">
                                  
                                    <td>{{ucfirst($trip->trip_number)}}</td>
                                    <td>{{ucfirst($trip->customer ? $trip->customer->name : "")}}</td>
                                    <td>{{ucfirst($trip->transporter ? $trip->transporter->name : "")}}</td>
                                    <td>
                                        @if ($trip->horse)
                                            Horse | {{ucfirst($trip->horse->horse_make ? $trip->horse->horse_make->name : "")}} {{ucfirst($trip->horse->horse_model ? $trip->horse->horse_model->name : "")}} {{ucfirst($trip->horse ? $trip->horse->registration_number : "")}} {{$trip->horse ? "| ".$trip->horse->fleet_number : ""}}
                                        @elseif ($trip->vehicle)
                                            Vehicle | {{ucfirst($trip->vehicle->vehicle_make ? $trip->vehicle->vehicle_make->name : "")}} {{ucfirst($trip->vehicle->vehicle_model ? $trip->vehicle->vehicle_model->name : "")}} {{ucfirst($trip->vehicle ? $trip->vehicle->registration_number : "")}}
                                        @endif
                                    </td>
                                    <td>  {{ $trip->start_date }}</td>
                                    <td>
                                        @if ($trip->delivery_note)
                                      
                                       {{$trip->delivery_note->offloaded_date}}
                                        
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
                                    @elseif($trip->trip_status == "OnHold")
                                    <td class="table-danger"><span class="label label-danger label-wide">{{$trip->trip_status}}</span></td>
                                    @elseif($trip->trip_status == "Offloading Point")
                                    <td class="table-accent"><span class="label label-accent label-wide">{{$trip->trip_status}}</span></td>
                                    @endif
                           
                                     
                                     <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{route('trips.show', $trip->id)}}"><i class="fas fa-eye color-default"></i>View</a></li>
                                                <li><a href="" wire:click="removeTrip({{$trip->id}})"><i class="fas fa-remove color-danger"></i>Remove</a></li>
                                            </ul>
                                        </div>
                                        @include('trips.delete')

                                </td>
                                  </tr>
                                  @elseif($trip->trip_status == "Scheduled")
                                  <tr style="background-color: #f0ad4e" >
                                
                                    <td>{{ucfirst($trip->trip_number)}}</td>
                                    <td>{{ucfirst($trip->customer ? $trip->customer->name : "")}}</td>
                                    <td>{{ucfirst($trip->transporter ? $trip->transporter->name : "")}}</td>
                                    <td>
                                        @if ($trip->horse)
                                            Horse | {{ucfirst($trip->horse->horse_make ? $trip->horse->horse_make->name : "")}} {{ucfirst($trip->horse->horse_model ? $trip->horse->horse_model->name : "")}} {{ucfirst($trip->horse ? $trip->horse->registration_number : "")}} {{$trip->horse ? "| ".$trip->horse->fleet_number : ""}}
                                        @elseif ($trip->vehicle)
                                           Vehicle | {{ucfirst($trip->vehicle->vehicle_make ? $trip->vehicle->vehicle_make->name : "")}} {{ucfirst($trip->vehicle->vehicle_model ? $trip->vehicle->vehicle_model->name : "")}} {{ucfirst($trip->vehicle ? $trip->vehicle->registration_number : "")}}
                                        @endif
                                    </td>
                                     <td>  {{ $trip->start_date }}</td>
                                     <td>
                                        @if ($trip->delivery_note)
                                       {{$trip->delivery_note->offloaded_date}}
                                        
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
                                    @elseif($trip->trip_status == "OnHold")
                                    <td class="table-danger"><span class="label label-danger label-wide">{{$trip->trip_status}}</span></td>
                                    @elseif($trip->trip_status == "Offloading Point")
                                    <td class="table-accent"><span class="label label-accent label-wide">{{$trip->trip_status}}</span></td>
                                    @endif
                
                                     
                                     <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{route('trips.show', $trip->id)}}"><i class="fas fa-eye color-default"></i>View</a></li>
                                                <li><a href="" wire:click="removeTrip({{$trip->id}})"><i class="fas fa-remove color-danger"></i>Remove</a></li>
                                            </ul>
                                        </div>
                                        @include('trips.delete')

                                </td>
                                  </tr>
                                  @elseif($trip->trip_status == "Loading Point")
                                  <tr  style="background-color: #adb5bd" >
                                  
                                <td>{{ucfirst($trip->trip_number)}}</td>
                                <td>{{ucfirst($trip->customer ? $trip->customer->name : "")}}</td>
                                <td>{{ucfirst($trip->transporter ? $trip->transporter->name : "")}}</td>
                                <td>
                                    @if ($trip->horse)
                                        Horse | {{ucfirst($trip->horse->horse_make ? $trip->horse->horse_make->name : "")}} {{ucfirst($trip->horse->horse_model ? $trip->horse->horse_model->name : "")}} {{ucfirst($trip->horse ? $trip->horse->registration_number : "")}} {{$trip->horse ? "| ".$trip->horse->fleet_number : ""}}
                                    @elseif ($trip->vehicle)
                                       Vehicle | {{ucfirst($trip->vehicle->vehicle_make ? $trip->vehicle->vehicle_make->name : "")}} {{ucfirst($trip->vehicle->vehicle_model ? $trip->vehicle->vehicle_model->name : "")}} {{ucfirst($trip->vehicle ? $trip->vehicle->registration_number : "")}}
                                    @endif
                                </td>
                                 <td>  {{ $trip->start_date }}</td>
                                 <td>
                                    @if ($trip->delivery_note)
                                    {{$trip->delivery_note->offloaded_date}}
                                    
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
                                @elseif($trip->trip_status == "OnHold")
                                <td class="table-danger"><span class="label label-danger label-wide">{{$trip->trip_status}}</span></td>
                                @elseif($trip->trip_status == "Offloading Point")
                                <td class="table-accent"><span class="label label-accent label-wide">{{$trip->trip_status}}</span></td>
                                @endif
                       
                                 
                                 <td class="w-10 line-height-35 table-dropdown">
                                    <div class="dropdown">
                                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fa fa-bars"></i>
                                            <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a href="{{route('trips.show', $trip->id)}}"><i class="fas fa-eye color-default"></i>View</a></li>
                                            <li><a href="" wire:click="removeTrip({{$trip->id}})"><i class="fas fa-remove color-danger"></i>Remove</a></li>
                                        </ul>
                                    </div>
                                    @include('trips.delete')

                            </td>
                                  </tr>
                                  @elseif($trip->trip_status == "Loaded")
                                  <tr  style="background-color: #5bc0de" >
                                  
                                    <td>{{ucfirst($trip->trip_number)}}</td>
                                    <td>{{ucfirst($trip->customer ? $trip->customer->name : "")}}</td>
                                    <td>{{ucfirst($trip->transporter ? $trip->transporter->name : "")}}</td>
                                    <td>
                                        @if ($trip->horse)
                                            Horse | {{ucfirst($trip->horse->horse_make ? $trip->horse->horse_make->name : "")}} {{ucfirst($trip->horse->horse_model ? $trip->horse->horse_model->name : "")}} {{ucfirst($trip->horse ? $trip->horse->registration_number : "")}} {{$trip->horse ? "| ".$trip->horse->fleet_number : ""}}
                                        @elseif ($trip->vehicle)
                                           Vehicle | {{ucfirst($trip->vehicle->vehicle_make ? $trip->vehicle->vehicle_make->name : "")}} {{ucfirst($trip->vehicle->vehicle_model ? $trip->vehicle->vehicle_model->name : "")}} {{ucfirst($trip->vehicle ? $trip->vehicle->registration_number : "")}}
                                        @endif
                                    </td>
                                     <td>  {{ $trip->start_date }}</td>
                                     <td>
                                        @if ($trip->delivery_note)
                                       {{$trip->delivery_note->offloaded_date}}
                                        
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
                                    @elseif($trip->trip_status == "OnHold")
                                    <td class="table-danger"><span class="label label-danger label-wide">{{$trip->trip_status}}</span></td>
                                    @elseif($trip->trip_status == "Offloading Point")
                                    <td class="table-accent"><span class="label label-accent label-wide">{{$trip->trip_status}}</span></td>
                                    @endif
                         
                                     
                                     <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{route('trips.show', $trip->id)}}"><i class="fas fa-eye color-default"></i>View</a></li>
                                                <li><a href="" wire:click="removeTrip({{$trip->id}})"><i class="fas fa-remove color-danger"></i>Remove</a></li>
                                            </ul>
                                        </div>
                                        @include('trips.delete')

                                </td>
                                  </tr>
                                  @elseif($trip->trip_status == "InTransit")
                                  <tr  style="background-color: #1976D2">
                                   
                                    <td>{{ucfirst($trip->trip_number)}}</td>
                                    <td>{{ucfirst($trip->customer ? $trip->customer->name : "")}}</td>
                                    <td>{{ucfirst($trip->transporter ? $trip->transporter->name : "")}}</td>
                                    <td>
                                        @if ($trip->horse)
                                            Horse | {{ucfirst($trip->horse->horse_make ? $trip->horse->horse_make->name : "")}} {{ucfirst($trip->horse->horse_model ? $trip->horse->horse_model->name : "")}} {{ucfirst($trip->horse ? $trip->horse->registration_number : "")}} {{$trip->horse ? "| ".$trip->horse->fleet_number : ""}}
                                        @elseif ($trip->vehicle)
                                           Vehicle | {{ucfirst($trip->vehicle->vehicle_make ? $trip->vehicle->vehicle_make->name : "")}} {{ucfirst($trip->vehicle->vehicle_model ? $trip->vehicle->vehicle_model->name : "")}} {{ucfirst($trip->vehicle ? $trip->vehicle->registration_number : "")}}
                                        @endif
                                    </td>
                                     <td>  {{ $trip->start_date }}</td>
                                     <td>
                                        @if ($trip->delivery_note)
                                       {{$trip->delivery_note->offloaded_date}}
                                        
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
                                    @elseif($trip->trip_status == "OnHold")
                                    <td class="table-danger"><span class="label label-danger label-wide">{{$trip->trip_status}}</span></td>
                                    @elseif($trip->trip_status == "Offloading Point")
                                    <td class="table-accent"><span class="label label-accent label-wide">{{$trip->trip_status}}</span></td>
                                    @endif
                            
                                     
                                     <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{route('trips.show', $trip->id)}}"><i class="fas fa-eye color-default"></i>View</a></li>
                                                <li><a href="" wire:click="removeTrip({{$trip->id}})"><i class="fas fa-remove color-danger"></i>Remove</a></li>
                                            </ul>
                                        </div>
                                        @include('trips.delete')

                                </td>
                                  </tr>
                                  @elseif($trip->trip_status == "OnHold")
                                  <tr  style="background-color: #d9534f">
                                  
                                    <td>{{ucfirst($trip->trip_number)}}</td>
                                    <td>{{ucfirst($trip->customer ? $trip->customer->name : "")}}</td>
                                    <td>{{ucfirst($trip->transporter ? $trip->transporter->name : "")}}</td>
                                    <td>
                                        @if ($trip->horse)
                                            Horse | {{ucfirst($trip->horse->horse_make ? $trip->horse->horse_make->name : "")}} {{ucfirst($trip->horse->horse_model ? $trip->horse->horse_model->name : "")}} {{ucfirst($trip->horse ? $trip->horse->registration_number : "")}} {{$trip->horse ? "| ".$trip->horse->fleet_number : ""}}
                                        @elseif ($trip->vehicle)
                                           Vehicle | {{ucfirst($trip->vehicle->vehicle_make ? $trip->vehicle->vehicle_make->name : "")}} {{ucfirst($trip->vehicle->vehicle_model ? $trip->vehicle->vehicle_model->name : "")}} {{ucfirst($trip->vehicle ? $trip->vehicle->registration_number : "")}}
                                        @endif
                                    </td>
                                     <td>  {{ $trip->start_date }}</td>
                                     <td>
                                        @if ($trip->delivery_note)
                                       {{$trip->delivery_note->offloaded_date}}
                                        
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
                                    @elseif($trip->trip_status == "OnHold")
                                    <td class="table-danger"><span class="label label-danger label-wide">{{$trip->trip_status}}</span></td>
                                    @elseif($trip->trip_status == "Offloading Point")
                                    <td class="table-accent"><span class="label label-accent label-wide">{{$trip->trip_status}}</span></td>
                                    @endif
                               
                                     
                                     <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{route('trips.show', $trip->id)}}"><i class="fas fa-eye color-default"></i>View</a></li>
                                                <li><a href="" wire:click="removeTrip({{$trip->id}})"><i class="fas fa-remove color-danger"></i>Remove</a></li>
                                            </ul>
                                        </div>
                                        @include('trips.delete')

                                </td>
                                  </tr>
                                  @elseif($trip->trip_status == "Offloading Point")
                                  <tr  style="background-color: #82B1FF">
                                  
                                    <td>{{ucfirst($trip->trip_number)}}</td>
                                    <td>{{ucfirst($trip->customer ? $trip->customer->name : "")}}</td>
                                    <td>{{ucfirst($trip->transporter ? $trip->transporter->name : "")}}</td>
                                    <td>
                                        @if ($trip->horse)
                                            Horse | {{ucfirst($trip->horse->horse_make ? $trip->horse->horse_make->name : "")}} {{ucfirst($trip->horse->horse_model ? $trip->horse->horse_model->name : "")}} {{ucfirst($trip->horse ? $trip->horse->registration_number : "")}} {{$trip->horse ? "| ".$trip->horse->fleet_number : ""}}
                                        @elseif ($trip->vehicle)
                                           Vehicle | {{ucfirst($trip->vehicle->vehicle_make ? $trip->vehicle->vehicle_make->name : "")}} {{ucfirst($trip->vehicle->vehicle_model ? $trip->vehicle->vehicle_model->name : "")}} {{ucfirst($trip->vehicle ? $trip->vehicle->registration_number : "")}}
                                        @endif
                                    </td>
                                     <td>  {{ $trip->start_date }}</td>
                                     <td>
                                        @if ($trip->delivery_note)
                                       {{$trip->delivery_note->offloaded_date}}
                                        
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
                                    @elseif($trip->trip_status == "OnHold")
                                    <td class="table-danger"><span class="label label-danger label-wide">{{$trip->trip_status}}</span></td>
                                    @elseif($trip->trip_status == "Offloading Point")
                                    <td class="table-accent"><span class="label label-accent label-wide">{{$trip->trip_status}}</span></td>
                                    @endif      
                             
                                     
                                     <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{route('trips.show', $trip->id)}}"><i class="fas fa-eye color-default"></i>View</a></li>
                                                <li><a href="" wire:click="removeTrip({{$trip->id}})"><i class="fas fa-remove color-danger"></i>Remove</a></li>
                                            </ul>
                                        </div>
                                        @include('trips.delete')

                                </td>
                                  </tr>
                                  @endif
                                  @empty
                                  <tr>
                                    <td colspan="12">
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
   
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="addTripsModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i>Add Trips to Tracking Group<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="addTrips()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="comment">Current Trips<span class="required" style="color: red">*</span></label>
                        <input type="text" wire:model.debounce.300ms="searchTrip" placeholder="Search trip" class="form-control">
                        <select class="form-control" wire:model.debounce.300ms="selectedTrip.0" size="4" required>
                            <option value="">Select Trip</option>
                              @foreach ($current_trips as $trip)
                                  <option value="{{$trip->id}}">{{$trip->trip_number}} | {{$trip->horse ? $trip->horse->registration_number : ""}} | {{$trip->customer ? $trip->customer->name : ""}} | {{$trip->loading_point ? $trip->loading_point->name : ""}} - {{$trip->offloading_point ? $trip->offloading_point->name : ""}} </option>
                              @endforeach
                           
                          </select>
                        @error('selectedTrip.0') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>
                    @foreach ($inputs as $key => $value)
                    <div class="row">
                        <div class="col-md-10">
                            <div class="form-group">
                                <input type="text" wire:model.debounce.300ms="searchTrip" placeholder="Search trip" class="form-control">
                                <select class="form-control" wire:model.debounce.300ms="selectedTrip.{{ $value }}" size="4" required>
                                    <option value="">Select Trip</option>
                                      @foreach ($current_trips as $trip)
                                          <option value="{{$trip->id}}">{{$trip->trip_number}} | {{$trip->horse ? $trip->horse->registration_number : ""}} | {{$trip->customer ? $trip->customer->name : ""}} | {{$trip->loading_point ? $trip->loading_point->name : ""}} - {{$trip->offloading_point ? $trip->offloading_point->name : ""}} </option>
                                      @endforeach
                                   
                                  </select>
                                @error('selectedTrip.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <label for=""></label>
                                <button class="btn btn-danger btn-rounded xs"   wire:click.prevent="removeTrip({{$key}})"> <i class="fa fa-times"></i></button>
                            </div>
                        </div>
                   
                    </div>
                    <br>
                    @endforeach
              
               
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <button class="btn btn-success btn-rounded btn-sm" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i>Trip</button>
                        </div>
                    </div>
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="trip_groupEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-truck-loading"></i> Edit Trip Group <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >

                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Enter Name" required>
                        @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

