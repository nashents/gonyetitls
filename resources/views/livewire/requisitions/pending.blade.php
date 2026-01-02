<div>
    <style>
        .modal-lg {
        max-width: 80%;
    }
    </style>
        <section class="section">
            <x-loading/>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel">
                            <div class="panel-heading">
                                <div>
                                    @include('includes.messages')
                                </div>
                               
                            </div>
                            <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">
                                <div class="panel-title">
                                    <div class="row">
                                    
                                    <div class="col-lg-3">
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                      Filter By
                                      </span>
                                      <select wire:model.debounce.300ms="requisition_filter" class="form-control" aria-label="..." >
                                        <option value="created_at">Requisition Created At</option>
                                  </select>
                                        </div>
                                        <!-- /input-group -->
                                    </div>
                                    @if ($requisition_filter == "created_at")
                                    <div class="col-lg-2" style="margin-right: 7px">
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                      From
                                      </span>
                                      <input type="date" wire:model.debounce.300ms="from"  class="form-control" aria-label="...">
                                        </div>
                                        <!-- /input-group -->
                                    </div>
                                    <div class="col-lg-2" style="margin-left: 30px">
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                      To
                                      </span>
                                      <input type="date" wire:model.debounce.300ms="to"  class="form-control" aria-label="...">
                                        </div>
                                        <!-- /input-group -->
                                    </div>
                              
                                    @endif
                                   
                                    <!-- /input-group -->
                                </div>
                              
                               
                                </div>
                                <br>
                                <div class="col-md-3" style="float: right; padding-right:0px">
                                    <div class="form-group">
                                        <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search requisitions...">
                                    </div>
                                </div>
                                <table  class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                    <thead >
                                        <th class="th-sm">Requisition#
                                        </th>
                                        <th class="th-sm">CreatedBy
                                        </th>
                                        <th class="th-sm">RequestedBy
                                        </th>
                                        <th class="th-sm">Item(s)
                                        </th>
                                        <th class="th-sm">Summary
                                        </th>
                                        <th class="th-sm">Date
                                        </th>
                                        <th class="th-sm">Total
                                        </th>
                                        <th class="th-sm">Payment
                                        </th>
                                        <th class="th-sm">Status
                                        </th>
                                        <th class="th-sm">Auth
                                        </th>
                                        <th class="th-sm">Actions
                                        </th>
                                      </tr>
                                    </thead>
                                    @if (isset($requisitions))
                                    <tbody>
                                        @forelse ($requisitions as $requisition)
                                      <tr>
                                                          <td>{{ucfirst($requisition->requisition_number)}}</td>
                                        <td>{{ucfirst($requisition->user->name)}} {{ucfirst($requisition->user->surname)}}</td>
                                        <td>
                                            {{ucfirst($requisition->employee ? $requisition->employee->name : "")}} {{ucfirst($requisition->employee ? $requisition->employee->surname : "")}}
                                            <br>
                                            <small><strong><i>{{ucfirst($requisition->department ? $requisition->department->name : "")}}</i></strong></small>
                                        </td>
                                        <td>
                                             @if ($requisition->requisition_items)
                                            @foreach ($requisition->requisition_items as $requisition_item)
                                                @if ($requisition_item->expense)
                                                    {{$requisition_item->expense ? $requisition_item->expense->name : ""}} 
                                                         @elseif($requisition_item->allowance)
                                                    {{ $requisition_item->allowance ? $requisition_item->allowance->name : ""}}
                                                @elseif($requisition_item->product)
                                                    {{ $requisition_item->product->brand ? $requisition_item->product->brand->name : ""}} {{ $requisition_item->product ? $requisition_item->product->name : ""}}
                                                @elseif($requisition_item->inventory)
                                                    {{ $requisition_item->inventory->product->brand ? $requisition_item->inventory->product->brand->name : ""}} {{ $requisition_item->inventory->product ? $requisition_item->inventory->product->name : ""}}
                                                @endif
                                                    @ @if ($requisition_item->amount)
                                                    {{ $requisition_item->currency ? $requisition_item->currency->name : ""}} {{ $requisition_item->currency ? $requisition_item->currency->symbol : ""}}{{ number_format($requisition_item->amount,2)}}
                                                @endif
                                                @if (!$loop->last), @endif
                                            @endforeach
                                        @endif
                                           
                                        </td>
                                        <td>
                                            {{ $requisition->subject ? "Subject: ".$requisition->subject : "" }}

                                            @if ($trip = $requisition->trip)
                                               
                                                  Trip: 
                                                <a href="{{ route('trips.show', $trip->id) }}" style="color: blue" target="_blank">
                                                  
                                                    {{ $trip->trip_number }} | 
                                                    {{ $trip->horse?->registration_number }} 
                                                    {{ $trip->driver?->employee?->name }} {{ $trip->driver?->employee?->surname }} |
                                                    {{ $trip->customer?->name }} | 
                                                    {{ $trip->loading_point?->name }} - {{ $trip->offloading_point?->name }}
                                                </a>

                                            @elseif ($booking = $requisition->booking)
                                              
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

                                            @elseif ( $purchase = $requisition->purchase)
                                                    Purchase Order:
                                                    <a href="{{ route('purchases.show', $purchase->id) }}" style="color: blue" target="_blank"> 
                                                        {{ $purchase->purchase_number }} | 
                                                        {{ $purchase->date }} |
                                                        {{ $purchase->vendor?->name }} |
                                                        {{ $purchase->currency?->name }} 
                                                        {{ $purchase->currency?->symbol }}{{ number_format($purchase->total ?? 0, 2) }}
                                                    </a>
                                               
                                            @endif

                                            @if ($requisition->description)
                                                Description: {{ $requisition->description }}
                                            @endif
                                        </td>
                                        <td>{{$requisition->date }}</td>
                                        <td>
                                            {{$company->currency ? $company->currency->name : "" }} {{$company->currency ? $company->currency->symbol : "" }}{{number_format($requisition->total,2)}}</td>
                                       <td>
                                            @if ($requisition->total)
                                                <span class="label label-{{($requisition->status == 'Paid') ? 'success' : (($requisition->status == 'Partial') ? 'warning' : 'danger') }}">{{ $requisition->status }}</span>
                                            @else
                                                <span class="label label-info">No payment</span>  
                                            @endif
                                        </td>
                                         <td><span class="label label-{{($requisition->is_completed == False ? 'warning' : 'success') }}">{{ $requisition->is_completed == False ? "inprogress" : "completed" }}</span></td>
                                        <td>
                                            <span class="badge bg-{{($requisition->authorization == 'approved') ? 'success' : (($requisition->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($requisition->authorization == 'approved') ? 'approved' : (($requisition->authorization == 'rejected') ? 'rejected' : 'pending') }}</span>
                                            @if ($requisition->authorization_date)
                                                <br>
                                                 <small><strong style="background-color: orange">Date: {{$requisition->authorization_date}}</strong></small>  
                                               
                                            @endif
                                            @if ($requisition->reason)
                                                <br>
                                                <small><strong style="background-color: orange">Comments: {{$requisition->reason}}</strong></small>  
                                            @endif 
                                        </td>
                                        <td class="w-10 line-height-35 table-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="{{ route('requisitions.show', $requisition->id) }}"  ><i class="fa fa-eye color-default"></i> View</a></li>
                                                    <li><a href="#" wire:click="authorize({{$requisition->id}})"><i class="fas fa-gavel color-success"></i> Authorization</a></li>
                                                </ul>
                                            </div>
                                            @include('requisitions.delete')

                                    </td>
                                      </tr>
                                      @empty
                                        <tr>
                                            <td colspan="10">
                                                <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                                    No Requisitions Found ....
                                                </div>
                                               
                                            </td>
                                          </tr>
                                        @endforelse
                                    </tbody>
                                    @else
                                    <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                                    @endif

                                  </table>

                                  <nav class="text-center" style="float: right">
                                    <ul class="pagination rounded-corners">
                                        @if (isset($requisitions))
                                            {{ $requisitions->links() }} 
                                        @endif 
                                    </ul>
                                </nav>  

                                <!-- /.col-md-12 -->
                            </div>
                        </div>
                    </div>


                </div>
                <!-- /.row -->

            </div>
            <!-- /.container-fluid -->
        </section>
        <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="authorizationModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modal4Label"><i class="fas fa-gavel"></i> Authorize Requisition<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                    </div>
                    <form wire:submit.prevent="update()" >
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
    </div>
