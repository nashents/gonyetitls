<div>
    <div id="invoice">
        <x-loading/>
        <div class="toolbar hidden-print">
            <div class="text-end">
                <button type="button" onclick="goBack()" class="btn btn-default border-primary btn-wide btn-rounded"><i class="fa fa-arrow-left" style="color:black"></i> Back</button>
                <a href="{{route('requisitions.print',$requisition->id)}}" class="btn btn-default border-primary btn-wide btn-rounded"><i class="fa fa-print" style="color:black"></i> Print</a>
                <a href="{{route('requisitions.pdf', $requisition->id)}}" class="btn btn-default border-primary btn-wide btn-rounded"><i class="fa fa-file-pdf-o" style="color:red"></i> Export as PDF</a>
            </div>
            <hr>
        </div>
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
                          
                            <h4 class="name" >
                                <a target="_blank" href="javascript:;" style="color:  {{$company->color ? $company->color : "#000000" }}">
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
                            <div>
                             
                                    VAT No.: {{$company->vat_number}}
                               
                            </div>
                            <div>
                              
                                    TIN.: {{$company->tin_number}}
                               
                            </div>
                        </div>
                    </div>
                    
                </header>
                <main>
                    <div class="row contacts">
                        <div class="col invoice-to" >
                            <div class="text-gray-light">Request From</div>
                            <h6 class="to"><strong>Employee: </strong> {{$requisition->employee ? $requisition->employee->name : ""}} {{$requisition->employee ? $requisition->employee->surname : ""}}</h4>
                            <div class="email"><strong>Dpt:</strong> {{$requisition->department ? $requisition->department->name : ""}}</div>
                          
                        </div>
                        <div class="col invoice-details">
                            <div class="date" style="padding-bottom: 3px"> <strong>Requisition No.:</strong> {{$requisition->requisition_number}}</div>
                            <div class="date" style="padding-bottom: 3px"><strong>Date:</strong> {{$requisition->date}}</div>
                        </div>
                    </div>
                
                    <table class="table table-striped">

                        <tbody>
                            <tr>
                                <th class="text-center"><strong>Requisition#</strong></th>
                                <td class="text-center"> {{$requisition->requisition_number}}</td>
                            </tr>
                            <tr>
                                <th class="text-center"><strong>CreatedBy</strong></th>
                                <td class="text-center"> {{$requisition->user ? $requisition->user->name : ""}} {{$requisition->user ? $requisition->user->surname : ""}}</td>
                            </tr>
                            <tr>
                                <th class="text-center"><strong>RequestedBy</strong></th>
                                <td class="text-center"> 
                                    {{$requisition->employee ? $requisition->employee->name : ""}} {{$requisition->employee ? $requisition->employee->surname : ""}}
                                    <br>
                                    {{$requisition->department ? $requisition->department->name : ""}}
                                </td>
                            </tr>
                           
                            <tr>
                                <th class="text-center"><strong>Requisition For</strong></th>
                                <td class="text-center"> 
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
                            @if ($requisition->requisition_items)
                                <tr>
                                    <th class="text-center"><strong>Requisted Items</strong></th>
                                    <td class="text-center">
                                        @if ($requisition->requisition_items)
                                                @foreach ($requisition->requisition_items as $requisition_item)
                                                    @if ($requisition_item->expense)
                                                        {{$requisition_item->expense ? $requisition_item->expense->name : ""}} 
                                                    @elseif($requisition_item->product)
                                                        {{ $requisition_item->product->brand ? $requisition_item->product->brand->name : ""}} {{ $requisition_item->product ? $requisition_item->product->name : ""}}
                                                    @elseif($requisition_item->inventory)
                                                        {{ $requisition_item->inventory->product->brand ? $requisition_item->inventory->product->brand->name : ""}} {{ $requisition_item->inventory->product ? $requisition_item->inventory->product->name : ""}}
                                                    @endif
                                                        @ @if ($requisition_item->amount)
                                                        {{ $requisition_item->currency ? $requisition_item->currency->name : ""}} {{ $requisition_item->currency ? $requisition_item->currency->symbol : ""}}{{ number_format($requisition_item->amount,2)}}
                                                    @endif
                                                    @if (!$loop->last), <br> @endif
                                                @endforeach
                                            @endif
                                    </td>
                                </tr>
                            @endif
                    
                            <tr>
                                <th class="text-center"><strong>Date</strong></th>
                                <td class="text-center"> {{$requisition->date}}</td>
                            </tr>
                            <tr>
                                <th class="text-center"><strong>Total</strong></th>
                                <td class="text-center"> {{Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->name : ""}} {{Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->symbol : ""}}{{number_format($requisition->total,2)}}</td>
                            </tr>
                            <tr>
                                <th class="text-center"><strong>Paid</strong></th>
                                <td class="text-center"> {{$requisition->currency ? $requisition->currency->symbol : ""}}{{number_format($requisition->paid,2)}}</td>
                            </tr>
                            <tr>
                                <th class="text-center"><strong>Status</strong></th>
                                <td class="text-center"> <span class="label label-{{($requisition->status == 'Paid') ? 'success' : (($requisition->status == 'Partial') ? 'warning' : 'danger') }}">{{ $requisition->status }}</span></td>
                            </tr>
                            <tr>
                                <th class="text-center"><strong>Authorization</strong></th>
                                <td class="text-center"> <span class="badge bg-{{($requisition->authorization == 'approved') ? 'success' : (($requisition->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($requisition->authorization == 'approved') ? 'approved' : (($requisition->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                            </tr>
                            <tr>
                                <th class="text-center"><strong>Authorization Date</strong></th>
                                <td class="text-center"> {{$requisition->authorization_date}}</td>
                            </tr>
                            <tr>
                                <th class="text-center"><strong>Authorized By</strong></th>
                                @php
                                    $authorized_by = App\Models\User::find($requisition->authorized_by_id);
                                @endphp
                                <td class="text-center"> 
                                    @if ($authorized_by)
                                        {{$authorized_by->name}} {{$authorized_by->surname}}
                                    @endif
                                </td>
                            </tr>
                        </tbody>

                    </table>
                
                </main>
             
                <center> 
                    <footer style=" bottom: 0px; left: 0px; right: 0px; ">
                        
                        <br>
                        <strong style="font-size: 18px;">Powered By</strong> <img src="{{asset('images/basilmark-logo.png')}}" alt="" style="width: 20%; height:20%">    
                    </footer>
                </center>  
            </div>
            <!--DO NOT DELETE THIS div. IT is responsible for showing footer always at the bottom-->
            <div></div>
        </div>
    </div>
</div>
