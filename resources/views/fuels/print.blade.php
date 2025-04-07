@extends('layouts.previews')
@section('title')
    Fuel Order Print|@if (isset(Auth::user()->employee->company))
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
    									<img src="{{asset('images/uploads/'.$company->logo)}}" width="200" alt="">
									</a>
                                </div>
                                <div class="col company-details">
                                    <h3 class="name" >
                                        <a target="_blank" href="javascript:;" style="color:  {{Auth::user()->employee->company ? Auth::user()->employee->company->color : Auth::user()->company->color }}">
									{{$company->name}}
									</a>
                                    </h3>
                                    <div>{{$company->street_address}}, {{$company->suburb}}, {{$company->city}} {{$company->country}}</div>
                                    <div>{{$company->phonenumber}}
                                    </div>
                                    <div>{{$company->email}}</div>
                                </div>
                            </div>
                        </header>
                        <main>
                            <div class="row contacts">
                                <div class="col invoice-to">
                                    <div class="text-gray-light">FUEL ORDER TO:</div>
                                    <h5 class="to">{{$container->name}}</h5>
                                    <div class="address">{{$container->address}}</div>
                                    <div class="email"><a href="mailto:{{$container->email}}">{{$container->email}}</a></div>
                                </div>
                                <div class="col invoice-details">
                                    <div class="date"><strong>Fuel Order Number:</strong> {{$fuel->order_number}}</div>
                                    @if ($fuel->type = "Trip")
                                        @if ($fuel->trip)
                                        <div class="date"> <strong>Trip Number:</strong>{{$fuel->trip ? $fuel->trip->trip_number : ""}}{{$fuel->trip->trip_ref ? "/".$fuel->trip->trip_ref : ""}} </div>
                                        @endif
                                    @endif
                                    <div class="date"> <strong>Collection Date:</strong>  @php
                                        $pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
                                        @endphp
                                        @if ((preg_match($pattern, $fuel->date)) )
                                            {{ \Carbon\Carbon::parse($fuel->date)->format('d M Y g:i A')}}
                                        @else
                                        {{$fuel->date}}
                                        @endif    
                                    </div>
                                </div>
                            </div>
                            <table>

                                <tbody>
                                    <tr>
                                        <th class="text-center"><strong>Fuel Order #</strong></th>
                                        <td class="text-center"> <strong>{{$fuel->order_number}}</strong></td>
                                    </tr>
                                    @if ($fuel->trip)
                                        <tr>
                                            <th class="text-center"><strong>Trip #</strong></th>
                                            <td class="text-center"> <strong>{{$fuel->trip ? $fuel->trip->trip_number : ""}}{{$fuel->trip->trip_ref ? "/".$fuel->trip->trip_ref : ""}}</strong></td>
                                        </tr>
                                    @endif
                                   
                                    <tr>
                                        <th class="text-center"> <strong>Date</strong></th>
                                        <td class="text-center">
                                            @php
                                            $pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
                                            @endphp
                                            @if ((preg_match($pattern, $fuel->date)) )
                                                {{ \Carbon\Carbon::parse($fuel->date)->format('d M Y g:i A')}}
                                            @else
                                            {{$fuel->date}}
                                            @endif    
                                        </td>
                                    </tr>
                                    @if ($fuel->horse)
                                    <tr>
                                        <th class="text-center"> <strong>Horse</strong></th>
                                        <td class="text-center">
                                            {{$fuel->horse ? $fuel->horse->registration_number : ""}} {{$fuel->horse->horse_make ? $fuel->horse->horse_make->name : ""}} {{$fuel->horse->horse_model ? $fuel->horse->horse_model->name : ""}} {{$fuel->horse ? "| ".$fuel->horse->fleet_number : ""}}   
                                        </td>
                                    </tr>
                                    @elseif($fuel->asset)
                                    <tr>
                                        <th class="text-center"> <strong>Asset</strong></th>
                                        <td class="text-center">
                                            {{$fuel->asset->product->brand ? $fuel->asset->product->brand->name : ""}} {{$fuel->asset->product ? $fuel->asset->product->name : ""}}
                                        </td>
                                    </tr>
                                    @elseif($fuel->vehicle) 
                                    <tr>
                                        <th class="text-center"> <strong>Vehicle</strong></th>
                                        <td class="text-center">
                                            {{  $fuel->vehicle ? $fuel->vehicle->registration_number : "" }} {{$fuel->vehicle->vehicle_make ? $fuel->vehicle->vehicle_make->name : ""}} {{$fuel->vehicle->vehicle_model ? $fuel->vehicle->vehicle_model->name : ""}} {{$fuel->vehicle ? "| ".$fuel->vehicle->fleet_number : ""}} 
                                        </td>
                                    </tr>
                                    @endif
                                    @if ($fuel->driver)
                                    <tr>
                                        <th class="text-center"><strong>Driver</strong></th>
                                        <td class="text-center">{{$fuel->driver ? $fuel->driver->employee->name : ""}} {{$fuel->driver ? $fuel->driver->employee->surname : ""}}</td>
                                    </tr>
                                    @endif
                                  
                                    <tr>
                                        <th class="text-center"><strong>Fuel Type</strong></th>
                                        <td class="text-center"> {{$fuel->container ? $fuel->container->fuel_type : ""}}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-center"><strong>Quantity</strong></th>
                                        <td class="text-center"> {{$fuel->quantity ? $fuel->quantity."L" : ""}}</td>
                                    </tr>
                                    @if ($fuel->comments)
                                    <tr>
                                        <th class="text-center"><strong>Comments</strong></th>
                                        <td class="text-center"> {{$fuel->comments}}</td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <th class="text-center"><strong>Checked By</strong></th>
                                        <td class="text-center"> {{$fuel->user->employee ? $fuel->user->employee->name : "" }} {{$fuel->user->employee ? $fuel->user->employee->surname : "" }}</td>
                                    </tr>
                                    @php
                                    $authoriser = App\Models\User::find($fuel->authorized_by_id);
                                    @endphp
                                    @if (isset($authoriser))
                                    <tr>
                                        <th class="text-center"><strong>Authorised By</strong></th>
                                        <td class="text-center"> {{$authoriser->name }} {{$authoriser->surname }}</td>
                                    </tr>
                                    @endif

                                </tbody>

                            </table>

                            @if ($fuel->driver)
                            <div class="row">
                                <div class="col-md-6">
                                    <strong style="margin-left: 20px">{{$fuel->driver->employee ? $fuel->driver->employee->name : ""}} {{$fuel->driver->employee ? $fuel->driver->employee->surname : ""}} Signature </strong>
                                    <br>
                                    <br>
                                    <strong style="margin-left: 20px">......................................................................</strong>
                                </div>
                            </div>
                            @endif
                            
                        </main>
                        <center> <footer style="   position: fixed; 
                        bottom: 0px; 
                        left: 0px; 
                        right: 0px;
                        height: 50px;">{{ucfirst($company->name)}} FUEL ORDER!!</footer></center>
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
