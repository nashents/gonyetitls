<div>
    <div id="invoice">
        <div class="toolbar hidden-print">
            <div class="text-end">
                <button type="button" onclick="goBack()" class="btn btn-default border-primary btn-wide btn-rounded" ><i class="fa fa-arrow-left"></i> Back</button>
                <a href="{{route('tickets.print',$ticket->id)}}" class="btn btn-default border-primary btn-wide btn-rounded"><i class="fa fa-print" style="color:black"></i> Print</a>
                <a href="{{route('tickets.pdf', $ticket->id)}}" class="btn btn-default border-primary btn-wide btn-rounded"><i class="fa fa-file-pdf-o" style="color: red"></i> Export as PDF</a>
            </div>
            <hr>
        </div>
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
                            <h5 class="to"> 
                                 @if (isset($ticket->booking->horse))
                                Horse | {{ucfirst($ticket->booking->horse->horse_make ? $ticket->booking->horse->horse_make->name : "")}} {{ucfirst($ticket->booking->horse->horse_model ? $ticket->booking->horse->horse_model->name : "" )}} {{ucfirst($ticket->booking->horse->registration_number)}}
                                @elseif(isset($ticket->booking->vehicle))
                                Vehicle | {{ucfirst($ticket->booking->vehicle->vehicle_make->name)}} {{ucfirst($ticket->booking->vehicle->vehicle_model->name)}} {{ucfirst($ticket->booking->vehicle->registration_number)}}
                                @elseif(isset($ticket->booking->trailer))
                                Trailer | {{ucfirst($ticket->booking->trailer->make)}} {{ucfirst($ticket->booking->trailer->model)}} {{ucfirst($ticket->booking->trailer->registration_number)}}
                            @endif</h5>
                        </div>
                        <div class="col invoice-details">
                            <div class="date"> <strong>Booking Number:</strong> {{$ticket->booking ? $ticket->booking->booking_number : ""}}</div>
                            <div class="date"><strong>Job Card Number:</strong> {{$ticket->ticket_number}}</div>
                            <div class="date"><strong>Date:</strong> {{$ticket->in_date}}</div>
                        </div>
                    </div>
                    <table class="table table-striped">

                        <tbody>
                            <tr>
                                <th class="text-center"><strong>Service Type</strong></th>
                                <td class="text-center">
                                    {{$ticket->service_type ? $ticket->service_type->name : ""}}
                                </td>
                            </tr>
                            <tr>
                                <th class="text-center"><strong>BookedBy</strong></th>
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
                                <th class="text-center"> <strong>Booked For</strong></th>
                                <td class="text-center">
                                    @if (isset($ticket->booking->horse))
                                    Horse | {{ucfirst($ticket->booking->horse->horse_make ? $ticket->booking->horse->horse_make->name : "")}} {{ucfirst($ticket->booking->horse->horse_model ? $ticket->booking->horse->horse_model->name : "" )}} {{ucfirst($ticket->booking->horse->registration_number)}}
                                    @elseif(isset($ticket->booking->vehicle))
                                    Vehicle | {{ucfirst($ticket->booking->vehicle->vehicle_make->name)}} {{ucfirst($ticket->booking->vehicle->vehicle_model->name)}} {{ucfirst($ticket->booking->vehicle->registration_number)}}
                                    @elseif(isset($ticket->booking->trailer))
                                    Trailer | {{ucfirst($ticket->booking->trailer->make)}} {{ucfirst($ticket->booking->trailer->model)}} {{ucfirst($ticket->booking->trailer->registration_number)}}
                                @endif
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
                                   {{ $ticket->odometer }} Kms
                                </td>
                            </tr>
                            @endif
                            @if ($ticket->next_service)
                            <tr>
                                <th class="text-center"><strong>Next Service</strong></th>
                                <td class="text-center"> 
                                   {{ $ticket->next_service }} Kms
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
                  
                        </tbody>

                    </table>
                </main>
              <center><footer>{{ucfirst($company->name)}} Job Card</footer></center>  
            </div>
            <!--DO NOT DELETE THIS div. IT is responsible for showing footer always at the bottom-->
            <div></div>
        </div>
    </div>
</div>
