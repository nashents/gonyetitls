<div>
    <div class="row mt-30">
    
        <!-- /.col-md-3 -->

        <div class="col-md-10 col-md-offset-1" >

            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Requisition Details</a></li>
                <li role="presentation"><a href="#items" aria-controls="items" role="tab" data-toggle="tab">Requisition Items</a></li>

            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <table class="table table-striped">

                        <tbody class="text-center line-height-35 ">
                            <tr>
                                <th class="w-10 text-center line-height-35">Requisition#</th>
                                <td class="w-20 line-height-35">{{$requisition->requisition_number}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">CreatedBy</th>
                                <td class="w-20 line-height-35">{{$requisition->user ? $requisition->user->name : ""}} {{$requisition->user ? $requisition->user->surname : ""}} </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">RequestedBy</th>
                                <td class="w-20 line-height-35">
                                    {{$requisition->employee ? $requisition->employee->name : ""}} {{$requisition->employee ? $requisition->employee->surname : ""}}
                                    <br>
                                    <small><strong><i>{{$requisition->department ? $requisition->department->name : ""}}</i></strong></small>
                                 </td>
                            </tr>
                           
                            @if ($requisition->trip)
                            <tr>
                                <th class="w-10 text-center line-height-35">Trip</th>
                                <td class="w-20 line-height-35"> <a href="{{route('trips.show',$requisition->trip->id)}}">{{$requisition->trip ? $requisition->trip->trip_number : ""}} {{$requisition->trip->trip_ref ? " / ".$requisition->trip->trip_ref : ""}}</td></a> 
                            </tr>
                            @endif
                            
                          
                                <tr>
                                    <th class="w-10 text-center line-height-35">Requisition For?</th>
                                    <td class="w-20 line-height-35">
                                             {{ $requisition->subject }}

                                            @if ($trip = $requisition->trip)
                                                <br>
                                                  Trip: 
                                                <a href="{{ route('trips.show', $trip->id) }}" style="color: blue" target="_blank">
                                                  
                                                    {{ $trip->trip_number }} | 
                                                    {{ $trip->horse?->registration_number }} 
                                                    {{ $trip->driver?->employee?->name }} {{ $trip->driver?->employee?->surname }} |
                                                    {{ $trip->customer?->name }} | 
                                                    {{ $trip->loading_point?->name }} - {{ $trip->offloading_point?->name }}
                                                </a>

                                            @elseif ($booking = $requisition->booking)
                                                <br>
                                                  Booking:
                                                <a href="{{ route('bookings.show', $booking->id) }}" style="color: blue" target="_blank">
                                                  
                                                    {{ $booking->booking_number }} | 
                                                    {{ $booking->service_type?->name }} |

                                                    @if ($horse = $booking->horse)
                                                        {{ $horse->registration_number }} {{ $horse->fleet_number ? "($horse->fleet_number)" : '' }}
                                                    @elseif ($vehicle = $booking->vehicle)
                                                        {{ $vehicle->registration_number }} {{ $vehicle->fleet_number ? "($vehicle->fleet_number)" : '' }}
                                                    @elseif ($trailer = $booking->trailer)
                                                        {{ $trailer->registration_number }} {{ $trailer->fleet_number ? "($trailer->fleet_number)" : '' }}
                                                    @endif
                                                </a>

                                            @elseif ($requisition->purchase_id)
                                                @php
                                                    $purchase = \App\Models\Purchase::find($requisition->purchase_id);
                                                @endphp
                                                @if($purchase)
                                                    <br>
                                                    Purchase Order:
                                                    <a href="{{ route('purchases.show', $purchase->id) }}" style="color: blue" target="_blank"> 
                                                        {{ $purchase->purchase_number }} | 
                                                        {{ $purchase->date }} |
                                                        {{ $purchase->vendor?->name }} |
                                                        {{ $purchase->currency?->name }} 
                                                        {{ $purchase->currency?->symbol }}{{ number_format($purchase->total ?? 0, 2) }}
                                                    </a>
                                                @endif
                                            @endif

                                            @if ($requisition->description)
                                                <br>
                                                {{ $requisition->description }}
                                            @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Date</th>
                                    <td class="w-20 line-height-35">{{$requisition->date}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Ccy</th>
                                    <td class="w-20 line-height-35">{{$requisition->currency ? $requisition->currency->name : ""}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Total</th>
                                    <td class="w-20 line-height-35">{{$requisition->currency ? $requisition->currency->symbol : ""}}{{number_format($requisition->total,2)}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Authorization</th>
                                    <td class="w-20 line-height-35">
                                        <span class="badge bg-{{($requisition->authorization == 'approved') ? 'success' : (($requisition->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($requisition->authorization == 'approved') ? 'approved' : (($requisition->authorization == 'rejected') ? 'rejected' : 'pending') }}</span>
                                        @if ($requisition->reason)
                                        <br>
                                        <small><strong style="background-color: orange">{{$requisition->reason}}</strong></small>  
                                        @endif  
                                    </td>
                                </tr>
                                @if ($requisition->authorized_by_id)
                                <tr>
                                    <th class="w-10 text-center line-height-35">AuthorizedBy</th>
                                    <td class="w-20 line-height-35">{{App\Models\User::find($requisition->authorized_by_id)->name}} {{App\Models\User::find($requisition->authorized_by_id)->surname}}</td>
                                </tr>
                                @endif
                                
                             
                        </tbody>
                    </table>
                </div>
                <div role="tabpanel" class="tab-pane" id="items">
                  @livewire('requisitions.items', ['id' => $requisition->id])
                </div> 
                <div class="row">
                    <div class="col-md-12">
                        <div class="btn-group pull-right mt-10" >
                           <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                        </div>
                    </div>
                    </div>

            </div>
        </div>
        <!-- /.col-md-9 -->
    </div>
   
</div>
