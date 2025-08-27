@extends('layouts.main')
@section('extra-css')
@if (Auth::user()->employee->company)
<link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->employee->company->logo)!!}">
@elseif (Auth::user()->company)
<link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->company->logo)!!}">
@endif
@endsection
@section('title')
    Job Card | @if (isset(Auth::user()->employee->company))
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
                                        <div class="text-gray-light">JOB CARD FOR</div>
                                        <div class="date"><strong>Category: </strong> 
                                            @if (isset($ticket->horse))
                                            Horse
                                            @elseif(isset($ticket->vehicle))
                                                Vehicle
                                            @elseif(isset($ticket->asset))
                                                Asset
                                            @elseif(isset($ticket->trailer))
                                                Trailer
                                            @endif
                                        </div>
                                        <div class="date"><strong>Equipment: </strong> 
                                            @if (isset($ticket->horse))
                                                {{ucfirst($ticket->horse->horse_make ? $ticket->horse->horse_make->name : "")}} {{ucfirst($ticket->horse->horse_model ? $ticket->horse->horse_model->name : "" )}} {{$ticket->horse->registration_number}} {{$ticket->horse->fleet_number ? "(".$ticket->horse->fleet_number.")"  : ""}}
                                            @elseif(isset($ticket->vehicle))
                                                {{ucfirst($ticket->vehicle->vehicle_make->name)}} {{ucfirst($ticket->vehicle->vehicle_model->name)}} {{$ticket->vehicle->registration_number}} {{$ticket->vehicle->registration_number}} {{$ticket->vehicle->fleet_number ? "(".$ticket->vehicle->fleet_number.")"  : ""}}
                                            @elseif(isset($ticket->asset))
                                                {{ucfirst($ticket->asset->product->brand ? $ticket->asset->product->brand->name : "")}} {{ucfirst($ticket->asset->product ? $ticket->asset->product->name : "")}}  {{$ticket->asset->serial_number}}
                                            @elseif(isset($ticket->trailer))
                                                {{ucfirst($ticket->trailer->make)}} {{ucfirst($ticket->trailer->model)}} {{$ticket->trailer->registration_number}} {{$ticket->vehicle->fleet_number ? "(".$ticket->vehicle->fleet_number.")"  : ""}}
                                            @endif
                                        </div>
                                </div>
                                <div class="col invoice-details">
                                    <div class="date"> <strong>Booking Number:</strong> {{$ticket->booking ? $ticket->booking->booking_number : ""}}</div>
                                    <div class="date"><strong>Job Card Number:</strong> {{$ticket->ticket_number}}</div>
                                    <div class="date"><strong>Date:</strong> {{$ticket->in_date}}</div>
                                    <div class="date"><strong>Status:</strong> {{$ticket->status == 1 ? "Open" : "Closed"}}</div>
                                </div>
                            </div>
                            <table class="table table-striped table-bordered">
        
                                 <tbody>
                            <tr>
                                <th class="text-center"><strong>Service Type</strong></th>
                                <td class="text-center">
                                    {{$ticket->service_type ? $ticket->service_type->name : ""}}
                                </td>
                            </tr>
                            <tr>
                                <th class="text-center"><strong>Booked By</strong></th>
                                <td class="text-center">
                                    @if ($ticket->booking)
                                    {{$ticket->booking->user ? $ticket->booking->user->name : ""}} {{$ticket->booking->user ? $ticket->booking->user->surname : ""}}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="text-center"><strong>Booked On</strong></th>
                                <td class="text-center"> 
                                    {{$ticket->booking ? $ticket->booking->created_at : ""}}
                                </td>
                            </tr>
                           
                            <tr>
                                <th class="text-center"><strong>Assigned To</strong></th>
                                <td class="text-center">
                                    @if (isset($ticket->booking->employees) && $ticket->booking->employees->count()>0)
                                    @foreach ($ticket->booking->employees as $mechanic)
                                        {{ $mechanic->name }} {{ $mechanic->surname }}
                                        <br>
                                    @endforeach
                                    @elseif(isset($ticket->booking->vendor))
                                        {{ucfirst($ticket->booking->vendor->name)}}  
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="text-center"><strong>Job Card Opened</strong></th>
                                <td class="text-center">{{$ticket->in_date}} @ {{$ticket->in_time}}</td>
                            </tr>
                           
                            <tr>
                                <th class="text-center"><strong>Job Card Closed</strong></th>
                                <td class="text-center">
                                    {{$ticket->out_date}} @ {{$ticket->out_time}}
                                </td>
                            </tr>
                            @if ($ticket->odometer)
                            <tr>
                                <th class="text-center"><strong>Mileage</strong></th>
                                <td class="text-center"> 
                                   {{ $ticket->odometer ? $ticket->odometer." Kms" : "" }}
                                </td>
                            </tr>
                            @endif
                            @if ($ticket->next_service)
                            <tr>
                                <th class="text-center"><strong>Next Service Mileage</strong></th>
                                <td class="text-center"> 
                                   {{ $ticket->next_service ? $ticket->next_service." Kms" : "" }}
                                </td>
                            </tr>
                            @endif
                                 @if ($ticket->hours)
                            <tr>
                                <th class="text-center"><strong>Hours</strong></th>
                                <td class="text-center"> 
                                   {{ $ticket->hours ? $ticket->hours." Hrs" : "" }}
                                </td>
                            </tr>
                            @endif
                            @if ($ticket->next_service_hours)
                            <tr>
                                <th class="text-center"><strong>Next Service Hours</strong></th>
                                <td class="text-center"> 
                                   {{ $ticket->next_service_hours ? $ticket->next_service_hours." Hrs" : "" }}
                                </td>
                            </tr>
                            @endif
                            <tr>
                                <th class="text-center"><strong>Station</strong></th>
                                <td class="text-center"> {{$ticket->station}}</td>
                            </tr>
                            <tr>
                                <th class="text-center"><strong>Problem Description</strong></th>
                                <td class="text-center"> {{$ticket->booking ? $ticket->booking->description : ""}}</td>
                            </tr>
                            <tr>
                                <th class="text-center"><strong>Mechanic Report</strong></th>
                                <td class="text-center"> {{$ticket->report}}</td>
                            </tr>
                            <tr>
                                <th class="text-center"><strong>Authorized By</strong></th>
                                <td class="text-center"> {{$authorizer->name}} {{$authorizer->surname}}</td>
                            </tr>
                  
                        </tbody>
        
                            </table>
                             <table class="table table-striped table-bordered">
                        <tbody>
                            <tr>
                                <th>
                                    <strong>Foreman: </strong>
                                </th>
                                <td><strong>Signature: </strong></td>
                                <td><strong>Date: </strong></td>
                            </tr>
                            <tr>
                                <th>
                                    <strong>Production: </strong>
                                </th>
                                <td><strong>Signature: </strong></td>
                                <td><strong>Date: </strong></td>
                            </tr>
                        </tbody>
                    </table>
                        </main>
                      <center><footer style="   position: fixed; 
                        bottom: 0px; 
                        left: 0px; 
                        right: 0px;
                        height: 50px;">{{ucfirst($company->name)}} Job Card</footer></center>  
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
