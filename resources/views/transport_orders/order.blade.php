<!doctype html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title>Transport Order</title>
    @include('includes.css')
</head>
<body>

<div class="container">
    <div class="card">
        <div class="card-body">
            <div id="invoice"  style="font-size: 16px">
                <div class="invoice overflow-auto">
                    <div style="margin-left: -30px; margin-right:-30px">
                        <header style="margin-top:-25px; padding-top:-25px; padding-bottom:10px" >
                        <div class="row">
                            <div class="col">
                                <a href="javascript:;">
                                    <img src="{{asset('images/uploads/'.$company->logo)}}" width="150" alt="">
                                </a>
                            </div>
                             <div class="col company-details" style="margin-top:-100px;">
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
                            <center><h2>TRANSPORTATION ORDER</h2>  </center>
                        </div>
                    </header>
                    <main>
                        <div class="row contacts">
                            <div class="col invoice-to">
                                <div class="text-gray-light">TRANSPORTATION ORDER FOR</div>
                                @if ($trip->customer)
                                <h5 class="to">{{$trip->customer->name}}</h5>
                                <div class="address"> 
                                    @if ($trip->customer->street_address)
                                    {{$trip->customer->street_address}} 
                                    @endif
                                    @if ($trip->customer->suburb)
                                    {{$trip->customer->suburb}}
                                    @endif
                                    {{$trip->customer->city}} {{$trip->customer->country}}
                                </div>
                                <div class="email"><a href="mailto:{{$trip->customer->email}}">{{$trip->customer->email}}</a> </div>
                                @endif
                        
                            </div>
                            <div class="col invoice-details" style="margin-top: -100px;">
                                <div class="date"> <strong>Trip Number:</strong> {{$trip->trip_number}}{{ $trip->trip_ref ? '/'.$trip->trip_ref : ""  }}</div>
                                <div class="date"><strong>Issue Date:</strong>
                                    @php
                                        $pattern = '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/';
                                    @endphp
                                    @if ((preg_match($pattern, $trip->created_at)))
                                        {{Carbon\Carbon::parse($trip->created_at)->format('d M Y h:i A')}}
                                    @else
                                        {{ $trip->created_at }}
                                    @endif
                                </div>
                                <div class="date"><strong>Loading Point:</strong> {{$trip->loading_point ? $trip->loading_point->name : ""}}</div>
                            </div>
                        </div>
                        <table class="table table-striped">

                            <tbody>
                                @if ($trip->start_date)
                                <tr>
                                    <th class="text-center"><strong>Date & Time of Dispatch</strong></th>
                                    <td class="text-center">
                                        
                                        @if ((preg_match($pattern, $trip->start_date)))
                                        {{Carbon\Carbon::parse($trip->start_date)->format('d M Y h:i A')}}
                                        @else
                                        {{ $trip->start_date }}
                                        @endif
                                    </td>
                                </tr>
                                @endif
                                <tr>
                                    <th class="text-center"><strong>Transporter</strong></th>
                                    <td class="text-center"> {{ $trip->transporter ? $trip->transporter->name : ""}}</td>
                                </tr>
                                <tr>
                                    <th class="text-center"> <strong>Driver</strong></th>
                                    <td class="text-center">
                                        @if ($trip->driver)
                                            {{$trip->driver->employee ? $trip->driver->employee->name : ""}} {{$trip->driver->employee ? $trip->driver->employee->surname : ""}} {{$trip->driver->employee ? $trip->driver->employee->idnumber : ""}}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-center"><strong>Horse</strong></th>
                                    <td class="text-center">
                                        @if ($trip->horse)
                                            {{$trip->horse->horse_make ? $trip->horse->horse_make->name : "" }}  {{$trip->horse->horse_model ? $trip->horse->horse_model->name : "" }} {{$trip->horse ? $trip->horse->registration_number : "" }}  {{$trip->horse->fleet_number ? "(".$trip->horse->fleet_number.")" : "" }}
                                        @endif
                                    </td>
                                </tr>
                                        @if ($trip->trailers->count()>0)
                                        <tr>
                                            <th class="text-center"> <strong>Trailer(s)</strong></th>
                                            <td class="text-center">
                                                @foreach ($trip->trailers as $trailer)
                                                    {{$trailer->make}} {{$trailer->model}} {{$trailer->registration_number}} {{$trailer->fleet_number ? "(".$trailer->fleet_number.")" : ""}} <br>
                                                @endforeach
                                            </td>
                                        </tr>
                                        @endif

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
                                    <th class="text-center"><strong>Customer</strong></th>
                                    <td class="text-center"> {{$trip->customer ? $trip->customer->name : ""}}</td>
                                </tr>
                            
                                <tr>
                                    <th class="text-center"><strong>From</strong></th>
                                    <td class="text-center">
                                        @if (isset($origin))
                                            {{$origin->country ? $origin->country->name : ""}} {{ $origin->city }}        
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-center"><strong>To</strong></th>
                                    <td class="text-center"> 
                                        @if ($destination)
                                            {{$destination->country ? $destination->country->name : ""}} {{ $destination->city }}
                                        @endif
                                    </td>
                                </tr>
                            
                                <tr>
                                    <th class="text-center"><strong>Loading Point</strong></th>
                                    <td class="text-center"> {{$trip->loading_point ? $trip->loading_point->name : ""}}</td>
                                </tr>
                                <tr>
                                    <th class="text-center"><strong>Offloading Point</strong></th>
                                    <td class="text-center"> {{$trip->offloading_point ? $trip->offloading_point->name : ""}}</td>
                                </tr>
                                @if ($trip->distance)
                                <tr>
                                    <th class="text-center"><strong>Distance</strong></th>
                                    <td class="text-center">{{$trip->distance ? $trip->distance." Kms" : ""}}</td>
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
                                @if ($trip->borders->count()>0)
                                    <tr>
                                        <th class="text-center"><strong>Border(s)</strong></th>
                                        <td class="text-center">
                                            @foreach ($trip->borders as $border)
                                            {{$border->name}} <br>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endif
                                @if ($trip->clearing_agents->count()>0)
                                    <tr>
                                        <th class="text-center"><strong>Clearing Agent</strong></th>
                                        <td class="text-center"> 
                                            @foreach ($trip->clearing_agents as $clearing_agent)
                                                {{$clearing_agent->name}} -> Email: {{$clearing_agent->email}} Phonenumber: {{$clearing_agent->phonenumber}} <br>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endif
                            
                                <tr>
                                    <th class="text-center"><strong>Cargo</strong></th>
                                    <td class="text-center"> {{$trip->cargo ? $trip->cargo->name : ""}}</td>
                                </tr>
                            
                                @if ($trip->weight)
                                <tr>
                                    <th class="text-center"><strong>Weight</strong></th>
                                    <td class="text-center"> {{$trip->weight." Tons"}} </td>
                                </tr> 
                                @endif
                            
                                @if ($trip->quantity)
                                <tr>
                                    <th class="text-center"><strong>Quantity</strong></th>
                                    <td class="text-center"> {{$trip->quantity}} {{$trip->measurement}}</td>
                                </tr>
                                @endif
                                @if ($trip->litreage)
                                <tr>
                                    <th class="text-center"><strong>Litreage @ Ambient</strong></th>
                                    <td class="text-center"> {{$trip->litreage}} {{$trip->measurement}}</td>
                                </tr>
                                @endif
                                @if ($trip->litreage_at_20)
                                <tr>
                                    <th class="text-center"><strong>Litreage @ 20 Degrees</strong></th>
                                    <td class="text-center"> {{$trip->litreage_at_20}} {{$trip->measurement}}</td>
                                </tr>
                                @endif
                                @if ($trip->fuels->where('authorization','approved')->count()>0)
                                <tr>
                                    <th class="text-center"><strong>Fuel Orders</strong></th>
                                    <td class="text-center"> 
                                        @foreach ($trip->fuels as $fuel)
                                        {{$fuel->order_number}} {{$fuel->fillup == 1 ? "Initial" : "Topup"}} {{$fuel->container ? $fuel->container->name : ""}} {{$fuel->type}} {{ $fuel->quantity ? $fuel->quantity."Litres" : "" }} <br>
                                        @endforeach
                                    </td>
                                </tr>
                                @endif
                            @if ($trip->start_date && $trip->end_date )
                            <tr>
                                <th class="text-center"><strong>Target day(s) for the trip</strong></th>
                                <td class="text-center">
                                    
                                    @if ((preg_match($pattern, $trip->start_date)) && (preg_match($pattern, $trip->end_date)) )
                                    {{ \Carbon\Carbon::parse($trip->start_date)->diffInDays($trip->end_date) }} Day(s)
                                    @else
                                    From: {{ $trip->start_date }} - To {{ $trip->end_date }}
                                    @endif
                                
                                </td>
                            </tr> 
                            @endif
                                
                                <tr>
                                    <th class="text-center"><strong>Checked By</strong></th>
                                    <td class="text-center"> {{$trip->user ? $trip->user->name : ""}}</td>
                                </tr>
                                <tr>
                                    <th class="text-center"><strong>Authorized By</strong></th>
                                    <td class="text-center"> {{$trip->authorizer ? $trip->authorizer->name : ""}}</td>
                                </tr>

                            </tbody>

                        </table>
                    </main>
                    </center>
                        <footer style=" position: fixed; 
                                        bottom: -60px; 
                                        left: 0px; 
                                        right: 0px;
                                        height: 50px;">
                        {{ucfirst($company->name)}} TRANSPORT ORDER!!
                        </footer>
                    </center>
                </div>
                <!--DO NOT DELETE THIS div. IT is responsible for showing footer always at the bottom-->
                <div></div>
            </div>
            </div>
        </div>
    </div>
</div>

