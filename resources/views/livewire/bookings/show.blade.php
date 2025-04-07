<div>
    <div class="row mt-30">
    <div class="col-md-10 col-md-offset-1">
        <ul class="nav nav-tabs nav-justified" role="tablist">
            <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Booking Details</a></li>
        </ul>
        <div class="tab-content bg-white p-15">
            <div role="tabpanel" class="tab-pane active" id="basic">
                <table class="table table-striped">

                    <tbody class="text-center line-height-35 ">

                        <tr>
                            <th class="w-10 text-center line-height-35">Booking#</th>
                            <td class="w-20 line-height-35">{{ucfirst($booking->booking_number)}}</td>
                        </tr>
                        @if ($booking->ticket)
                        <tr>
                            <th class="w-10 text-center line-height-35">Ticket#</th>
                            <td class="w-20 line-height-35">
                                <a href="{{ route('tickets.show',$booking->ticket) }}" style="color: blue"> {{ucfirst($booking->ticket->ticket_number)}}</a>
                            </td>
                        </tr>
                        @endif
                      
                        <tr>
                            <th class="w-10 text-center line-height-35">CreatedBy</th>
                            <td class="w-20 line-height-35">{{ucfirst($booking->user ? $booking->user->name : "")}} {{ucfirst($booking->user ? $booking->user->surname : "")}}</td>
                        </tr>
                        <tr>
                            <th class="w-10 text-center line-height-35">Requested By</th>
                            <td class="w-20 line-height-35">{{ucfirst($booking->employee ? $booking->employee->name : "")}} {{ucfirst($booking->employee ? $booking->employee->surname : "")}}</td>
                        </tr>
                            <tr>
                            <th class="w-10 text-center line-height-35">AssignedTo</th>
                            <td class="w-20 line-height-35">
                                @if (isset($booking->employees) && $booking->employees->count()>0)
                                @foreach ($booking->employees as $mechanic)
                                    {{ $mechanic->name }} {{ $mechanic->surname }}
                                    <br>
                                @endforeach
                            @elseif(isset($booking->vendor))
                                {{ucfirst($booking->vendor->name)}}  
                            @endif
                            </td>
                                
                            </tr>
                         
                            <tr>
                                <th class="w-10 text-center line-height-35">Booking For</th>
                                <td class="w-20 line-height-35">
                                    @if (isset($booking->horse))
                                        Horse | {{ucfirst($booking->horse->horse_make ? $booking->horse->horse_make->name : "")}} {{ucfirst($booking->horse->horse_model ? $booking->horse->horse_model->name : "" )}} {{ucfirst($booking->horse->registration_number)}}
                                        @elseif(isset($booking->vehicle))
                                        Vehicle | {{ucfirst($booking->vehicle->vehicle_make->name)}} {{ucfirst($booking->vehicle->vehicle_model->name)}} {{ucfirst($booking->vehicle->registration_number)}}
                                        @elseif(isset($booking->trailer))
                                        Trailer | {{ucfirst($booking->trailer->name)}} {{ucfirst($booking->trailer->registration_number)}}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">In Date & Time</th>
                                <td class="w-20 line-height-35">{{$booking->in_date}} @ {{$booking->in_time}}</td>
                            </tr>
                            @if (isset($booking->estimated_out_date))
                            <tr>
                                <th class="w-10 text-center line-height-35">Estimated Out Date & Time</th>
                                <td class="w-20 line-height-35">{{$booking->estimated_out_date}} @ {{$booking->estimated_out_time}}</td>
                            </tr>
                            @endif
                            @if (isset($booking->_out_date))
                            <tr>
                                <th class="w-10 text-center line-height-35">Out Date & Time</th>
                                <td class="w-20 line-height-35">{{$booking->out_date}} @ {{$booking->out_time}}</td>
                            </tr>
                            @endif

                            <tr>
                                <th class="w-10 text-center line-height-35">Station</th>
                                <td class="w-20 line-height-35">{{$booking->station}}</td>
                            </tr>
                            @if ($booking->odometer)
                            <tr>
                                <th class="w-10 text-center line-height-35">Odometer</th>
                               
                                    <td class="w-20 line-height-35">
                                       
                                        {{$booking->odometer}}Kms
                                       
                                    </td>
                              
                               
                            </tr>
                            @endif
                            <tr>
                                <th class="w-10 text-center line-height-35">Problem Description</th>
                                <td class="w-20 line-height-35">{{$booking->description}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Authorization</th>
                                <td class="w-20 line-height-35"><span class="badge bg-{{($booking->authorization == 'approved') ? 'success' : (($booking->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($booking->authorization == 'approved') ? 'approved' : (($booking->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Status</th>
                                <td class="w-20 line-height-35"><span class="badge bg-{{$booking->status == 1 ? "warning" : "success"}}">{{$booking->status == 1 ? "Open" : "Closed"}}</span></td>
                            </tr>
                    </tbody>
                </table>
                <div class="row">
                    <div class="col-md-12">
                        <div class="btn-group pull-right mt-10" >
                           <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                        </div>
                    </div>
                    </div>
            </div>

            <!-- /.section-title -->
        </div>
    </div>
    </div>
</div>
