<div>
    <style>
        th {
            width: 30%; /* Adjust width as needed */
            /* text-align: left; */
            padding: 10px;
            /* border: 1px solid #ddd; */
        }
    </style>
    <x-loading/>
    <section class="section">
        <div class="container-fluid">
            @include('includes.messages')
            <div class="row mt-15">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Trip# {{$trip->trip_number}}</h5>
                            </div>
                        </div>
                        <div class="panel-body">

                            <!-- Nav tabs -->
                            <ul class="nav nav-tabs border-bottom border-primary" role="tablist">
                                <li role="presentation" class="active"><a class="" href="#trips" aria-controls="trips" role="tab" data-toggle="tab" >Trip Details</a></li>
                                <li role="presentation" ><a class="" href="#destinations" aria-controls="destinations" role="tab" data-toggle="tab" >Offloading Points</a></li>
                                <li role="presentation" ><a class="" href="#documents" aria-controls="documents" role="tab" data-toggle="tab" >Trip Documents</a></li>
                                <li role="presentation" ><a class="" href="#expenses" aria-controls="expenses" role="tab" data-toggle="tab" >Trip Expenses</a></li>
                                <li role="presentation" ><a class="" href="#delivery_note" aria-controls="delivery_note" role="tab" data-toggle="tab" >Offloading Details</a></li>
                                <li role="presentation" ><a class="" href="#locations" aria-controls="locations" role="tab" data-toggle="tab" >Location Updates</a></li>
                                <li role="presentation" ><a class="" href="#breakdowns" aria-controls="breakdowns" role="tab" data-toggle="tab" >Incident(s)</a></li>
                            </ul>
                            <!-- Tab panes -->
                            <div class="tab-content bg-white pt-30">
                                <div role="tabpanel" class="tab-pane active" id="trips">
                                    <div class="col-md-12 p-n">
                                        <div class="col-md-12">
                                            <div class="panel panel-info">
                                                <div class="panel-heading">
                                                    <div class="panel-title">
                                                        <h5>Trip Details</h5>
                                                    </div>
                                                </div>
                                                <div class="panel-body overflow-x-auto">

                                                    <table class="table table-condensed mb-0 border-top table-striped">
                                                        <tbody>
                                                            <tr>
                                                                <th scope="row"> Trip Status</th>
                                                                @if ($trip->trip_status == "Offloaded")
                                                                    <td class="table-success">
                                                                        <span class="label label-success label-wide " style="margin-right:5px;">{{$trip->trip_status}}</span>
                                                                        @if ($trip->trip_status_date)
                                                                       
                                                                        @if ((preg_match($pattern, $trip->trip_status_date)) )
                                                                            On {{ \Carbon\Carbon::parse($trip->trip_status_date)->format('d M Y g:i A')}}
                                                                        @else
                                                                            On {{$trip->trip_status_date}}
                                                                        @endif  
                                                                    @endif
                                                                    </td>
                                                                    @elseif($trip->trip_status == "Scheduled")
                                                                    <td class="table-warning" >
                                                                        <span class="label label-warning label-wide" style="margin-right:5px;">{{$trip->trip_status}}</span>
                                                                        @if ($trip->trip_status_date)
                                                                       
                                                                        @if ((preg_match($pattern, $trip->trip_status_date)) )
                                                                            On {{ \Carbon\Carbon::parse($trip->trip_status_date)->format('d M Y g:i A')}}
                                                                        @else
                                                                            On {{$trip->trip_status_date}}
                                                                        @endif  
                                                                    @endif
                                                                    </td>
                                                                    @elseif($trip->trip_status == "Loading Point")
                                                                    <td class="table-default" >
                                                                        <span class="label label-default label-wide" style="margin-right:5px;">{{$trip->trip_status}}</span>
                                                                        @if ($trip->trip_status_date)
                                                                       
                                                                        @if ((preg_match($pattern, $trip->trip_status_date)) )
                                                                            On {{ \Carbon\Carbon::parse($trip->trip_status_date)->format('d M Y g:i A')}}
                                                                        @else
                                                                            On {{$trip->trip_status_date}}
                                                                        @endif  
                                                                        @endif
                                                                    </td>
                                                                    @elseif($trip->trip_status == "Started")
                                                                    <td class="table-default" >
                                                                        <span class="label label-primary label-wide" style="margin-right:5px;">{{$trip->trip_status}}</span>
                                                                        @if ($trip->trip_status_date)
                                                                       
                                                                        @if ((preg_match($pattern, $trip->trip_status_date)) )
                                                                            On {{ \Carbon\Carbon::parse($trip->trip_status_date)->format('d M Y g:i A')}}
                                                                        @else
                                                                            On {{$trip->trip_status_date}}
                                                                        @endif  
                                                                        @endif
                                                                    </td>
                                                                    @elseif($trip->trip_status == "Loaded")
                                                                    <td class="table-info">
                                                                        <span class="label label-info label-wide" style="margin-right:5px;">{{$trip->trip_status}}</span>
                                                                        @if ($trip->trip_status_date)
                                                                       
                                                                        @if ((preg_match($pattern, $trip->trip_status_date)) )
                                                                            On {{ \Carbon\Carbon::parse($trip->trip_status_date)->format('d M Y g:i A')}}
                                                                        @else
                                                                            On {{$trip->trip_status_date}}
                                                                        @endif  
                                                                    @endif
                                                                    </td>
                                                                    @elseif($trip->trip_status == "InTransit")
                                                                    <td class="table-primary">
                                                                        <span class="label label-primary label-wide" style="margin-right:5px;">{{$trip->trip_status}}</span>
                                                                        @if ($trip->trip_status_date)
                                                                       
                                                                        @if ((preg_match($pattern, $trip->trip_status_date)) )
                                                                            On {{ \Carbon\Carbon::parse($trip->trip_status_date)->format('d M Y g:i A')}}
                                                                        @else
                                                                            On {{$trip->trip_status_date}}
                                                                        @endif  
                                                                    @endif
                                                                    </td>
                                                                    @elseif($trip->trip_status == "OnHold")
                                                                    <td class="table-danger">
                                                                        <span class="label label-danger label-wide" style="margin-right:5px;">{{$trip->trip_status}}</span>
                                                                        @if ($trip->trip_status_date)
                                                                       
                                                                        @if ((preg_match($pattern, $trip->trip_status_date)) )
                                                                            On {{ \Carbon\Carbon::parse($trip->trip_status_date)->format('d M Y g:i A')}}
                                                                        @else
                                                                            On {{$trip->trip_status_date}}
                                                                        @endif  
                                                                    @endif
                                                                    </td>
                                                                    @elseif($trip->trip_status == "Offloading Point")
                                                                    <td class="table-default">
                                                                        <span class="label label-default label-wide" style="margin-right:5px;">{{$trip->trip_status}}</span>
                                                                        @if ($trip->trip_status_date)
                                                                       
                                                                        @if ((preg_match($pattern, $trip->trip_status_date)) )
                                                                            On {{ \Carbon\Carbon::parse($trip->trip_status_date)->format('d M Y g:i A')}}
                                                                        @else
                                                                            On {{$trip->trip_status_date}}
                                                                        @endif  
                                                                    @endif
                                                                    </td>
                                                                    @elseif($trip->trip_status == "Cancelled")
                                                                    <td class="table-default">
                                                                        <span class="label label-default label-wide" style="margin-right:5px;">{{$trip->trip_status}}</span>
                                                                        @if ($trip->trip_status_date)
                                                                       
                                                                        @if ((preg_match($pattern, $trip->trip_status_date)) )
                                                                            On {{ \Carbon\Carbon::parse($trip->trip_status_date)->format('d M Y g:i A')}}
                                                                        @else
                                                                            On {{$trip->trip_status_date}}
                                                                        @endif  
                                                                    @endif
                                                                    </td>
                                                                    @endif
        
                                                                   
                                                                  
                                                            </tr>
                                                            @if ($trip->trip_status_description)
                                                            <tr>
                                                                <th scope="row"> Trip Status Description</th>
                                                                <td>
                                                                    {{$trip->trip_status_description}}
                                                                </td>
                                                            </tr>
                                                            @endif
                                                            <tr>
                                                                <th scope="row"> Trip#</th>
                                                                <td>
                                                                    {{$trip->trip_number}}
                                                                </td>
                                                            </tr>
                                                            @if ($initial_fuel)
                                                            <tr>
                                                                <th scope="row">Fuel Order#</th>
                                                                <td>
                                                                    <a href="{{ route('fuels.show',$initial_fuel->id) }}" style="color:blue">{{$initial_fuel->order_number}}</a>
                                                                </td>
                                                            </tr>
                                                            @endif
                                                            <tr>
                                                                <th scope="row">Transporter</th>
                                                                <td>
                                                                    <a href="{{ route('transporters.show',$trip->transporter->id) }}" style="color:blue">   {{ucfirst($trip->transporter ? $trip->transporter->name : "")}}</a>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Trip Type</th>
                                                                <td>
                                                                    {{$trip->trip_type ? $trip->trip_type->name : ""}}
                                                                </td>
                                                            </tr>
                                                            @if ($trip->cd3_number)
                                                            <tr>
                                                                <th scope="row">CD3#</th>
                                                                <td>
                                                                    {{$trip->cd3_number}}
                                                                </td>
                                                            </tr>
                                                            @endif
                                                            @if ($trip->cd1_number)
                                                            <tr>
                                                                <th scope="row">CD1#</th>
                                                                <td>
                                                                    {{$trip->cd1_number}}
                                                                </td>
                                                            </tr>
                                                            @endif
                                                            @if ($trip->bill_of_entry)
                                                            <tr>
                                                                <th scope="row">Bill Of Entry #</th>
                                                                <td>
                                                                    {{$trip->bill_of_entry}}
                                                                </td>
                                                            </tr>
                                                            @endif
                                                            @if ($trip->manifest_number)
                                                            <tr>
                                                                <th scope="row">Manifest#</th>
                                                                <td>
                                                                    {{$trip->manifest_number}}
                                                                </td>
                                                            </tr>
                                                            @endif
                                                            @if (isset($trip->borders) && $trip->borders->count()>0)
                                                            <tr>
                                                                <th scope="row">Boarder(s)</th>
                                                                <td>
                                                                    @foreach ($trip->borders as $border)
                                                                        {{$border->name}} <br>
                                                                    @endforeach
                                                                </td>
                                                            </tr>
                                                            @endif
                                                            @if (isset($trip->clearing_agents) && $trip->clearing_agents->count()>0)
                                                            <tr>
                                                                <th scope="row">Clearing Agent(s)</th>
                                                                <td>
                                                                    @foreach ($trip->clearing_agents as $clearing_agent)
                                                                        <a href="{{ route('clearing_agents.show',$clearing_agent->id) }}" style="color:blue">{{$clearing_agent->name}}</a> <br>
                                                                    @endforeach
                                                                </td>
                                                            </tr>
                                                            @endif
                                                            <tr>
                                                                <th scope="row"> Trip Tracking Group</th>
                                                                <td>
                                                                    @if ($trip->trip_group)
                                                                        <a href="{{ route('trip_groups.show', $trip->trip_group->id) }}">  {{$trip->trip_group ? $trip->trip_group->name : "no group"}}</a>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                            @if ($trip->broker)
                                                            <tr>
                                                                <th scope="row">Broker</th>
                                                                <td>
                                                                    <a href="{{ route('brokers.show',$trip->broker->id) }}" style="color:blue">{{ucfirst($trip->broker ? $trip->broker->name : "")}}
                                                                </td>
                                                            </tr>
                                                            @endif
                                                            <tr>
                                                                <th scope="row">Customer</th>
                                                                <td>
                                                                    @if ($trip->customer)
                                                                        <a href="{{ route('customers.show',$trip->customer->id) }}" style="color:blue"> {{$trip->customer ? $trip->customer->name : ""}}</a>
                                                                    @endif
                                                                   
                                                                </td>
                                                            </tr>
                                                            @if ($trip->consignee)
                                                            <tr>
                                                                <th scope="row">Consignee</th>
                                                                <td>
                                                                    <a href="{{ route('consignees.show',$trip->consignee->id) }}" style="color:blue"> {{$trip->consignee ? $trip->consignee->name : ""}}</a>
                                                                </td>
                                                            </tr>
                                                            @endif
                                                            <tr>
                                                                <th scope="row">Equipment</th>
                                                                <td>
                                                                    @if ($trip->horse)
                                                                    Horse | <a href="{{ route('horses.show',$trip->horse->id) }}" style="color:blue">  {{$trip->horse->registration_number}} {{$trip->horse->fleet_number ? "(".$trip->horse->fleet_number.")" : ""}}    {{$trip->horse->horse_make ? $trip->horse->horse_make->name : "" }} {{$trip->horse->horse_model ? $trip->horse->horse_model->name : "" }} </a>
                                                                    @elseif($trip->vehicle)
                                                                    Vehicle | <a href="{{ route('vehicles.show',$trip->vehicle->id) }}" style="color:blue"> {{$trip->vehicle->registration_number}} {{$trip->vehicle->fleet_number ? "(".$trip->vehicle->fleet_number.")" : ""}}  {{$trip->vehicle->vehicle_make ? $trip->vehicle->vehicle_make->name : "" }} {{$trip->vehicle->vehicle_model ? $trip->vehicle->vehicle_model->name : "" }} </a>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row"> Trailer(s)</th>
                                                                <td>
                                                                    @if ($trip->trailers->count()>0)
                                                                    [ @foreach ($trip->trailers as $trailer)
                                                                    {{$trailer->make}} {{$trailer->model}} ({{$trailer->registration_number}}),
                                                                    @endforeach
                                                                    ]
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row"> Driver</th>
                                                                <td>
                                                                    @if ($trip->driver)
                                                                        <a href="{{ route('employees.show',$trip->driver->employee->id) }}" style="color:blue">  {{$trip->driver->employee ? $trip->driver->employee->name : ""}} {{ $trip->driver->employee ? $trip->driver->employee->surname : ""}}</a>
                                                                    @endif
                                                                    @if ($trip->notes)
                                                                        <br>
                                                                        <strong>Driver  Notes | </strong> {{ $trip->notes }}
                                                                    @endif
                                                                    @if ($trip->driver_allowances->count()>0)
                                                                    <br>
                                                                    <strong>Allowances |</strong> <br>
                                                                        @foreach ($trip->driver_allowances as $driver_allowance)
                                                                            {{$driver_allowance->allowance ? $driver_allowance->allowance->name : ""}} {{$driver_allowance->currency ? $driver_allowance->currency->name : ""}} {{$driver_allowance->currency ? $driver_allowance->currency->symbol : ""}}{{number_format($driver_allowance->amount,2)}}, 
                                                                        @endforeach
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row"> From</th>
                                                                <td>
                                                                    @if (isset($from))
                                                                    {{$from->country ? $from->country->name : ""}} {{$from->city}}
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row"> To</th>
                                                                <td>
                                                                    @if (isset($to))
                                                                     {{$to->country ? $to->country->name : ""}} {{$to->city}}
                                                                    @endif  
                                                                </td>
                                                            </tr>
                                                            @if ($trip->loading_point)
                                                            <tr>
                                                                <th scope="row">Loading Point</th>
                                                                <td>
                                                                    <a href="{{ route('loading_points.show',$trip->loading_point->id) }}" style="color:blue">{{$trip->loading_point ? $trip->loading_point->name : ""}}</a>
                                                                </td>
                                                            </tr>
                                                            @endif
                                                            @if ($trip->offloading_point)
                                                            <tr>
                                                                <th scope="row">Offloading Point</th>
                                                                <td>
                                                                    <a href="{{ route('offloading_points.show',$trip->offloading_point->id) }}" style="color:blue">{{$trip->offloading_point ? $trip->offloading_point->name : ""}}</a>
                                                                </td>
                                                            </tr>
                                                            @endif
                                                            <tr>
                                                                <th scope="row">Route</th>
                                                                <td>
                                                                    {{$trip->route ? $trip->route->name : ""}}
                                                                    @if ($trip->route)
                                                                    | Rank {{$trip->route ? $trip->route->rank : ""}}
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                            @if ($trip->emptyrun_origin == True)
                                                                <tr>
                                                                    <th scope="row"> Empty Run - Origin</th>
                                                                    <td>
                                                                        Starting Mileage: {{$emptyrun_origin->starting_mileage ? $emptyrun_origin->starting_mileage." Kms" : ""}} <br>
                                                                        Ending Mileage: {{$emptyrun_origin->ending_mileage ? $emptyrun_origin->ending_mileage." Kms" : ""}} <br>
                                                                        Distance : {{$emptyrun_origin->distance ? $emptyrun_origin->distance." Kms" : ""}} <br>
                                                                        Fuel Quantity: {{$emptyrun_origin->fuel_quantity ? $emptyrun_origin->fuel_quantity." Litres" : ""}} <br>
                                                                        Fuel Amount: {{$emptyrun_origin->currency ? $emptyrun_origin->currency->name : ""}} {{$emptyrun_origin->currency ? $emptyrun_origin->currency->symbol : ""}}{{number_format($emptyrun_origin->fuel_amount ? $emptyrun_origin->fuel_amount : 0,2)}}
                                                                    </td>
                                                                </tr>
                                                            @endif
                                                            @if ($trip->emptyrun_destination == True)
                                                                <tr>
                                                                    <th scope="row"> Empty Run - Destination</th>
                                                                    <td>
                                                                        Starting Mileage: {{$emptyrun_destination->starting_mileage ? $emptyrun_destination->starting_mileage." Kms" : ""}} <br>
                                                                        Ending Mileage: {{$emptyrun_destination->ending_mileage ? $emptyrun_destination->ending_mileage." Kms" : ""}} <br>
                                                                        Distance : {{$emptyrun_destination->distance ? $emptyrun_destination->distance." Kms" : ""}} <br>
                                                                        Fuel Quantity: {{$emptyrun_destination->fuel_quantity ? $emptyrun_destination->fuel_quantity." Litres" : ""}} <br>
                                                                        Fuel Amount: {{$emptyrun_destination->currency ? $emptyrun_destination->currency->name : ""}} {{$emptyrun_destination->currency ? $emptyrun_destination->currency->symbol : ""}}{{number_format($emptyrun_destination->fuel_amount ? $emptyrun_destination->fuel_amount : 0,2)}}
                                                                    </td>
                                                                </tr>
                                                            @endif
                                                            @if ($trip->truck_stops->count()>0)
                                                                <tr>
                                                                    <th scope="row">Recommended Truck Stops</th>
                                                                    <td>
                                                                        [ 
                                                                            @foreach ($trip->truck_stops as $truck_stop)
                                                                                <i class="fas fa-map-pin" style="color:red"></i> {{ $truck_stop->name }}
                                                                            @endforeach
                                                                        ]
                                                                    </td>
                                                                </tr>
                                                            @endif
                                                            
                                                            <tr>
                                                                <th scope="row">Departure</th>
                                                                <td>
                                                                    @if ((preg_match($pattern, $trip->start_date)) )
                                                                        {{ \Carbon\Carbon::parse($trip->start_date)->format('d M Y g:i A')}}
                                                                    @else
                                                                        {{$trip->start_date}}
                                                                    @endif    
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Estimated Offloading Date</th>
                                                                <td>
                                                                     @if ((preg_match($pattern, $trip->end_date)) )
                                                                        {{ \Carbon\Carbon::parse($trip->end_date)->format('d M Y g:i A')}}
                                                                    @else
                                                                        {{$trip->end_date}}
                                                                    @endif
                                                                   
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Actual Offloading Date</th>
                                                                <td>
                                                                    @if ($trip->delivery_note)
                                                                        @if ((preg_match($pattern, $trip->delivery_note->offloaded_date)) )
                                                                        {{ \Carbon\Carbon::parse($trip->delivery_note->offloaded_date)->format('d M Y g:i A')}}
                                                                        @else
                                                                            {{$trip->delivery_note->offloaded_date}}
                                                                        @endif
                                                                    @endif
                                                                    
                                                                </td>
                                                            </tr>
                                                            @if (isset($trip->start_date) && isset($trip->end_date))
                                                            <tr>
                                                                <th scope="row">  Standard Trip Duration</th>
                                                                <td>
                                                                   
                                                                    @if ((preg_match($pattern, $trip->start_date)) && (preg_match($pattern, $trip->end_date)) )
                                                                    
                                                                      {{ \Carbon\Carbon::parse($trip->start_date)
                                                                        ->diff(\Carbon\Carbon::parse($trip->end_date))
                                                                        ->format('%d Day(s) %h Hour(s) %i Minute(s)') }}

                                                                    @endif
                                                                </td>
                                                            </tr>
                                                            @endif
                                                            @if (isset($trip->start_date) && isset($trip->delivery_note->offloaded_date))
                                                            <tr>
                                                                <th scope="row">  Actual Trip Duration</th>
                                                                <td>
                                                                    @if ((preg_match($pattern, $trip->start_date)) && (preg_match($pattern, $trip->delivery_note->offloaded_date)) )

                                                                    {{ \Carbon\Carbon::parse($trip->start_date)
                                                                        ->diff(\Carbon\Carbon::parse($trip->delivery_note->offloaded_date))
                                                                        ->format('%d Day(s) %h Hour(s) %i Minute(s)') }}

                                                                   
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                            @endif
                                                            @if ($trip->timelines == True)
                                                                <tr>
                                                                    <th scope="row">Timelines</th>
                                                                    <td>
                                                                       Arrive @ LP: {{$trip->arrive_loading_point}} Depart LP: {{$trip->depart_loading_point}} Loading Time: {{$trip->loading_time}} 
                                                                       <br>
                                                                       Arrive @ OP: {{$trip->arrive_offloading_point}} Depart OP: {{$trip->depart_offloading_point}} Offloading Time: {{$trip->offloading_time}} 
                                                                    </td>
                                                                </tr>
                                                            @endif
                                                            
                                                            <tr>
                                                                <th scope="row"> Starting Mileage</th>
                                                                <td>
                                                                    {{$trip->starting_mileage ? $trip->starting_mileage." Kms" : ""}} 
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row"> Starting Hours</th>
                                                                <td>
                                                                    {{$trip->starting_hours ? $trip->starting_hours." Hours" : ""}} 
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Ending Mileage</th>
                                                                <td>
                                                                    {{$trip->ending_mileage ? $trip->ending_mileage." Kms" : ""}} 
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Ending Hours</th>
                                                                <td>
                                                                    {{$trip->ending_hours ? $trip->ending_hours." Hours" : ""}} 
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Approximate Distance</th>
                                                                <td>
                                                                    {{$trip->distance ? $trip->distance." Kms" : ""}} 
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Acutual Distance</th>
                                                                <td>
                                                                    
                                                                    {{$actual_distance ? $actual_distance." Kms" : ""}}
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Approximate Trip Fuel</th>
                                                                <td>
                                                                    @if ($trip->trip_fuel)
                                                                        {{$trip->trip_fuel}} l
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Fuel Consumption Mileage</th>
                                                                <td>
                                                                    {{$trip->fuel_consumption_mileage ? number_format($trip->fuel_consumption_mileage, 2)." Km/l" : ""}}
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Fuel Consumption Hours</th>
                                                                <td>
                                                                   {{$trip->fuel_consumption_hours ? number_format($trip->fuel_consumption_hours,2)." H/l" : ""}}
                                                                </td>
                                                            </tr>
                                                 
                                                            <tr>
                                                                <th scope="row">Cargo</th>
                                                                <td>
                                                                    {{$trip->cargo ? $trip->cargo->name : ""}}
                                                                    <br>
                                                                    @if ($trip->cargo_details)
                                                                    <strong>Additional Cargo Details | </strong>  {{ $trip->cargo_details }}
                                                                    @endif
                                                                    <br>
                                                                    @if (isset($trip->cargo))
                                                                    @if ($trip->cargo->risk == "High")
                                                                    <strong>Risk Level | </strong><span class="label label-danger"> {{ $trip->cargo ? $trip->cargo->risk : "" }}</span>
                                                                    @elseif($trip->cargo->risk == "Medium")
                                                                    <strong>Risk Level | </strong><span class="label label-warning"> {{ $trip->cargo ? $trip->cargo->risk : "" }}</span>
                                                                    @elseif($trip->cargo->risk == "Low")
                                                                    <strong>Risk Level | </strong><span class="label label-success"> {{ $trip->cargo ? $trip->cargo->risk : "" }}</span>
                                                                    @endif 
                                                                    @endif
                                                                    @if ($trip->temparature)
                                                                        <br>
                                                                        <strong>Temperature(<span>&deg;C</span>)</strong>  {{$trip->temparature}}
                                                                    @endif
                                                                    @if ($trip->volume)
                                                                        <br>
                                                                        <strong>Volume(m<sup>3</sup>)</strong>  {{$trip->volume}}
                                                                    @endif
                                                                    @if ($trip->seal_number)
                                                                        <br>
                                                                        <strong>Seal Number(s)</strong>  {{$trip->seal_number}}
                                                                    @endif
                                                                    @if ($trip->container_number)
                                                                        <br>
                                                                        <strong>Container Number(s)</strong>  {{$trip->container_number}}
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                            @if (isset($trip->weight))
                                                                <tr>
                                                                    <th scope="row">Scheduled Weight</th>
                                                                    <td>
                                                                        {{$trip->weight ? $trip->weight." tons" : ""}} 
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th scope="row">Loaded Weight</th>
                                                                    <td>
                                                                        {{$trip->delivery_note->loaded_weight ? $trip->delivery_note->loaded_weight." tons" : ""}} 
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th scope="row">Offloading Weight</th>
                                                                    <td>
                                                                        {{$trip->delivery_note->offloaded_weight ? $trip->delivery_note->offloaded_weight." tons" : ""}} 
                                                                    </td>
                                                                </tr>
                                                            @endif
                                                            @if (isset($trip->cargo))
                                                            @if ($trip->cargo->type == "Solid")
                                                                <tr>
                                                                    <th scope="row"> Secheduled Quantity</th>
                                                                    <td>
                                                                        {{$trip->quantity}} {{$trip->measurement}}
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th scope="row"> Loaded Quantity</th>
                                                                    <td>
                                                                        {{$trip->delivery_note->loaded_quantity ? $trip->delivery_note->loaded_quantity : ""}} {{$trip->measurement}}
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th scope="row"> Offloaded Quantity</th>
                                                                    <td>
                                                                        {{$trip->delivery_note->offloaded_quantity ? $trip->delivery_note->offloaded_quantity : ""}} {{$trip->measurement}}
                                                                    </td>
                                                                </tr>
                                                            @elseif($trip->cargo->type == "Liquid")
                                                            <tr>
                                                                <th scope="row">Scheduled Litreage @ Ambient Temperature</th>
                                                                <td>
                                                                    {{$trip->litreage}} {{$trip->measurement}}
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Scheduled Litreage @ 20 Degrees</th>
                                                                <td>
                                                                    {{$trip->litreage_at_20}} {{$trip->measurement}}
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Loaded Litreage @ Ambient Temperature</th>
                                                                <td>
                                                                     {{$trip->delivery_note ? $trip->delivery_note->loaded_litreage : ""}} {{$trip->measurement}}
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Loaded Litreage @ 20 Degrees</th>
                                                                <td>
                                                                    {{$trip->delivery_note ? $trip->delivery_note->loaded_litreage_at_20 : ""}} {{$trip->measurement}}
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Offloaded Litreage @ Ambient Temperature</th>
                                                                <td>
                                                                    {{$trip->delivery_note ? $trip->delivery_note->offloaded_litreage : ""}} {{$trip->measurement}}
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Offloaded Litreage @ 20 Degrees</th>
                                                                <td>
                                                                    {{$trip->delivery_note ? $trip->delivery_note->offloaded_litreage_at_20 : ""}} {{$trip->measurement}}
                                                                </td>
                                                            </tr>
                                                            @endif
                                                            @endif

                                                            @if ($this->company->rates_managed_by_finance == 1)
                                                                @if (in_array('Finance', $department_names) ||  in_array('Super Admin', $role_names))
                                                                    @if (!$this->user->driver)
                                                                            <tr>
                                                                                <th scope="row">Freight Calculation Method</th>
                                                                                <td>
                                                                                    @if ($trip->freight_calculation == "rate_weight_distance")
                                                                                        Rate * Weight * Distance
                                                                                    @elseif ($trip->freight_calculation == "flat_rate")
                                                                                        Flat Rate
                                                                                    @elseif ($trip->freight_calculation == "rate_distance")
                                                                                    Rate * Distance
                                                                                    @elseif ($trip->freight_calculation == "rate_weight")
                                                                                        Rate * Weight/Litreage
                                                                                    @endif
                                                                                </td>
                                                                            </tr>
                                                                            @if ($trip->currency)
                                                                                <tr>
                                                                                    <th scope="row"> Currency</th>
                                                                                    <td>
                                                                                        {{$trip->currency ? $trip->currency->name : ""}}
                                                                                    </td>
                                                                                </tr>
                                                                            @endif
                                                                            @if ($trip->rate)
                                                                                <tr>
                                                                                    <th scope="row">Customer Rate</th>
                                                                                    <td>
                                                                                        {{$trip->currency ? $trip->currency->symbol : ""}}{{number_format($trip->rate,2)}}
                                                                                    </td>
                                                                                </tr>
                                                                            @endif
                                                                            @if ($trip->freight)
                                                                                <tr>
                                                                                    <th scope="row">Customer Freight</th>
                                                                                    <td>
                                                                                        {{$trip->currency ? $trip->currency->symbol : ""}}{{number_format($trip->freight,2)}}
                                                                                    </td>
                                                                                </tr>
                                                                            @endif
                                                                            @if (isset($trip->exchange_rate) && isset($trip->exchange_customer_freight))
                                                                                <tr>
                                                                                    <th scope="row">Customer Freight Conversion</th>
                                                                                    <td>
                                                                                        Currency conversion: {{ $this->company->currency ? $this->company->currency->name : "" }} {{ $this->company->currency ? $this->company->currency->symbol : "" }}{{ number_format($trip->exchange_customer_freight,2)}} @ {{ $trip->exchange_rate}} 
                                                                                    </td>
                                                                                </tr>
                                                                            @endif
                                                                            @if ($trip->transporter_rate)
                                                                                <tr>
                                                                                    <th scope="row">  Transporter Rate</th>
                                                                                    <td>
                                                                                        {{$trip->currency ? $trip->currency->symbol : ""}}{{number_format($trip->transporter_rate,2)}}
                                                                                    </td>
                                                                                </tr>
                                                                            @endif
                                                                            @if ($trip->transporter_freight)
                                                                                <tr>
                                                                                    <th scope="row"> Transporter Freight</th>
                                                                                    <td>
                                                                                        {{$trip->currency ? $trip->currency->symbol : ""}}{{number_format($trip->transporter_freight,2)}}
                                                                                    </td>
                                                                                </tr>
                                                                            @endif
                                                                            @if (isset($trip->exchange_rate) && (isset($trip->exchange_transporter_freight) && $trip->exchange_transporter_freight > 0))
                                                                                <tr>
                                                                                    <th scope="row">  Transporter Freight Conversion</th>
                                                                                    <td>
                                                                                        Currency conversion: {{ $this->company->currency ? $this->company->currency->name : "" }} {{ $this->company->currency ? $this->company->currency->symbol : "" }}{{ number_format($trip->exchange_transporter_freight,2)}} @ {{ $trip->exchange_rate}} 
                                                                                    </td>
                                                                                </tr>
                                                                            @endif
                                                                               <tr>
                                                                                <th scope="row">Trip Expenses</th>
                                                                                <td>
                                                                                    @if ($total_transporter_expenses)
                                                                                    Total Transporter Expenses: {{$this->company->currency ? $this->company->currency->name : ""}} {{$this->company->currency ? $this->company->currency->symbol : ""}}{{number_format($total_transporter_expenses,2)}} <br>
                                                                                    @endif
                                                                                    @if ($total_customer_expenses)
                                                                                        Total Customer Expenses: {{$this->company->currency ? $this->company->currency->name : ""}} {{$this->company->currency ? $this->company->currency->symbol : ""}}{{number_format($total_customer_expenses,2)}} <br>
                                                                                    @endif
                                                                                    @if ($total_expenses)
                                                                                        Total Self Expenses: {{$this->company->currency ? $this->company->currency->name : ""}} {{$this->company->currency ? $this->company->currency->symbol : ""}}{{number_format($total_expenses,2)}} <br>
                                                                                    @endif
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th scope="row">Turnover</th>
                                                                                <td>
                                                                                    @if ($trip->turnover)
                                                                                        {{$trip->currency ? $trip->currency->symbol : ""}}{{number_format($trip->turnover,2)}}
                                                                                    @endif
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th scope="row"> Cost Of Sales</th>
                                                                                <td>
                                                                                    @if ($trip->cost_of_sales)
                                                                                        {{$default_currency?->name}} {{$default_currency?->symbol}}{{number_format($trip->cost_of_sales,2)}}
                                                                                    @endif
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th scope="row"> Gross Profit</th>
                                                                                <td>
                                                                                    @if ($trip->freight)
                                                                                    {{ $trip->currency ? $trip->currency->symbol : "" }}{{number_format($trip->freight,2)}}
                                                                                    @endif
                                                                                </td>
                                                                            </tr>
                                                                         
            
                                                                            <tr>
                                                                                <th scope="row"> Net Profit</th>
                                                                                <td>
                                                                                    @if ($trip->net_profit)
                                                                                    {{$default_currency?->name}} {{$default_currency?->symbol}}{{number_format($trip->net_profit,2)}}
                                                                                    @endif
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th scope="row">Net Profit Percentage</th>
                                                                                <td>
                                                                                    @if (isset($trip->net_profit_percentage))
                                                                                        {{number_format($trip->net_profit_percentage,2)}}%
                                                                                    @endif
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th scope="row">Markup Percentage</th>
                                                                                <td>
                                                                                    @if (isset($trip->markup_percentage))
                                                                                        {{number_format($trip->markup_percentage,2)}}%
                                                                                    @endif
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th scope="row"> Cost Per Kilometer</th>
                                                                                <td>
                                                                                    @if (isset($cpk))
                                                                                        {{$this->company->currency ? $this->company->currency->name : ""}}   {{$this->company->currency ? $this->company->currency->symbol : ""}}{{number_format($cpk,2)}} / Km
                                                                                    @endif
                                                                                </td>
                                                                            </tr>
                                                                            @if ($trip->agent)
                                                                            <tr>
                                                                                <th scope="row">  Agent</th>
                                                                                <td>
                                                                                    <a href="{{ route('agents.show',$trip->agent->id) }}" style="color:blue">   {{$trip->agent->name}} {{$trip->agent->surname}}</a>
                                                                                </td>
                                                                            </tr>
                                                                            @endif
                                                                            @if ($trip->commission)
                                                                            <tr>
                                                                                <th scope="row"> Agent Commission</th>
                                                                                <td>
                                                                                    {{$trip->currency ? $trip->currency->symbol : ""}}{{number_format($trip->commission->amount ? $trip->commission->amount : 0,2)}} @ {{$trip->commission ? $trip->commission->commission : ""}}%
                                                                                </td>
                                                                            </tr>
                                                                            @endif

                                                                        @endif
                                                                    @endif
                                                                @else 
                                                                    @if (!$this->user->driver)
                                                                        <tr>
                                                                            <th scope="row">Freight Calculation Method</th>
                                                                            <td>
                                                                                @if ($trip->freight_calculation == "rate_weight_distance")
                                                                                    Rate * Weight * Distance
                                                                                @elseif ($trip->freight_calculation == "flat_rate")
                                                                                    Flat Rate
                                                                                @elseif ($trip->freight_calculation == "rate_distance")
                                                                                Rate * Distance
                                                                                @elseif ($trip->freight_calculation == "rate_weight")
                                                                                    Rate * Weight/Litreage
                                                                                @endif
                                                                            </td>
                                                                        </tr>
                                                                        @if ($trip->currency)
                                                                            <tr>
                                                                                <th scope="row"> Currency</th>
                                                                                <td>
                                                                                    {{$trip->currency ? $trip->currency->name : ""}}
                                                                                </td>
                                                                            </tr>
                                                                        @endif
                                                                        @if ($trip->rate)
                                                                            <tr>
                                                                                <th scope="row">Customer Rate</th>
                                                                                <td>
                                                                                    {{$trip->currency ? $trip->currency->symbol : ""}}{{number_format($trip->rate,2)}}
                                                                                </td>
                                                                            </tr>
                                                                        @endif
                                                                        @if ($trip->freight)
                                                                            <tr>
                                                                                <th scope="row">Customer Freight</th>
                                                                                <td>
                                                                                    {{$trip->currency ? $trip->currency->symbol : ""}}{{number_format($trip->freight,2)}}
                                                                                </td>
                                                                            </tr>
                                                                        @endif
                                                                        @if (isset($trip->exchange_rate) && isset($trip->exchange_customer_freight))
                                                                            <tr>
                                                                                <th scope="row">Customer Freight Conversion</th>
                                                                                <td>
                                                                                    Currency conversion: {{ $this->company->currency ? $this->company->currency->name : "" }} {{ $this->company->currency ? $this->company->currency->symbol : "" }}{{ number_format($trip->exchange_customer_freight,2)}} @ {{ $trip->exchange_rate}} 
                                                                                </td>
                                                                            </tr>
                                                                        @endif
                                                                        @if ($trip->transporter_rate)
                                                                            <tr>
                                                                                <th scope="row">  Transporter Rate</th>
                                                                                <td>
                                                                                    {{$trip->currency ? $trip->currency->symbol : ""}}{{number_format($trip->transporter_rate,2)}}
                                                                                </td>
                                                                            </tr>
                                                                        @endif
                                                                        @if ($trip->transporter_freight)
                                                                            <tr>
                                                                                <th scope="row"> Transporter Freight</th>
                                                                                <td>
                                                                                    {{$trip->currency ? $trip->currency->symbol : ""}}{{number_format($trip->transporter_freight,2)}}
                                                                                </td>
                                                                            </tr>
                                                                        @endif
                                                                        @if (isset($trip->exchange_rate) && (isset($trip->exchange_transporter_freight) && $trip->exchange_transporter_freight > 0))
                                                                            <tr>
                                                                                <th scope="row">  Transporter Freight Conversion</th>
                                                                                <td>
                                                                                    Currency conversion: {{ $this->company->currency ? $this->company->currency->name : "" }} {{ $this->company->currency ? $this->company->currency->symbol : "" }}{{ number_format($trip->exchange_transporter_freight,2)}} @ {{ $trip->exchange_rate}} 
                                                                                </td>
                                                                            </tr>
                                                                        @endif
                                                                        <tr>
                                                                            <th scope="row">Trip Expenses</th>
                                                                            <td>
                                                                                @if ($total_transporter_expenses)
                                                                                Total Transporter Expenses: {{$this->company->currency ? $this->company->currency->name : ""}} {{$this->company->currency ? $this->company->currency->symbol : ""}}{{number_format($total_transporter_expenses,2)}} <br>
                                                                                @endif
                                                                                @if ($total_customer_expenses)
                                                                                    Total Customer Expenses: {{$this->company->currency ? $this->company->currency->name : ""}} {{$this->company->currency ? $this->company->currency->symbol : ""}}{{number_format($total_customer_expenses,2)}} <br>
                                                                                @endif
                                                                                @if ($total_expenses)
                                                                                    Total Self Expenses: {{$this->company->currency ? $this->company->currency->name : ""}} {{$this->company->currency ? $this->company->currency->symbol : ""}}{{number_format($total_expenses,2)}} <br>
                                                                                @endif
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th scope="row">Turnover</th>
                                                                            <td>
                                                                                @if ($trip->turnover)
                                                                                    {{$trip->currency ? $trip->currency->symbol : ""}}{{number_format($trip->turnover,2)}}
                                                                                @endif
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th scope="row"> Cost Of Sales</th>
                                                                            <td>
                                                                                @if ($trip->cost_of_sales)
                                                                                    {{$default_currency?->name}} {{$default_currency?->symbol}}{{number_format($trip->cost_of_sales,2)}}
                                                                                @endif
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th scope="row"> Gross Profit</th>
                                                                            <td>
                                                                                @if ($trip->freight)
                                                                                {{ $trip->currency ? $trip->currency->symbol : "" }}{{number_format($trip->freight,2)}}
                                                                                @endif
                                                                            </td>
                                                                        </tr>
                                                                        

                                                                        <tr>
                                                                            <th scope="row"> Net Profit</th>
                                                                            <td>
                                                                                @if ($trip->net_profit)
                                                                                {{$default_currency?->name}} {{$default_currency?->symbol}}{{number_format($trip->net_profit,2)}}
                                                                                @endif
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th scope="row">Net Profit Percentage</th>
                                                                            <td>
                                                                                @if (isset($trip->net_profit_percentage))
                                                                                    {{number_format($trip->net_profit_percentage,2)}}%
                                                                                @endif
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th scope="row">Markup Percentage</th>
                                                                            <td>
                                                                                @if (isset($trip->markup_percentage))
                                                                                    {{number_format($trip->markup_percentage,2)}}%
                                                                                @endif
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th scope="row"> Cost Per Kilometer</th>
                                                                            <td>
                                                                                @if (isset($cpk))
                                                                                    {{$this->company->currency ? $this->company->currency->name : ""}}   {{$this->company->currency ? $this->company->currency->symbol : ""}}{{number_format($cpk,2)}} / Km
                                                                                @endif
                                                                            </td>
                                                                        </tr>
                                                                       
                                                                        @if ($trip->agent)
                                                                        <tr>
                                                                            <th scope="row">  Agent</th>
                                                                            <td>
                                                                                <a href="{{ route('agents.show',$trip->agent->id) }}" style="color:blue">   {{$trip->agent->name}} {{$trip->agent->surname}}</a>
                                                                            </td>
                                                                        </tr>
                                                                        @endif
                                                                        @if ($trip->commission)
                                                                        <tr>
                                                                            <th scope="row"> Agent Commission</th>
                                                                            <td>
                                                                                {{$trip->currency ? $trip->currency->symbol : ""}}{{number_format($trip->commission->amount ? $trip->commission->amount : 0,2)}} @ {{$trip->commission ? $trip->commission->commission : ""}}%
                                                                            </td>
                                                                        </tr>
                                                                        @endif
                                                                @endif
                                                            @endif
                                                            @if ($trip->comments)
                                                            <tr>
                                                                <th scope="row"> Trip Comments</th>
                                                                <td>
                                                                    {{$trip->comments}}
                                                                </td>
                                                            </tr>
                                                            @endif
                                                            <tr>
                                                                <th scope="row"> Authorization</th>
                                                                <td>
                                                                    <span class="label label-{{($trip->authorization == 'approved') ? 'success' : (($trip->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($trip->authorization == 'approved') ? 'approved' : (($trip->authorization == 'rejected') ? 'rejected' : 'pending') }}</span>
                                                                </td>
                                                            </tr>
                                                            @if ($trip->reason)
                                                                <tr>
                                                                    <th scope="row">Authorization Comments</th>
                                                                    <td>
                                                                        {{$trip->reason}}
                                                                    </td>
                                                                </tr>
                                                            @endif
                                                            @if (isset($trip->authorized_by_id))
                                                                <tr>
                                                                    <th scope="row">   Authorized By</th>
                                                                    <td>
                                                                       
                                                                        {{ $authorizer->name }} {{ $authorizer->surname }}
                                                                    </td>
                                                                </tr>
                                                            @endif
                                                        </tbody>
                                                    </table>  

                                                   
                                                  
                                               
                                                    <br>
                                                    <br>
                                                    <!-- /.col-xs-12 -->
                                                  

                                                </div>



                                                <!-- /.panel-body -->
                                            </div>
                                            <!-- /.panel -->

                                        </div>

                                    </div>

                                </div>
                                <!-- /.tab-pane -->


                                <div role="tabpanel" class="tab-pane " id="destinations">
                                    <div class="col-md-12 p-n">
                                        <div class="col-md-12">
                                            <div class="panel panel-info">
                                                <div class="panel-heading">
                                                    <div class="panel-title">
                                                        <h5>Other Offloading Points</h5>
                                                    </div>
                                                </div>
                                                <div class="panel-body overflow-x-auto">
                                                   @livewire('trips.destinations', ['trip' => $trip])
                                                </div>
                                                <!-- /.panel-body -->
                                            </div>
                                            <!-- /.panel -->

                                        </div>

                                    </div>
                                </div>
                                <div role="tabpanel" class="tab-pane " id="documents">
                                    <div class="col-md-12 p-n">
                                        <div class="col-md-12">
                                            <div class="panel panel-info">
                                                <div class="panel-heading">
                                                    <div class="panel-title">
                                                        <h5>Trip Document(s)</h5>
                                                    </div>
                                                </div>
                                                <div class="panel-body overflow-x-auto">
                                                   @livewire('trips.documents', ['trip' => $trip])

                                                </div>
                                                <!-- /.panel-body -->
                                            </div>
                                            <!-- /.panel -->

                                        </div>

                                    </div>
                                </div>
                           
                                <div role="tabpanel" class="tab-pane" id="expenses">
                                    <div class="col-md-12 p-n">
                                        <div class="col-md-12">
                                            <div class="panel panel-info">
                                                <div class="panel-heading">
                                                    <div class="panel-title">
                                                        <h5>Trip Expenses</h5>
                                                    </div>
                                                </div>
                                                <div class="panel-body overflow-x-auto">
                                                   @livewire('trips.expenses', ['trip' => $trip])
                                                </div>
                                                <!-- /.panel-body -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                             
                                <div role="tabpanel" class="tab-pane " id="delivery_note">
                                    @livewire('trips.delivery-notes', ['trip' => $trip])
                                </div>
                                <div role="tabpanel" class="tab-pane " id="locations">
                                    <div class="col-md-12 p-n">
                                        <div class="col-md-12">
                                            <div class="panel panel-info">
                                                <div class="panel-heading">
                                                    <div class="panel-title">
                                                        <h5>Trip Location Update(s)</h5>
                                                    </div>
                                                </div>
                                                <div class="panel-body overflow-x-auto">
                                                   @livewire('trips.locations', ['trip' => $trip])
                                                </div>
                                                <!-- /.panel-body -->
                                            </div>
                                            <!-- /.panel -->
                                        </div>

                                    </div>
                                </div>
                                <div role="tabpanel" class="tab-pane " id="breakdowns">
                                    <div class="col-md-12 p-n">
                                        <div class="col-md-12">
                                            <div class="panel panel-info">
                                                <div class="panel-heading">
                                                    <div class="panel-title">
                                                        <h5>Trip Incident(s)</h5>
                                                    </div>
                                                </div>
                                                <div class="panel-body overflow-x-auto">
                                                   @livewire('trips.breakdowns', ['trip' => $trip])
                                                </div>
                                                <!-- /.panel-body -->
                                            </div>
                                            <!-- /.panel -->
                                        </div>

                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="btn-group pull-right mt-10" >
                                            <div class="row" style="float: right; padding-right:30px">
                                                <a href="#"  onclick="goBack()" class="btn btn-default" ><i class="fas fa-arrow-left color-default"></i> <strong>Back</strong> </a> 
                                                @if ($trip->authorization == "pending" || $trip->authorization == "rejected")
                                                <a href="#" wire:click="authorize({{$trip->id}})"><i class="fas fa-gavel color-success"></i> Authorization</a>    
                                                @endif
                                            </div>
                                        </div>
                                       
                                    </div>
                                </div>
                               
                            </div>
                            <!-- /.tab-content -->
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-md-12 -->
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
                <form wire:submit.prevent="updateAuthorization()" >
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
    <!--Trip Status Modal-->
 

 
</div>
