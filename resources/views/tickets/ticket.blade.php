<!doctype html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
 
  <title>Job Card</title>
 

@include('includes.css')

</head>
<body>

<div class="container">
    <div class="card">
        <div class="card-body">
            <div id="invoice">
                <div class="invoice overflow-auto">
                    <div style="margin-left: -30px; margin-right:-30px">
                        <header style="margin-top:-25px; padding-top:-25px; padding-bottom:10px" >
                            <div class="row">
                                <div class="col" >
                                    <a href="javascript:;">
                                        <img src="{{asset('images/uploads/'.$company->logo)}}" width="150" alt="">
                                    </a>
                                </div>
                                <div class="col company-details" style="margin-top:-110px;">
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
                                    <h5 class="to"> 
                                         @if (isset($ticket->booking->horse))
                                        Horse | {{ucfirst($ticket->booking->horse->horse_make ? $ticket->booking->horse->horse_make->name : "")}} {{ucfirst($ticket->booking->horse->horse_model ? $ticket->booking->horse->horse_model->name : "" )}} {{ucfirst($ticket->booking->horse->registration_number)}}
                                        @elseif(isset($ticket->booking->vehicle))
                                        Vehicle | {{ucfirst($ticket->booking->vehicle->vehicle_make->name)}} {{ucfirst($ticket->booking->vehicle->vehicle_model->name)}} {{ucfirst($ticket->booking->vehicle->registration_number)}}
                                        @elseif(isset($ticket->booking->trailer))
                                        Trailer | {{ucfirst($ticket->booking->trailer->make)}} {{ucfirst($ticket->booking->trailer->model)}} {{ucfirst($ticket->booking->trailer->registration_number)}}
                                    @endif</h5>
                                </div>
                                <div class="col invoice-details" style="margin-top:-130px;">
                                    <div class="date"> <strong>Booking Number:</strong> {{$ticket->booking ? $ticket->booking->booking_number : ""}}</div>
                                    <div class="date"><strong>Job Card Number:</strong> {{$ticket->ticket_number}}</div>
                                    <div class="date"><strong>Date:</strong> {{$ticket->in_date}}</div>
                                </div>
                            </div>
                            <table class="table table-striped">
        
                                <tbody>
                                    <tr>
                                        <th class="text-center">
                                            <center>
                                                <strong>Service Type</strong>
                                            </center>
                                        </th>
                                        <td class="text-center">
                                            <center>{{$ticket->service_type ? $ticket->service_type->name : ""}}</center>
                                            
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-center">
                                        <center>
                                            <strong>BookedBy</strong>
                                        </center>
                                        </th>
                                        <td class="text-center">
                                            <center>
                                                @if ($ticket->booking)
                                                {{$ticket->booking->user ? $ticket->booking->user->name : ""}} {{$ticket->booking->user ? $ticket->booking->user->surname : ""}}
                                                @endif
                                            </center>
                                           
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-center">
                                            <center>
                                                <strong>Booked On</strong>
                                            </center>
                                        </th>
                                        <td class="text-center"> 
                                            <center>
                                                {{$ticket->booking ? $ticket->booking->created_at : ""}}
                                            </center>
                                           
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-center"> 
                                            <center>
                                                <strong>Booked For</strong>
                                            </center>
                                        </th>
                                        <td class="text-center">
                                            <center>
                                                @if (isset($ticket->booking->horse))
                                                    Horse | {{ucfirst($ticket->booking->horse->horse_make ? $ticket->booking->horse->horse_make->name : "")}} {{ucfirst($ticket->booking->horse->horse_model ? $ticket->booking->horse->horse_model->name : "" )}} {{ucfirst($ticket->booking->horse->registration_number)}}
                                                @elseif(isset($ticket->booking->vehicle))
                                                    Vehicle | {{ucfirst($ticket->booking->vehicle->vehicle_make->name)}} {{ucfirst($ticket->booking->vehicle->vehicle_model->name)}} {{ucfirst($ticket->booking->vehicle->registration_number)}}
                                                @elseif(isset($ticket->booking->trailer))
                                                    Trailer | {{ucfirst($ticket->booking->trailer->make)}} {{ucfirst($ticket->booking->trailer->model)}} {{ucfirst($ticket->booking->trailer->registration_number)}}
                                                @endif
                                            </center>
                                            
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-center">
                                            <center>
                                                <strong>Assigned To</strong>
                                            </center>
                                        </th>
                                        <td class="text-center">
                                            <center>
                                                @if (isset($ticket->booking->employees) && $ticket->booking->employees->count()>0)
                                                @foreach ($ticket->booking->employees as $mechanic)
                                                    {{ $mechanic->name }} {{ $mechanic->surname }}
                                                    <br>
                                                @endforeach
                                                @elseif(isset($ticket->booking->vendor))
                                                    {{ucfirst($ticket->booking->vendor->name)}}  
                                                @endif
                                            </center>
                                          
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-center">
                                            <center>
                                                <strong>Job Card Opened</strong>
                                            </center>
                                        </th>
                                        <td class="text-center">
                                            <center>
                                                {{$ticket->in_date}} @ {{$ticket->in_time}}
                                            </center>
                                            
                                        </td>
                                    </tr>
                                   
                                    <tr>
                                        <th class="text-center">
                                            <center> 
                                                <strong>Job Card Closed</strong>
                                            </center>
                                        </th>
                                        <td class="text-center">
                                            <center>
                                                {{$ticket->out_date}} @ {{$ticket->out_time}}
                                            </center>
                                        </td>
                                    </tr>
                                    @if ($ticket->odometer)
                                    <tr>
                                        <th class="text-center">
                                            <center>
                                                <strong>Mileage</strong>
                                            </center>
                                        </th>
                                        <td class="text-center"> 
                                            <center>
                                                {{ $ticket->odometer }} Kms
                                            </center>
                                        </td>
                                    </tr>
                                    @endif
                                    @if ($ticket->next_service)
                                    <tr>
                                        <th class="text-center">
                                            <center>
                                                <strong>Next Service</strong>
                                            </center>
                                        </th>
                                        <td class="text-center"> 
                                            <center>
                                                {{ $ticket->next_service }} Kms
                                            </center>
                                           
                                        </td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <th class="text-center">
                                            <center>
                                                <strong>Station</strong>
                                            </center>
                                        </th>
                                        <td class="text-center">
                                            <center>
                                                {{$ticket->station}}
                                            </center> 
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-center">
                                            <center>
                                                <strong>Problem Description</strong>
                                            </center>
                                        </th>
                                        <td class="text-center">
                                            <center>
                                                {{$ticket->booking ? $ticket->booking->description : ""}}
                                            </center>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-center">
                                            <center> <strong>Mechanic Report</strong></center>
                                        </th>
                                        <td class="text-center">
                                            <center> {{$ticket->report}}</center>
                                        </td>
                                    </tr>
                          
                                </tbody>
        
                            </table>
                        </main>
                      <center><footer style="   position: fixed; 
                        bottom: -60px; 
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

