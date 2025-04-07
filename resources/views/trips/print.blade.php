@extends('layouts.previews')
@section('title')
    Trip Sheet Preview|@if (isset(Auth::user()->employee->company))
    {{Auth::user()->employee->company->name}}
    @elseif (Auth::user()->company)
    {{Auth::user()->company->name}}
    @endif
@endsection
@section('content')

<div class="container">
    <div class="card">
        <div class="card-body">
            <div id="invoice">
                <div class="invoice overflow-auto">
                    <div style="min-width: 600px">
                        <header>
                            <div class="row">
                                <div class="col">
                                    <a href="javascript:;">
                                        <img src="{{asset('images/uploads/'.$company->logo)}}" width="150" alt="">
                                    </a>
                                </div>
                                <div class="col company-details">
                                    <h4 class="name" >
                                        <a target="_blank" href="javascript:;" style="color:  {{Auth::user()->employee->company ? Auth::user()->employee->company->color : Auth::user()->company->color }}">
                                            {{$company->name}}
                                        </a>
                                    </h4>
                                    <div>{{$company->street_address}} {{$company->suburb}} <br>
                                        {{$company->city}}, {{$company->country}}</div>
                                    <div>
                                        {{$company->phonenumber}}
                                        @if ($company->second_phonenumber)
                                        | {{$company->second_phonenumber}}
                                        @endif
                                        @if ($company->third_phonenumber)
                                        | {{$company->third_phonenumber}}
                                        @endif
                                    </div>
                                  
                                    
                                    <div>{{$company->email}}</div>
                                    @if ($company->second_email)
                                    <div>{{$company->second_email}}</div>
                                    @endif
                                    @if ($company->third_email)
                                    <div>{{$company->third_email}}</div>
                                    <br>
                                    @endif
                                </div>
                            </div>
                            <div style="padding-top: 25px; padding-bottom:15px">
                                <center><h2>TRIP SHEET</h2>  </center>
                            </div>
                        </header>
                        <main>
                            <div class="row contacts">
                                <div class="col invoice-to">
                                    <div class="text-gray-light">CUSTOMER:</div>
                                    <h5 class="to">{{$customer->name}}</h5>
                                    <div class="address"> 
                                        @if ($customer->street_address)
                                        {{$customer->street_address}} 
                                        @endif
                                        @if ($customer->suburb)
                                        {{$customer->suburb}}
                                        @endif
                                        {{$customer->city}} {{$customer->country}}
                                    </div>
                                    <div class="email"><a href="mailto:{{$customer->email}}">{{$customer->email}}</a> </div>
                                </div>
                                <div class="col invoice-details">
                                    <div class="date"> <strong>Trip Number:</strong> {{$trip->trip_number}}{{$trip->trip_ref ? "/".$trip->trip_ref : ""}}</div>
                                    <div class="date"><strong>Start Date:</strong>
                                      @php
                                        $pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
                                    @endphp
                                    @if ((preg_match($pattern, $trip->start_date)))
                                        {{Carbon\Carbon::parse($trip->start_date)->format('d M Y h:i A')}}
                                    @else
                                        {{ $trip->start_date }}
                                    @endif</div>
                                    @if ($trip->end_date)
                                    <div class="date"><strong>Estimated End Date:</strong>
                                      @php
                                        $pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
                                    @endphp
                                    @if ((preg_match($pattern, $trip->end_date)))
                                        {{Carbon\Carbon::parse($trip->end_date)->format('d M Y h:i A')}}
                                    @else
                                        {{ $trip->end_date }}
                                    @endif</div>
                                    @endif
                                </div>
                            </div>
                            <table>

                                <tbody>
                                    <tr>
                                        <th class="text-center"><strong>Trip#</strong></th>
                                        <td class="text-center">{{$trip->trip_number}}{{$trip->trip_ref ? "/".$trip->trip_ref : ""}}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-center"><strong>Transporter</strong></th>
                                        <td class="text-center"> {{$trip->transporter ? $trip->transporter->name : ""}}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-center"><strong>Trip Type</strong></th>
                                        <td class="text-center"> {{$trip->trip_type ? $trip->trip_type->name : ""}}</td>
                                    </tr>
                                    @if ($trip->manifest_number)
                                    <tr>
                                        <th class="text-center"><strong>Manifest Number</strong></th>
                                        <td class="text-center"> {{$trip->manifest_number}}</td>
                                    </tr>
                                    @endif
                                    @if ($trip->cd3_number)
                                    <tr>
                                        <th class="text-center"><strong>CD3 Number</strong></th>
                                        <td class="text-center"> {{$trip->cd3_number}}</td>
                                    </tr>
                                    @endif
                                    @if ($trip->cd1_number)
                                    <tr>
                                        <th class="text-center"><strong>CD1 Number</strong></th>
                                        <td class="text-center"> {{$trip->cd1_number}}</td>
                                    </tr>
                                    @endif
                                    @if ($trip->borders->count()>0)
                                    <tr>
                                        <th class="text-center"><strong>Borders</strong></th>
                                        <td class="text-center"> 
                                            @foreach ($trip->borders as $border)
                                                {{ $border->name }} <br>
                                            @endforeach
                                        </td>
                                    </tr>
                                    @endif
                                    @if ($trip->clearing_agents->count()>0)
                                    <tr>
                                        <th class="text-center"><strong>Clearing Agents</strong></th>
                                        <td class="text-center"> 
                                            @foreach ($trip->clearing_agents as $clearing_agent)
                                            {{$clearing_agent->name}} -> Email: {{$clearing_agent->email}} Phonenumber: {{$clearing_agent->phonenumber}} <br>
                                            @endforeach
                                        </td>
                                    </tr>
                                    @endif
                                    @if ($trip->trip_group)
                                    <tr>
                                        <th class="text-center"> <strong>Trip Tracking</strong></th>
                                        <td class="text-center">{{$trip->trip_group ? $trip->trip_group->name : ""}} </td>
                                    </tr>
                                    @endif
                                  
                                    @if ($trip->horse)
                                        <tr>
                                            <th class="text-center"> <strong>Horse</strong></th>
                                            <td class="text-center">{{$trip->horse->horse_make ? $trip->horse->horse_make->name : ""}} {{$trip->horse->horse_model ? $trip->horse->horse_model->name : ""}} {{$trip->horse ? $trip->horse->registration_number : ""}} </td>
                                        </tr>
                                        <tr>
                                            <th class="text-center"> <strong>Fleet Number</strong></th>
                                            <td class="text-center">{{$trip->horse ? $trip->horse->fleet_number : ""}}</td>
                                        </tr>
                                    @elseif ($trip->vehicle)
                                        <tr>
                                            <th class="text-center"> <strong>Vehicle</strong></th>
                                            <td class="text-center">{{$trip->horse->horse_make ? $trip->horse->horse_make->name : ""}} {{$trip->horse->horse_model ? $trip->horse->horse_model->name : ""}} {{$trip->horse ? $trip->horse->registration_number : ""}}</td>
                                        </tr>
                                    @endif
                                    @if ($trip->trailers->count()>0)
                                    <tr>
                                        <th class="text-center"> <strong>Trailer(s)</strong></th>
                                        <td class="text-center">
                                            @foreach ($trip->trailers as $trailer)
                                                {{$trailer->make}} {{$trailer->model}} {{$trailer->registration_number}} {{"| ".$trailer->fleet_number}} <br>
                                            @endforeach
                                        </td>
                                    </tr>
                                    @endif
                                   
                                    <tr>
                                        <th class="text-center"><strong>Driver</strong></th>
                                        <td class="text-center">{{$trip->driver ? $trip->driver->employee->name : ""}} {{$trip->driver ? $trip->driver->employee->surname : ""}} {{$trip->driver ? $trip->driver->employee->idnumber : ""}}</td>
                                    </tr>
                                    @if ($trip->driver_allowances->count()>0)
                                    @foreach ($trip->driver_allowances as $allowance)
                                    <tr>
                                        <th class="text-center"><strong>{{ $allowance->allowance ? $allowance->allowance->name : "" }}</strong></th>
                                        <td class="text-center"> 
                                            {{ $allowance->currency ? $allowance->currency->name : ""}} {{ $allowance->currency ? $allowance->currency->symbol : ""}}{{ number_format($allowance->amount)}}
                                        </td>
                                    </tr>
                                    @endforeach
                                @endif
                                    <tr>
                                        <th class="text-center"> <strong>Start Date</strong></th>
                                        <td class="text-center">
                                            @php
                                                $pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
                                            @endphp
                                            @if ((preg_match($pattern, $trip->start_date)))
                                                {{Carbon\Carbon::parse($trip->start_date)->format('d M Y h:i A')}}
                                            @else
                                                {{ $trip->start_date }}
                                            @endif
                                            
                                        </td>
                                    </tr>
                                    @if ($trip->end_date)
                                    <tr>
                                        <th class="text-center"> <strong>Estimated End Date</strong></th>
                                        <td class="text-center">
                                            @php
                                                $pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
                                            @endphp
                                            @if ((preg_match($pattern, $trip->end_date)))
                                                {{Carbon\Carbon::parse($trip->end_date)->format('d M Y h:i A')}}
                                            @else
                                                {{ $trip->end_date }}
                                            @endif
                                        </td>
                                    </tr> 
                                    @endif
                                        @php
                                            $pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
                                        @endphp
                                         @if ((preg_match($pattern, $trip->start_date)) && (preg_match($pattern, $trip->end_date)) )
                                            <tr>
                                                <th class="text-center"> <strong>Standard Trip Duration</strong></th>
                                                <td class="text-center">
                                                    {{ \Carbon\Carbon::parse($trip->start_date)->diffInDays($trip->end_date) }} Day(s)
                                                </td>
                                            </tr> 
                                        @endif
                                    <tr>
                                        <th class="text-center"> <strong>From</strong></th>
                                        <td class="text-center">{{App\Models\Destination::find($trip->from)->country ? App\Models\Destination::find($trip->from)->country->name : ""}} {{App\Models\Destination::find($trip->from) ? App\Models\Destination::find($trip->from)->city : ""}}  </td>
                                    </tr>
                                    <tr>
                                        <th class="text-center"> <strong>To</strong></th>
                                        <td class="text-center">{{App\Models\Destination::find($trip->from)->country ? App\Models\Destination::find($trip->to)->country->name : ""}} {{App\Models\Destination::find($trip->from) ? App\Models\Destination::find($trip->to)->city : ""}}</td>
                                    </tr>
                                    @if ($trip->loading_point)
                                    <tr>
                                        <th class="text-center"> <strong>Loading Point</strong></th>
                                        <td class="text-center">{{$trip->loading_point ? $trip->loading_point->name : ""}}</td>
                                    </tr>
                                    @endif
                                    @if ($trip->offloading_point)
                                    <tr>
                                        <th class="text-center"> <strong>Offloading Point</strong></th>
                                        <td class="text-center">{{$trip->offloading_point ? $trip->offloading_point->name : ""}}</td>
                                    </tr>
                                    @endif
                                    @if ($trip->distance)
                                    <tr>
                                        <th class="text-center"><strong>Distance</strong></th>
                                        <td class="text-center">{{$trip->distance."Kms"}}</td>
                                    </tr>
                                    @endif
                                    @if ($trip->route)
                                    <tr>
                                        <th class="text-center"><strong>Route</strong></th>
                                        <td class="text-center"> {{$trip->route ? $trip->route->name : ""}}</td>
                                    </tr>
                                @endif
                                @if ($trip->truck_stops->count()>0)
                                    <tr>
                                        <th class="text-center"><strong>Designated Truck Stops</strong></th>
                                        <td class="text-center"> 
                                            @foreach ($trip->truck_stops as $truck_stop)
                                                {{ $truck_stop->name }} {{ $truck_stop->rating }}
                                            @endforeach
                                        </td>
                                    </tr>
                                @endif

                                    @if ($trip->fuels->where('authorization','approved')->count()>0)
                                    <tr>
                                        <th class="text-center"><strong>Fuel Orders</strong></th>
                                        <td class="text-center"> 
                                            @foreach ($trip->fuels as $fuel)
                                                {{$fuel->order_number}} {{$fuel->fillup == 1 ? "Initial" : "Topup"}} {{$fuel->container ? $fuel->container->name : ""}} {{$fuel->type}} {{ $fuel->quantity."Litres" }} <br>
                                            @endforeach
                                        </td>
                                    </tr>
                                    @endif
                                   
                                    <tr>
                                        <th class="text-center"><strong>Cargo</strong></th>
                                        <td class="text-center">{{$trip->cargo ? $trip->cargo->name : ""}}</td>
                                    </tr>
                                   
                                    @if ($trip->weight)
                                    <tr>
                                        <th class="text-center"><strong> Scheduled Weight</strong></th>
                                        <td class="text-center"> {{$trip->weight}}Tons</td>
                                    </tr>
                                    @endif
                                    @if ($trip->quantity)
                                        <tr>
                                            <th class="text-center"><strong>Scheduled Quantity</strong></th>
                                            <td class="text-center"> {{$trip->quantity}} {{$trip->measurement}}</td>
                                        </tr>
                                    @endif
                                    
                                  
                                    @if ($trip->litreage)
                                    <tr>
                                        <th class="text-center"><strong>Scheduled Litreage @ Ambient</strong></th>
                                        <td class="text-center"> {{$trip->litreage}}{{$trip->measurement}}</td>
                                    </tr> 
                                    @endif
                                    
                                    @if ($trip->litreage_at_20)
                                    <tr>
                                        <th class="text-center"><strong> Scheduled Litreage @ 20 Degrees</strong></th>
                                        <td class="text-center"> {{$trip->litreage_at_20}}{{$trip->measurement}}</td>
                                    </tr>
                                    @endif
                                   
                                    @if ($trip->delivery_note->loaded_weight)
                                    <tr>
                                        <th class="text-center"><strong> Loaded Weight</strong></th>
                                        <td class="text-center"> {{$trip->delivery_note->loaded_weight}}Tons</td>
                                    </tr>
                                    @endif
                                    @if ($trip->delivery_note->loaded_quantity)
                                        <tr>
                                            <th class="text-center"><strong>Loaded Quantity</strong></th>
                                            <td class="text-center"> {{$trip->delivery_note->loaded_quantity}} {{$trip->measurement}}</td>
                                        </tr>
                                    @endif
                                    
                                  
                                    @if ($trip->delivery_note->loaded_litreage)
                                    <tr>
                                        <th class="text-center"><strong>Loaded Litreage @ Ambient</strong></th>
                                        <td class="text-center"> {{$trip->delivery_note->loaded_litreage}}{{$trip->measurement}}</td>
                                    </tr> 
                                    @endif
                                    
                                    @if ($trip->delivery_note->loaded_litreage_at_20)
                                    <tr>
                                        <th class="text-center"><strong> Loaded Litreage @ 20 Degrees</strong></th>
                                        <td class="text-center"> {{$trip->loaded_litreage_at_20}}{{$trip->measurement}}</td>
                                    </tr>
                                    @endif
                                   
                                    @if ($trip->delivery_note->offloaded_weight)
                                    <tr>
                                        <th class="text-center"><strong> Offloaded Weight</strong></th>
                                        <td class="text-center"> {{$trip->delivery_note->offloaded_weight}}Tons</td>
                                    </tr>
                                    @endif
                                    @if ($trip->delivery_note->offloaded_quantity)
                                        <tr>
                                            <th class="text-center"><strong>Offloaded Quantity</strong></th>
                                            <td class="text-center"> {{$trip->delivery_note->offloaded_quantity}} {{$trip->measurement}}</td>
                                        </tr>
                                    @endif
                                    
                                  
                                    @if ($trip->delivery_note->offloaded_litreage)
                                    <tr>
                                        <th class="text-center"><strong>Offloaded Litreage @ Ambient</strong></th>
                                        <td class="text-center"> {{$trip->delivery_note->offloaded_litreage}}{{$trip->measurement}}</td>
                                    </tr> 
                                    @endif
                                    
                                    @if ($trip->delivery_note->offloaded_litreage_at_20)
                                    <tr>
                                        <th class="text-center"><strong> Offloaded Litreage @ 20 Degrees</strong></th>
                                        <td class="text-center"> {{$trip->offloaded_litreage_at_20}}{{$trip->measurement}}</td>
                                    </tr>
                                    @endif
                                 
                                    @if ((isset($trip->delivery_note->loaded_weight) && $trip->delivery_note->loaded_weight > 0) && (isset($trip->delivery_note->offloaded_weight) &&  $trip->delivery_note->offloaded_weight > 0 ))
                                        <tr>
                                        <th class="text-center"><strong> Weight Loss</strong></th>
                                        <td class="text-center"> 
                                            @php
                                            if ((isset($trip->delivery_note->loaded_weight) && $trip->delivery_note->loaded_weight > 0 ) && ( isset($trip->delivery_note->offloaded_weight) && $trip->delivery_note->offloaded_weight > 0 )) {
                                                $weight_loss = $trip->delivery_note->loaded_weight - $trip->delivery_note->offloaded_weight;
                                            }else {
                                                $weight_loss = Null;
                                            }
                                        @endphp
                                        <!-- /.col-md-6 -->
                                        @if (isset($weight_loss) && $weight_loss > 0)
                                        <div style="color: red">
                                            {{ $weight_loss }} Tons
                                        </div>
                                        @elseif (isset($weight_loss) && $weight_loss == 0)
                                        <div >
                                            {{ $weight_loss }} Tons
                                           </div>
                                        @else
                                        <div>
                                        No Offloaded Weight Recorded
                                        </div>
                                        @endif
                                        </td>
                                        </tr>
                                    @endif
                                    @if ((isset($trip->delivery_note->loaded_quantity) && $trip->delivery_note->loaded_quantity > 0 ) && (isset($trip->delivery_note->offloaded_quantity) && $trip->delivery_note->offloaded_quantity > 0))
                                    <tr>
                                        <th class="text-center"><strong> Quantity Loss</strong></th>
                                        <td class="text-center"> 
                                            @php
                                            if ((isset($trip->delivery_note->loaded_quantity) && $trip->delivery_note->loaded_quantity > 0 ) && (isset($trip->delivery_note->offloaded_quantity) && $trip->delivery_note->offloaded_quantity > 0) ) {
                                                $quantity_loss = $trip->delivery_note->loaded_quantity - $trip->delivery_note->offloaded_quantity;
                                            }else {
                                                $quantity_loss = Null;
                                            }
                                        @endphp
                                        <!-- /.col-md-6 -->
                                        @if (isset($quantity_loss) && $quantity_loss > 0)
                                        <div  style="color: red">
                                         {{ $quantity_loss }} {{$trip->delivery_note->measurement}}
                                        </div>
                                        @elseif (isset($quantity_loss) && $quantity_loss == 0)
                                        <div >
                                            {{ $quantity_loss }} {{$trip->delivery_note->measurement}}
                                           </div>
                                        @else
                                        <div >
                                        No Offloading Quantity Recorded
                                        </div>
                                        @endif
                                        </td>
                                    </tr>
                                    @endif
                                    @if ((isset($trip->delivery_note->loaded_litreage) && $trip->delivery_note->loaded_litreage > 0 ) && (isset($trip->delivery_note->offloaded_litreage) && $trip->delivery_note->offloaded_litreage > 0 ))
                                    <tr>
                                        <th class="text-center"><strong>Litreage Loss @ Ambient Temperature</strong></th>
                                        <td class="text-center"> 
                                            @php
                                            if ((isset($trip->delivery_note->loaded_litreage) && $trip->delivery_note->loaded_litreage > 0) && (isset($trip->delivery_note->offloaded_litreage) && $trip->delivery_note->offloaded_litreage > 0)) {
                                                $litreage_loss = $trip->delivery_note->loaded_litreage - $trip->delivery_note->offloaded_litreage;
                                            }else{
                                                $litreage_loss = Null;
                                            }
                                        @endphp
                                        <!-- /.col-md-6 -->
                                        @if (isset($litreage_loss) && $litreage_loss > 0)
                                        <div class="col-xs-6 p-n" style="color: red">
                                         {{ $litreage_loss }} {{$trip->delivery_note->measurement}}
                                        </div>
                                        @elseif (isset($litreage_loss) && $litreage_loss == 0)
                                        <div class="col-xs-6 p-n">
                                            {{ $litreage_loss }} {{$trip->delivery_note->measurement}}
                                           </div>
                                        @else
                                        <div class="col-xs-6 p-n">
                                        No Offloaded Litreage recorded
                                        </div>
                                        @endif
                                        </td>
                                    </tr>
                                    @endif
                                    @if ((isset($trip->delivery_note->loaded_litreage_at_20) && $trip->delivery_note->loaded_litreage_at_20 > 0) && (isset($trip->delivery_note->offloaded_litreage_at_20) && $trip->delivery_note->offloaded_litreage_at_20 > 0))
                                    <tr>
                                        <th class="text-center"><strong> Litreage Loss @ 20 Degrees</strong></th>
                                        <td class="text-center"> 
                                            @php
                                            if ((isset($trip->delivery_note->loaded_litreage_at_20) && $trip->delivery_note->loaded_litreage_at_20 > 0 ) && (isset($trip->delivery_note->offloaded_litreage_at_20) && $trip->delivery_note->offloaded_litreage_at_20 > 0)) {
                                                $litreage_at_20_loss = $trip->delivery_note->loaded_litreage_at_20 - $trip->delivery_note->offloaded_litreage_at_20;
                                            }else {
                                                $litreage_at_20_loss = Null;
                                            }
                                        @endphp
                                        <!-- /.col-md-6 -->
                                        @if (isset($litreage_at_20_loss) && $litreage_at_20_loss > 0)
                                        <div class="col-xs-6 p-n" style="color: red">
                                         {{ $litreage_at_20_loss }} {{$trip->delivery_note->measurement}}
                                        </div>
                                        @elseif (isset($litreage_at_20_loss) && $litreage_at_20_loss == 0)
                                        <div class="col-xs-6 p-n">
                                            {{ $litreage_at_20_loss }} {{$trip->delivery_note->measurement}}
                                           </div>
                                        @else
                                        <div class="col-xs-6 p-n">
                                        No Offloaded Litreage recorded
                                        </div>
                                        @endif
                                        </td>
                                    </tr>
                                    @endif
                                    @if ((isset($trip->delivery_note->loaded_freight) && $trip->delivery_note->loaded_freight > 0) && (isset($trip->delivery_note->offloaded_freight) && $trip->delivery_note->offloaded_freight > 0))
                                    <tr>
                                        <th class="text-center"><strong> Freight Loss</strong></th>
                                        <td class="text-center"> 
                                            @php
                                            if ((isset($trip->delivery_note->loaded_freight) && $trip->delivery_note->loaded_freight > 0) && (isset($trip->delivery_note->offloaded_freight) && $trip->delivery_note->offloaded_freight > 0)) {
                                                $freight_loss = $trip->delivery_note->loaded_freight - $trip->delivery_note->offloaded_freight;
                                            }else {
                                                $freight_loss = Null;
                                            }
                                        @endphp
                                        <!-- /.col-md-6 -->
                                        @if (isset($freight_loss) && $freight_loss > 0)
                                        <div class="col-xs-6 p-n" style="color: red">
                                            {{ $trip->currency ? $trip->currency->name : "" }} {{ $trip->currency ? $trip->currency->symbol : "" }}{{ number_format($freight_loss,2) }}
                                        </div>
                                        @elseif (isset($freight_loss) && $freight_loss == 0)
                                        <div class="col-xs-6 p-n">
                                            {{ $trip->currency ? $trip->currency->name : "" }} {{ $trip->currency ? $trip->currency->symbol : "" }}{{ number_format($freight_loss,2) }}
                                           </div>
                                        @else
                                        <div class="col-xs-6 p-n">
                                            No Offloaded Freight Recorded
                                            </div>
                                        @endif
                                        </td>
                                    </tr>
                                    @endif

                                    @if ($trip->currency)
                                        <tr>
                                            <th class="text-center"><strong>Currency</strong></th>
                                            <td class="text-center"> {{$trip->currency ? $trip->currency->name : ""}}</td>
                                        </tr>
                                    @endif
                                    @if ($trip->rate)
                                    <tr>
                                        <th class="text-center"><strong>Rate</strong></th>
                                        <td class="text-center">{{$trip->currency ? $trip->currency->symbol : ""}}{{number_format($trip->rate,2)}}</td>
                                    </tr>
                                    @endif
                                    @if ($trip->freight)
                                    <tr>
                                        <th class="text-center"><strong>Freight</strong></th>
                                        <td class="text-center"> {{$trip->currency ? $trip->currency->symbol : ""}}{{number_format($trip->freight,2)}}</td>
                                    </tr>
                                    @endif
                                  
                                    <tr>
                                        <th class="text-center"><strong>Trip Status</strong></th>
                                        <td class="text-center"> {{$trip->trip_status}}</td>
                                    </tr>
                                    @if ($trip->payment_status)
                                    <tr>
                                        <th class="text-center"><strong>Payment Status</strong></th>
                                        <td class="text-center"> {{$trip->payment_status}}</td>
                                    </tr>
                                    @endif
                                   
                                    @if ($trip->comments)
                                    <tr>
                                        <th class="text-center"><strong>Comments</strong></th>
                                        <td class="text-center"> {{$trip->comments}}</td>
                                    </tr>
                                    @endif
                                    @if (isset($trip->broker))
                                    <tr>
                                        <th class="text-center"><strong>Broker</strong></th>
                                        <td class="text-center"> {{$trip->broker->name}}</td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <th class="text-center"><strong>Checked By</strong></th>
                                        <td class="text-center"> {{$trip->user->employee ? $trip->user->employee->name : "" }} {{$trip->user->employee ? $trip->user->employee->surname : "" }}</td>
                                    </tr>
                                    @php
                                        $user = App\Models\User::find($trip->authorized_by_id);
                                    @endphp
                                    @if ($user)
                                        <tr>
                                            <th class="text-center"><strong>Authorized By</strong></th>
                                        
                                            <td class="text-center"> {{$user->employee ? $user->employee->name : "" }} {{$user->employee ? $user->employee->surname : "" }}</td>
                                        </tr>
                                    @endif
                                   
                                </tbody>

                            </table>
                        </main>
                        <footer style="   position: fixed; 
                        bottom: 0px; 
                        left: 0px; 
                        right: 0px;
                        height: 50px;">{{ucfirst($company->name)}} Trip Sheet For {{$trip->trip_number}}!!</footer>
                    </div>
                    <!--DO NOT DELETE THIS div. IT is responsible for showing footer always at the bottom-->
                    <div></div>
                </div>
            </div>
        </div>
    </div>
</div>
@section('extra-js')
<script>
    window.addEventListener("load", window.print());
  </script>
@endsection
@endsection
