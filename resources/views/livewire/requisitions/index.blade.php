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
                                <div class="row">
                                    <div class="col-md-12">
                                        <a href="#" data-toggle="modal" data-target="#requisitionModal"  class="btn btn-default"><i class="fa fa-plus-square-o"></i>Requisition</a>
                                        <a href="#" wire:click.prevent="exportRequisitionExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                        <a href="#" wire:click.prevent="exportRequisitionCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                                        <a href="#" wire:click.prevent="exportRequisitionPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a> 
                                    </div>
                                   
                                </div>
                              
                               
                                </div>
                               
                            </div>
                            <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">
                                <div class="col-md-3" style="float: right; padding-right:0px">
                                    <div class="form-group">
                                        <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search requisitions...">
                                    </div>

                                </div>
                                <table  class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                    <thead >
                                        <th class="th-sm">Requisition#
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
                                      <tr  
                                            @if($requisition->type == 'po_requisition')
                                                style="background-color:#e8f4fd;border-left:6px solid #17a2b8;"
                                            @elseif($requisition->type == 'payment_requisition')
                                                style="background-color:#eafaf1; border-left:6px solid #28a745;"
                                            @endif>
                                        <td>
                                            {{ucfirst($requisition->requisition_number)}} <br>
                                           
                                            <small class="text-muted">
                                                <strong>Type:</strong> 
                                                @if ($requisition->type == "po_requisition")
                                                    PO Requisition
                                                @else
                                                    Payment Requisition
                                                @endif <br>
                                                <strong>CreatedBy:</strong> {{ucfirst($requisition->user->name)}} {{ucfirst($requisition->user->surname)}} <br>
                                                <strong>CreatedOn:</strong> {{$requisition->created_at}}
                                            </small>
                                        </td>
                                        <td>
                                            {{ucfirst($requisition->employee ? $requisition->employee->name : "")}} {{ucfirst($requisition->employee ? $requisition->employee->surname : "")}}
                                            <br>
                                            <small><strong><i>{{ucfirst($requisition->department ? $requisition->department->name : "")}}</i></strong></small>
                                        </td>
                                        <td>
                                             @if ($requisition->requisition_items)
                                             <small>
                                                @php   
                                                    $count = 1;
                                                @endphp
                                                    @foreach ($requisition->requisition_items as $requisition_item)
                                                    <strong>{{$count++}}) </strong>
                                                    @if ($requisition_item->expense)
                                                        {{$requisition_item->expense ? $requisition_item->expense->name : ""}} 
                                                    @elseif($requisition_item->allowance)
                                                        {{ $requisition_item->allowance ? $requisition_item->allowance->name : ""}}
                                                    @elseif($requisition_item->product)
                                                        {{ $requisition_item->product->brand ? $requisition_item->product->brand->name : ""}} {{ $requisition_item->product ? $requisition_item->product->name : ""}}
                                                    @elseif($requisition_item->inventory)
                                                        {{ $requisition_item->inventory->product->brand ? $requisition_item->inventory->product->brand->name : ""}} {{ $requisition_item->inventory->product ? $requisition_item->inventory->product->name : ""}}
                                                    @endif
                                                    {{$requisition_item->qty ? " X (".$requisition_item->qty.")" : ""}}
                                                    @if ($requisition_item->amount)
                                                         @ {{ $requisition_item->currency ? $requisition_item->currency->name : ""}} {{ $requisition_item->currency ? $requisition_item->currency->symbol : ""}}{{ number_format($requisition_item->amount,2)}}
                                                         @if ($requisition_item->currency_id != $company->currency_id)
                                                            {{ " (".$company->currency->symbol.number_format($requisition_item->exchange_amount,2).")" }} at {{$requisition_item->exchange_rate}}
                                                             
                                                         @endif
                                                    @endif
                                                    {{$requisition_item->payment_method ? $requisition_item->payment_method->name : ""}}
                                                    @if (!$loop->last), @endif <br>
                                                @endforeach
                                             </small>
                                               
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
                                             @if ($requisition->paid_by_id)
                                                <br>
                                                 <small style="background-color: orange"><strong >MarkedBy: </strong> {{$this->findUser($requisition->paid_by_id)}}</small>  
                                            @endif
                                            @if ($requisition->paid_on)
                                                <br>
                                                 <small style="background-color: orange"><strong >Date: </strong> {{$requisition->paid_on}}</small>  
                                            @endif
                                            @if ($requisition->paid_comments)
                                                <br>
                                                <small style="background-color: orange"><strong >Comments: </strong> {{$requisition->paid_comments}}</small>  
                                            @endif 
                                        </td>
                                        <td>
                                            <span class="label label-{{($requisition->is_completed == False ? 'warning' : 'success') }}">{{ $requisition->is_completed == False ? "inprogress" : "completed" }}</span>
                                            @if ($requisition->completed_by_id)
                                                <br>
                                                 <small style="background-color: orange"><strong >MarkedBy: </strong> {{$this->findUser($requisition->completed_by_id)}}</small>  
                                            @endif
                                            @if ($requisition->completed_on)
                                                <br>
                                                 <small style="background-color: orange"><strong >Date: </strong> {{$requisition->completed_on}}</small>  
                                            @endif
                                            @if ($requisition->completed_comments)
                                                <br>
                                                <small style="background-color: orange"><strong >Comments: </strong> {{$requisition->completed_comments}}</small>  
                                            @endif 
                                        </td>
                                       <td>
                                            @php
                                                $isTwoStep = ($requisition->company?->enable_requisition_two_step_authorization ?? false)
                                                    && $requisition->type == "payment_requisition";

                                                $badgeClass = 'warning';
                                                $statusText = 'pending';

                                                if ($requisition->authorization == 'approved') {
                                                    $badgeClass = 'success';
                                                    $statusText = 'approved';
                                                } elseif ($requisition->authorization == 'rejected') {
                                                    $badgeClass = 'danger';
                                                    $statusText = 'rejected';
                                                } elseif ($isTwoStep && $requisition->authorization_stage == 1) {
                                                    $badgeClass = 'info';
                                                    $statusText = 'first approved';
                                                }
                                            @endphp

                                            <span class="badge bg-{{ $badgeClass }}">
                                                {{ $statusText }}
                                            </span>

                                            @if ($requisition->authorized_by_id)
                                                @php
                                                    $firstUser = App\Models\User::find($requisition->authorized_by_id);
                                                @endphp
                                                <br>
                                                <small style="background-color: orange">
                                                    <strong>1st AuthBy: </strong>
                                                    {{ $firstUser?->name }} {{ $firstUser?->surname }}
                                                </small>
                                            @endif

                                            @if ($requisition->authorization_date)
                                                <br>
                                                <small style="background-color: orange">
                                                    <strong>1st Date: </strong>
                                                    {{ $requisition->authorization_date }}
                                                </small>
                                            @endif

                                            @if ($requisition->reason)
                                                <br>
                                                <small style="background-color: orange">
                                                    <strong>1st Comments: </strong>
                                                    {{ $requisition->reason }}
                                                </small>
                                            @endif

                                            @if ($requisition->second_authorized_by_id)
                                                @php
                                                    $secondUser = App\Models\User::find($requisition->second_authorized_by_id);
                                                @endphp
                                                <br>
                                                <small style="background-color: #87ceeb">
                                                    <strong>2nd AuthBy: </strong>
                                                    {{ $secondUser?->name }} {{ $secondUser?->surname }}
                                                </small>
                                            @endif

                                            @if ($requisition->second_authorization_date)
                                                <br>
                                                <small style="background-color: #87ceeb">
                                                    <strong>2nd Date: </strong>
                                                    {{ $requisition->second_authorization_date }}
                                                </small>
                                            @endif

                                            @if ($requisition->second_authorization_comments)
                                                <br>
                                                <small style="background-color: #87ceeb">
                                                    <strong>2nd Comments: </strong>
                                                    {{ $requisition->second_authorization_comments }}
                                                </small>
                                            @endif
                                        </td>
                                        <td class="w-10 line-height-35 table-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                        <li><a href="{{route('requisitions.show', $requisition->id)}}"><i class="fa fa-eye color-default"></i>View</a></li>
                                                    @if ($requisition->authorization == "pending" || $requisition->authorization == "rejected")
                                                        <li><a href="#" wire:click="edit({{ $requisition->id }})"><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                    @endif
                                                    @if ($requisition->authorization == "approved")
                                                        <li><a href="{{route('requisitions.preview',$requisition->id)}}"  ><i class="fas fa-file-invoice color-primary"></i> Preview</a></li>
                                                    @endif
                                                    @if ($requisition->authorization == "approved")
                                                    @php
                                                        $employee_departments = Auth::user()->employee->departments;
                                                        foreach ($employee_departments as $department) {
                                                            $department_names[] = $department->name;
                                                        }
                                                        $roles = Auth::user()->roles;
                                                        foreach ($roles as $role) {
                                                            $role_names[] = $role->name;
                                                        }
                                                    @endphp
                                                    @if (in_array('Finance', $department_names) || in_array('Super Admin', $role_names))
                                                        @if ($requisition->total)
                                                            <li><a href="#" wire:click="showPayment({{$requisition->id}})"  ><i class="fas fa-check color-success"></i> Mark as paid</a></li>
                                                        @endif
                                                    @endif
                                                     <li><a href="#" wire:click="showStatus({{$requisition->id}})"  ><i class="fas fa-check color-success"></i> Mark as completed</a></li>
                                                  
                                               @endif
                                                    <li><a href="#" data-toggle="modal" data-target="#requisitionDeleteModal{{$requisition->id}}"><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                                   

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

 
        <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="requisitionPaymentModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modal4Label"><i class="fas fa-check"></i> Mark requisition as paid<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                    </div>
                    <form wire:submit.prevent="recordPayment()" >
                    <div class="modal-body">
                       <p>Are you sure you want to mark requisition
                        @if ($requisition_number)
                            {{$requisition_number}}
                        @endif
                        as paid?
                       </p>
                       <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Paid On<span class="required" style="color: red">*</span></label>
                                    <input type="date" wire:model.debounce.300ms="paid_date" class="form-control"  required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Comments</label>
                                    <textarea class="form-control" wire:model.debounce.300ms="paid_comments"  cols="30" rows="2"></textarea>
                                </div>
                            </div>
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

        <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="requisitionStatusModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modal4Label"><i class="fas fa-check"></i> Mark requisition  as comleted<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                    </div>
                    <form wire:submit.prevent="updateStatus()" >
                    <div class="modal-body">
                       <p>Are you sure you want to mark requisition
                        @if ($requisition_number)
                            {{$requisition_number}}
                        @endif
                        as completed?
                       </p>
                       <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Completed On<span class="required" style="color: red">*</span></label>
                                    <input type="date" wire:model.debounce.300ms="completed_date" class="form-control"  required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Comments</label>
                                    <textarea class="form-control" wire:model.debounce.300ms="completed_comments"  cols="30" rows="2"></textarea>
                                </div>
                            </div>
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
        

        <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="requisitionModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
            <div class="modal-dialog  mw-100 w-80" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Requisition <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                    </div>
                    <form wire:submit.prevent="store()" >
                        <div class="modal-body">
                            <div class="form-group" >
                                <label for="name">Select type of requisition</label>
                                <label class="radio-inline">
                                    <input type="radio" wire:model.debounce.300ms="requisition_type" value="po_requisition" name="optradio1">Purchase Order Requisition
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" wire:model.debounce.300ms="requisition_type" value="payment_requisition" name="optradio2">Payment Requisition
                                </label>
                            </div>
                            @if (isset($requisition_type))
                                @if ($requisition_type == "payment_requisition")
                                    <div class="form-group" >
                                        <label for="name">Select type of payment requisition to create.</label>
                                        <label class="radio-inline">
                                            <input type="radio" wire:model.debounce.300ms="requisition_for" value="Booking" name="optradio3">Booking
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" wire:model.debounce.300ms="requisition_for" value="Purchase" name="optradio4">Purchase Order
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" wire:model.debounce.300ms="requisition_for" value="Trip" name="optradio5" >Trip
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" wire:model.debounce.300ms="requisition_for" value="Other" name="optradio6" >Other
                                        </label>
                                    </div>

                                    @if (isset($requisition_for))
                                        @if (in_array($requisition_for,['Trip','Booking','Purchase']))
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="input-group">
                                                        <span class="input-group-addon">
                                                        Filter By
                                                        </span>
                                                        <select wire:model.debounce.300ms="filter" class="form-control" aria-label="..." >
                                                            <option value="created_at">Created At</option>
                                                            @if ($requisition_for == "Trip")
                                                                <option value="start_date">Date</option>
                                                            @elseif($requisition_for == "Booking")
                                                                <option value="in_date">Date</option>
                                                            @elseif($requisition_for == "Purchase")
                                                                <option value="date">Date</option>
                                                            @endif
                                                        
                                                        </select>
                                                    </div>
                                                    <!-- /input-group -->
                                                </div>
                                                <div class="col-lg-3" >
                                                    <div class="input-group">
                                                        <span class="input-group-addon">
                                                            From
                                                        </span>
                                                        <input type="date" wire:model.debounce.300ms="search_from"  class="form-control" aria-label="...">
                                                    </div>
                                                    <!-- /input-group -->
                                                </div>
                                                <div class="col-lg-3">
                                                    <div class="input-group">
                                                        <span class="input-group-addon">
                                                            To
                                                        </span>
                                                        <input type="date" wire:model.debounce.300ms="search_to"  class="form-control" aria-label="...">
                                                    </div>
                                                    <!-- /input-group -->
                                                </div>
                                                <!-- /input-group -->
                                            </div>
                                        @endif
                                        @if ($requisition_for == "Trip")
                                            <div class="form-group">
                                                <label for="country">Trips</label>
                                                <input type="text" wire:model.debounce.300ms="searchTrip" placeholder="Search with trip#, trip ref, horse reg#..." class="form-control">
                                                <select wire:model.debounce.300ms="selectedTrip" class="form-control" size="8">
                                                    <option value="">Select Trip</option>
                                                    @if (isset($trips))
                                                        @foreach ($trips as $trip)
                                                            <option value="{{ $trip->id }}">
                                                                    {{ $trip->trip_number }}{{ $trip->trip_ref ? "/".$trip->trip_ref : "" }} | {{ $trip->start_date }}
                                                                @if ($trip->horse)
                                                                    | {{ $trip->horse ? $trip->horse->registration_number : "" }} {{ $trip->horse->fleet_number ? "(".$trip->horse->fleet_number.")" : "" }} 
                                                                @endif
                                                                @if ($trip->driver)
                                                                    | {{ $trip->driver->employee ? $trip->driver->employee->name : "" }} {{ $trip->driver->employee ? $trip->driver->employee->surname : "" }}
                                                                @endif
                                                                    | {{ $trip->customer ? $trip->customer->name : "" }} | {{ $trip->loading_point ? $trip->loading_point->name : "" }} - {{ $trip->offloading_point ? $trip->offloading_point->name : "" }}
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                                @error('selectedTrip') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        @elseif($requisition_for == "Booking")
                                            <div class="form-group">
                                                <label for="country">Bookings</label>
                                                <input type="text" wire:model.debounce.300ms="searchBooking" placeholder="Search with booking#, service tye, horse, vehicle, trailer reg#..." class="form-control">
                                                <select wire:model.debounce.300ms="selectedBooking" class="form-control" size="8">
                                                    <option value="">Select Booking</option>
                                                    @foreach ($bookings as $booking)
                                                        <option value="{{ $booking->id }}"> {{ $booking->booking_number ? "Bkn#: ".$booking->booking_number : "" }}  {{ $booking->ticket ? "Tckt#: ".$booking->ticket->ticket_number : "" }}
                                                                {{ $booking->service_type ? $booking->service_type->name : "" }} 
                                                            @if ($booking->horse)
                                                                {{ $booking->horse ? $booking->horse->registration_number : "" }} {{ $booking->horse->fleet_number ? "(".$booking->horse->fleet_number.")" : "" }}
                                                            @elseif ($booking->vehicle)
                                                                {{ $booking->vehicle ? $booking->vehicle->registration_number : "" }} {{ $booking->vehicle->fleet_number ? "(".$booking->vehicle->fleet_number.")" : "" }}
                                                            @elseif ($booking->trailer)
                                                                {{ $booking->trailer ? $booking->trailer->registration_number : "" }} {{ $booking->trailer->fleet_number ? "(".$booking->trailer->fleet_number.")" : "" }}
                                                        @endif
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('selectedBooking') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        @elseif($requisition_for == "Purchase")
                                            <div class="form-group">
                                                <label for="country">Purchase Orders</label>
                                                <input type="text" wire:model.debounce.300ms="searchPurchase" placeholder="Search with order#, vendor, currency..." class="form-control">
                                                    <select wire:model.debounce.300ms="selectedPurchase" class="form-control" size="8">
                                                        <option value="">Select Purchase Order</option>
                                                        @foreach ($purchases as $purchase)
                                                            <option value="{{ $purchase->id }}">{{ $purchase->purchase_number }} | 
                                                                {{ $purchase->date}} |
                                                                {{ $purchase->vendor ? $purchase->vendor->name : "" }} |
                                                                {{ $purchase->currency ? $purchase->currency->name : "" }} {{ $purchase->currency ? $purchase->currency->symbol : "" }}{{ number_format($purchase->total ? $purchase->total : 0,2)}}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                @error('selectedPurchase') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        @endif
                                    @endif
                                @endif
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="requisition_date">Date<span class="required" style="color: red">*</span></label>
                                            <input type="date"  class="form-control" wire:model.debounce.300ms="requisition_date" placeholder="Enter Date" required />
                                            @error('requisition_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="country">RequestedBy<span class="required" style="color: red">*</span></label>
                                        <select wire:model.debounce.300ms="employee_id" class="form-control" required >
                                            <option value="">Select Employee</option>
                                            @foreach ($employees as $employee)
                                            <option value="{{ $employee->id }}">{{ $employee->name }} {{ $employee->surname }}</option>
                                            @endforeach
                                        </select>
                                            @error('employee_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name">Subject</label>
                                            <input type="text"  class="form-control" wire:model.debounce.300ms="subject" placeholder="Enter Requisition Subject" />
                                            @error('subject') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="country">Departments<span class="required" style="color: red">*</span></label>
                                        <select wire:model.debounce.300ms="department_id" class="form-control" required >
                                            <option value="">Select Department</option>
                                            @foreach ($departments as $department)
                                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                                            @endforeach
                                            
                                        </select>
                                            @error('department_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        @if (in_array($requisition_for,['Trip','Booking','Purchase']))
                                            <div class="form-group">
                                                <label for="Product">Expense Category</label>
                                                <select wire:model.debounce.300ms="selectedAccount" class="form-control" disabled>
                                                    <option value="">Select Category</option>
                                                    @foreach ($expense_accounts as $account)
                                                    <option value="{{$account->id}}">{{$account->name}}</option>
                                                    @endforeach
                                                </select>
                                                @error('selectedAccount') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                        @else
                                            <div class="form-group">
                                                <label for="Product">Expense Category<span class="required" style="color: red">*</span></label>
                                                <select wire:model.debounce.300ms="selectedAccount" class="form-control" required>
                                                    <option value="">Select Category</option>
                                                    @foreach ($expense_accounts as $account)
                                                    <option value="{{$account->id}}">{{$account->name}}</option>
                                                    @endforeach
                                                </select>
                                                @error('selectedAccount') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                        @endif
                                    </div> 
                                </div>
                                @if ($this->requisition_for == "Other")
                                    <div class="mt-30" style="background-color: lightgrey; padding:5px; border: 1px solid #333; border-radius: 5px;">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="country">Items<span class="required" style="color: red">*</span></label>
                                                    <select wire:model.debounce.300ms="selectedProduct.0" class="form-control" required>
                                                    <option value="">Select Item</option>
                                                        @foreach ($products as $product)
                                                            <option value="{{$product->id}}">
                                                                <strong>{{$product->name}}</strong> {{$product->description ? "| ".$product->description : ""}}
                                                            </option> 
                                                        @endforeach
                                                    </select>
                                                    <small>
                                                        <a href="{{route('product_services.all',['category' => 'bills'])}}" target="_blank"><i class="fa fa-plus-square-o"></i> New Product / Service</a>
                                                        <a href="#" wire:click.prevent="refresh('products')"  style="float: right; margin-top:5px;"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                                    </small> 
                                                    @error('selectedProduct.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label for="country">Currencies</label>
                                                    <select wire:model.debounce.300ms="selectedCurrency.0"  class="form-control"  >
                                                        <option value="">Select Currency</option>
                                                        @foreach ($currencies as $currency)
                                                        <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('selectedCurrency.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>

                                                @if (!empty($selectedCurrency) && isset($selectedCurrency[0]))
                                                    @if ($company && $selectedCurrency[0] != $company->currency_id)
                                                    @php
                                                        $currency = \App\Models\Currency::find($selectedCurrency[0] ?? null);
                                                    @endphp
                                                        <div class="form-group">
                                                            <label for="customer">Conversion Rate<span class="required" style="color: red">*</span></label>
                                                            <input type="number" step="any" min="0" class="form-control"
                                                                wire:model.debounce.300ms="exchange_rate.0"
                                                                placeholder="Exchange Rate 
                                                                {{$currency ?  'From '. $currency->name : "" }}
                                                                    {{ $company->currency ? 'To '.$company->currency->name : '' }}"
                                                                required>
                                                                @error('exchange_rate.0') <span class="text-danger error">{{ $message }}</span>@enderror

                                                            <small style="color: green">
                                                                {{ $currency ? '1 '.$currency->name.' is how much in' : '' }}
                                                                {{ $company->currency ? $company->currency->name.'?' : '' }}
                                                            </small>
                                                            <small>
                                                                {{ $exchange_amount[0] ?? '' ? 'The converted amount is: '.$exchange_amount[0] : '' }}
                                                            </small>
                                                            <br>
                                                        </div>
                                                    @endif
                                                @endif
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label for="country">Payment Methods</label>
                                                    <select wire:model.debounce.300ms="payment_method_id.0"  class="form-control"  >
                                                        <option value="">Select Payment Method</option>
                                                        @foreach ($payment_methods as $payment_method)
                                                        <option value="{{ $payment_method->id }}">{{ $payment_method->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('payment_method_id.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <div class="form-group">
                                                    <label for="name">Qty<span class="required" style="color: red">*</span></label>
                                                    <input type="number" class="form-control" wire:model.debounce.300ms="qty.0"  required />
                                                    @error('qty.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label for="name">Amount<span class="required" style="color: red">*</span></label>
                                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount.0" required/>
                                                    @error('amount.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                             <div class="col-md-2">
                                                <div class="form-group">
                                                    <label for="subheading">Sales Tax</label>
                                                    <select wire:model.debounce.300ms="selectedTax.0" class="form-control">
                                                        <option value="">Select Tax</option>
                                                            @foreach ($tax_accounts as $tax)
                                                            <option value="{{$tax->id}}">{{$tax->abbreviation}}</option>
                                                            @endforeach
                                                        </select>
                                                        <small><a href="{{ route('taxes.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Tax</a></small>
                                                    @error('selectedTax.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <br>
                                @endif
                                @foreach ($inputs as $key => $value)
                                    @if ($this->requisition_for == "Other")
                                        <div class="mt-15" style="background-color: lightgrey; padding:5px; border: 1px solid #333; border-radius: 5px;">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    @if ($requisition_for == "Trip")
                                                        @if (isset($expense_id[$value]))
                                                            <div class="form-group">
                                                                <label for="country">Expenses<span class="required" style="color: red">*</span></label>
                                                                <select wire:model.debounce.300ms="expense_id.{{ $value }}" {{in_array($this->requisition_for, ['Trip', 'Purchase']) ? "disabled" : ""}} class="form-control" required >
                                                                    <option value="">Select Expense</option>
                                                                    @foreach ($expenses as $expense)
                                                                    <option value="{{ $expense->id }}">{{ $expense->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                                @error('expense_id.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                            </div>
                                                        @else
                                                            <div class="form-group">
                                                                <label for="country">Allowances<span class="required" style="color: red">*</span></label>
                                                                <select wire:model.debounce.300ms="allowance_id.{{ $value }}" {{in_array($this->requisition_for, ['Trip', 'Purchase']) ? "disabled" : ""}} class="form-control" required >
                                                                    <option value="">Select Allowance</option>
                                                                    @foreach ($allowances as $allowance)
                                                                    <option value="{{ $allowance->id }}">{{ $allowance->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                                @error('allowance_id.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                            </div>
                                                        @endif
                                                    @else
                                                        <div class="form-group">
                                                            <label for="country">Items<span class="required" style="color: red">*</span></label>
                                                            <select wire:model.debounce.300ms="selectedProduct.{{$value}}" class="form-control" required {{in_array($this->requisition_for, ['Trip', 'Purchase', 'Booking']) ? "disabled" : ""}}>
                                                                <option value="">Select Item</option>
                                                                @foreach ($products as $product)
                                                                    <option value="{{$product->id}}">
                                                                        <strong>{{$product->name}}</strong> {{$product->description ? "| ".$product->description : ""}}
                                                                    </option> 
                                                                @endforeach
                                                            </select>
                                                            <small>
                                                                <a href="{{route('product_services.all',['category' => 'bills'])}}" target="_blank"><i class="fa fa-plus-square-o"></i> New Product / Service</a>
                                                                <a href="#" wire:click.prevent="refresh('products')"  style="float: right; margin-top:5px;"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                                            </small> 
                                                            @error('selectedProduct.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label for="country">Currencies</label>
                                                        <select wire:model.debounce.300ms="selectedCurrency.{{$value}}" {{in_array($this->requisition_for, ['Trip', 'Purchase', 'Booking']) ? "disabled" : ""}} class="form-control"  >
                                                            <option value="">Select Currency</option>
                                                            @foreach ($currencies as $currency)
                                                            <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('selectedCurrency.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                    </div>
                                                    @if (!empty($selectedCurrency) && isset($selectedCurrency[$value]))
                                                        @if ($company && $selectedCurrency[$value] != $company->currency_id)
                                                            @php
                                                                $currency = \App\Models\Currency::find($selectedCurrency[$value] ?? null);
                                                            @endphp
                                                            <div class="form-group">
                                                                <label for="customer">Conversion Rate<span class="required" style="color: red">*</span></label>
                                                                <input type="number" step="any" min="0" class="form-control"
                                                                    wire:model.debounce.300ms="exchange_rate.{{$value}}"
                                                                    placeholder="Exchange Rate 
                                                                        {{ $currency ? 'From '.$currency->name : '' }}
                                                                        {{ $company->currency ? 'To '.$company->currency->name : '' }}"
                                                                    required {{in_array($this->requisition_for, ['Trip', 'Purchase', 'Booking']) ? "disabled" : ""}}>
                                                                    @error('exchange_rate.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror

                                                                <small style="color: green">
                                                                    {{ $currency ? '1 '.$currency->name.' is how much in' : '' }}
                                                                    {{ $company->currency ? $company->currency->name.'?' : '' }}
                                                                </small>
                                                                <small>
                                                                    {{ $exchange_amount[$value] ?? '' ? 'The converted amount is: '.$exchange_amount[$value] : '' }}
                                                                </small>
                                                                <br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label for="country">Payment Methods</label>
                                                        <select wire:model.debounce.300ms="payment_method_id.{{$value}}"  class="form-control" {{in_array($this->requisition_for, ['Trip', 'Purchase', 'Booking']) ? "disabled" : ""}} >
                                                            <option value="">Select Payment Method</option>
                                                            @foreach ($payment_methods as $payment_method)
                                                            <option value="{{ $payment_method->id }}">{{ $payment_method->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('payment_method_id.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-1">
                                                    <div class="form-group">
                                                        <label for="name">Qty<span class="required" style="color: red">*</span></label>
                                                        <input type="number" class="form-control" wire:model.debounce.300ms="qty.{{ $value }}" {{in_array($this->requisition_for, ['Trip', 'Purchase', 'Booking']) ? "disabled" : "" }}  required/>
                                                        @error('qty.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-1">
                                                    <div class="form-group">
                                                        <label for="name">Amount<span class="required" style="color: red">*</span></label>
                                                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount.{{ $value }}" {{in_array($this->requisition_for, ['Trip', 'Purchase', 'Booking']) ? "disabled" : "" }} required />
                                                        @error('amount.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                <div class="form-group">
                                                    <label for="subheading">Sales Tax</label>
                                                    <select wire:model.debounce.300ms="selectedTax.{{ $value }}" class="form-control">
                                                        <option value="">Select Tax</option>
                                                            @foreach ($tax_accounts as $tax)
                                                            <option value="{{$tax->id}}">{{$tax->abbreviation}}</option>
                                                            @endforeach
                                                        </select>
                                                        <small><a href="{{ route('taxes.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Tax</a></small>
                                                    @error('selectedTax.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                                <div class="col-md-1">
                                                    <div class="form-group">
                                                        <label for=""></label>
                                                        <button class="btn btn-danger btn-rounded btn-xs" style="margin-top:23px"  wire:click.prevent="remove({{$key}})"> <i class="fa fa-times"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        @php
                                            $isIncluded = (bool)($included[$value] ?? true);
                                        @endphp
                                        <div class="mt-15"
                                            style="background-color: lightgrey; padding:5px; border: 1px solid #333; border-radius: 5px; {{ $isIncluded ? '' : 'opacity:0.55;' }}">
                                            <div class="row">
                                                {{-- Include checkbox --}}
                                                <div class="col-md-1">
                                                    <div class="form-group" style="margin-top: 23px;">
                                                        <label class="d-block" style="font-weight: 600;">Include</label>
                                                        <input type="checkbox"
                                                            wire:model="included.{{ $value }}">
                                                    </div>
                                                </div>
                                                {{-- Everything else: disable when unchecked --}}
                                                <div class="col-md-4">
                                                    @if ($requisition_for == "Trip")
                                                        @if (isset($expense_id[$value]))
                                                            <div class="form-group">
                                                                <label>Expenses<span class="required" style="color: red">*</span></label>
                                                                <select wire:model.debounce.300ms="expense_id.{{ $value }}"
                                                                        class="form-control"
                                                                        {{ (!$isIncluded || in_array($this->requisition_for, ['Trip', 'Purchase'])) ? 'disabled' : '' }}
                                                                        required>
                                                                    <option value="">Select Expense</option>
                                                                    @foreach ($expenses as $expense)
                                                                        <option value="{{ $expense->id }}">{{ $expense->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                                @error('expense_id.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                            </div>
                                                        @else
                                                            <div class="form-group">
                                                                <label>Allowances<span class="required" style="color: red">*</span></label>
                                                                <select wire:model.debounce.300ms="allowance_id.{{ $value }}"
                                                                        class="form-control"
                                                                        {{ (!$isIncluded || in_array($this->requisition_for, ['Trip', 'Purchase'])) ? 'disabled' : '' }}
                                                                        required>
                                                                    <option value="">Select Allowance</option>
                                                                    @foreach ($allowances as $allowance)
                                                                        <option value="{{ $allowance->id }}">{{ $allowance->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                                @error('allowance_id.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                            </div>
                                                        @endif
                                                    @else
                                                        <div class="form-group">
                                                            <label>Items<span class="required" style="color: red">*</span></label>
                                                            <select wire:model.debounce.300ms="selectedProduct.{{$value}}"
                                                                    class="form-control"
                                                                    {{ (!$isIncluded || in_array($this->requisition_for, ['Trip', 'Purchase', 'Booking'])) ? 'disabled' : '' }}
                                                                    required>
                                                                <option value="">Select Item</option>
                                                                @foreach ($products as $product)
                                                                    <option value="{{$product->id}}">
                                                                        <strong>{{$product->name}}</strong> {{$product->description ? "| ".$product->description : ""}}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <small>
                                                                <a href="{{route('product_services.all',['category' => 'bills'])}}" target="_blank">
                                                                    <i class="fa fa-plus-square-o"></i> New Product / Service
                                                                </a>
                                                                <a href="#" wire:click.prevent="refresh('products')" style="float: right; margin-top:5px;">
                                                                    <i class="fa fa-refresh" aria-hidden="true"></i>
                                                                </a>
                                                            </small>
                                                            @error('selectedProduct.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                        </div>
                                                    @endif
                                                </div>

                                                {{-- Repeat the same disable condition for other inputs --}}
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>Currencies</label>
                                                        <select wire:model.debounce.300ms="selectedCurrency.{{$value}}"
                                                                class="form-control"
                                                                {{ (!$isIncluded || in_array($this->requisition_for, ['Trip', 'Purchase', 'Booking'])) ? 'disabled' : '' }}>
                                                            <option value="">Select Currency</option>
                                                            @foreach ($currencies as $currency)
                                                                <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('selectedCurrency.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>

                                                <div class="col-md-1">
                                                    <div class="form-group">
                                                        <label>Qty<span class="required" style="color: red">*</span></label>
                                                        <input type="number"
                                                            class="form-control"
                                                            wire:model.debounce.300ms="qty.{{ $value }}"
                                                            {{ (!$isIncluded || in_array($this->requisition_for, ['Trip', 'Purchase', 'Booking'])) ? 'disabled' : '' }}
                                                            required />
                                                        @error('qty.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>

                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>Amount</label>
                                                        <input type="number" step="any"
                                                            class="form-control"
                                                            wire:model.debounce.300ms="amount.{{ $value }}"
                                                            {{ (!$isIncluded || in_array($this->requisition_for, ['Trip', 'Purchase', 'Booking'])) ? 'disabled' : '' }} />
                                                        @error('amount.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                {{-- No remove button anymore (optional: keep it as a “hard delete” elsewhere) --}}
                                            </div>
                                        </div>
                                    @endif
                                    <br>
                                @endforeach
                                @if ($this->requisition_for == "Other")
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i>Item</button>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <div class="form-group">
                                    <label for="name">Notes</label>
                                    <textarea class="form-control" wire:model.debounce.300ms="description" cols="30" rows="4"></textarea>
                                    @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                                <h5 class="underline mt-n">Upload Supporting Documents</h5>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="title">Title</label>
                                            <input type="text"  wire:model.debounce.300ms="title.0" class="form-control" placeholder="Title">
                                            @error('title.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="file">File</label>
                                            <input type="file" class="form-control" wire:model.debounce.300ms="file.0"  placeholder="Upload File " >
                                            @error('file.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="expires_at">Expiry Date</label>
                                            <input type="date" class="form-control" wire:model.debounce.300ms="expires_at.0" placeholder="dd/mm/yy" />
                                            @error('expires_at.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <!-- /.col-md-6 -->
                                </div>
                                @foreach ($documentInputs as $key => $value)
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="title">Title</label>
                                                <input type="text"  wire:model.debounce.300ms="title.{{$value}}" class="form-control" placeholder="Title">
                                                @error('title.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="file">File</label>
                                                <input type="file" class="form-control" wire:model.debounce.300ms="file.{{$value}}"  placeholder="Upload File ">
                                                @error('file.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="file">Expiry Date</label>
                                                <input type="date" class="form-control" wire:model.debounce.300ms="expires_at.{{$value}}"  placeholder="dd/mm/yy"/>
                                                @error('expires_at.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                        </div>
                                    
                                        <div class="col-md-1">
                                            <div class="form-group">
                                                <label for=""></label>
                                                <button class="btn btn-danger btn-rounded xs" style="margin-top:23px"  wire:click.prevent="documentsRemove({{$key}})"> <i class="fa fa-times"></i></button>
                                            </div>
                                        </div>
                                        <!-- /.col-md-6 -->
                                    </div>
                                @endforeach
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="documentsAdd({{$m}})"> <i class="fa fa-plus"></i> File</button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                                <button type="submit" class="btn bg-success btn-wide btn-rounded" ><i class="fa fa-save"></i>Save</button>
                            </div>
                            <!-- /.btn-group -->
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="requisitionEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
            <div class="modal-dialog  mw-100 w-80" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Edit Requisition <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                    </div>
                    <form wire:submit.prevent="update()" >
                        <div class="modal-body">
                            <div class="form-group" >
                                <label for="name">Select type of requisition</label>
                                <label class="radio-inline">
                                    <input type="radio" wire:model.debounce.300ms="requisition_type" value="po_requisition" name="optradio1">Purchase Order Requisition
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" wire:model.debounce.300ms="requisition_type" value="payment_requisition" name="optradio2">Payment Requisition
                                </label>
                            </div>
                            @if (isset($requisition_type))
                                @if ($requisition_type == "payment_requisition")
                                    <div class="form-group" >
                                        <label for="name">Select type of payment requisition to create.</label>
                                        <label class="radio-inline">
                                            <input type="radio" wire:model.debounce.300ms="requisition_for" value="Booking" name="optradio3">Booking
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" wire:model.debounce.300ms="requisition_for" value="Purchase" name="optradio4">Purchase Order
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" wire:model.debounce.300ms="requisition_for" value="Trip" name="optradio5" >Trip
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" wire:model.debounce.300ms="requisition_for" value="Other" name="optradio6" >Other
                                        </label>
                                    </div>
                                    @if (isset($requisition_for))
                                        @if (in_array($requisition_for,['Trip','Booking','Purchase']))
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="input-group">
                                                        <span class="input-group-addon">
                                                        Filter By
                                                        </span>
                                                        <select wire:model.debounce.300ms="filter" class="form-control" aria-label="..." >
                                                            <option value="created_at">Created At</option>
                                                            @if ($requisition_for == "Trip")
                                                                <option value="start_date">Date</option>
                                                            @elseif($requisition_for == "Booking")
                                                                <option value="in_date">Date</option>
                                                            @elseif($requisition_for == "Purchase")
                                                                <option value="date">Date</option>
                                                            @endif
                                                        
                                                        </select>
                                                    </div>
                                                    <!-- /input-group -->
                                                </div>
                                                <div class="col-lg-3" >
                                                    <div class="input-group">
                                                        <span class="input-group-addon">
                                                            From
                                                        </span>
                                                        <input type="date" wire:model.debounce.300ms="search_from"  class="form-control" aria-label="...">
                                                    </div>
                                                    <!-- /input-group -->
                                                </div>
                                                <div class="col-lg-3">
                                                    <div class="input-group">
                                                        <span class="input-group-addon">
                                                            To
                                                        </span>
                                                        <input type="date" wire:model.debounce.300ms="search_to"  class="form-control" aria-label="...">
                                                    </div>
                                                    <!-- /input-group -->
                                                </div>
                                                <!-- /input-group -->
                                            </div>
                                        @endif
                                        @if ($requisition_for == "Trip")
                                            <div class="form-group">
                                                <label for="country">Trips</label>
                                                <input type="text" wire:model.debounce.300ms="searchTrip" placeholder="Search with trip#, trip ref, horse reg#..." class="form-control">
                                                <select wire:model.debounce.300ms="selectedTrip" class="form-control" size="8">
                                                    <option value="">Select Trip</option>
                                                    @if (isset($trips))
                                                        @foreach ($trips as $trip)
                                                            <option value="{{ $trip->id }}">
                                                                    {{ $trip->trip_number }}{{ $trip->trip_ref ? "/".$trip->trip_ref : "" }} | {{ $trip->start_date }}
                                                                @if ($trip->horse)
                                                                    | {{ $trip->horse ? $trip->horse->registration_number : "" }} {{ $trip->horse->fleet_number ? "(".$trip->horse->fleet_number.")" : "" }} 
                                                                @endif
                                                                @if ($trip->driver)
                                                                    | {{ $trip->driver->employee ? $trip->driver->employee->name : "" }} {{ $trip->driver->employee ? $trip->driver->employee->surname : "" }}
                                                                @endif
                                                                    | {{ $trip->customer ? $trip->customer->name : "" }} | {{ $trip->loading_point ? $trip->loading_point->name : "" }} - {{ $trip->offloading_point ? $trip->offloading_point->name : "" }}
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                                @error('selectedTrip') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        @elseif($requisition_for == "Booking")
                                            <div class="form-group">
                                                <label for="country">Garage Bookings</label>
                                                <input type="text" wire:model.debounce.300ms="searchBooking" placeholder="Search with booking#, service tye, horse, vehicle, trailer reg#..." class="form-control">
                                                <select wire:model.debounce.300ms="selectedBooking" class="form-control" size="8">
                                                    <option value="">Select Booking</option>
                                                    @foreach ($bookings as $booking)
                                                        <option value="{{ $booking->id }}"> {{ $booking->booking_number ? "Bkn#: ".$booking->booking_number : "" }}  {{ $booking->ticket ? "Tckt#: ".$booking->ticket->ticket_number : "" }}
                                                                {{ $booking->service_type ? $booking->service_type->name : "" }} 
                                                            @if ($booking->horse)
                                                                {{ $booking->horse ? $booking->horse->registration_number : "" }} {{ $booking->horse->fleet_number ? "(".$booking->horse->fleet_number.")" : "" }}
                                                            @elseif ($booking->vehicle)
                                                                {{ $booking->vehicle ? $booking->vehicle->registration_number : "" }} {{ $booking->vehicle->fleet_number ? "(".$booking->vehicle->fleet_number.")" : "" }}
                                                            @elseif ($booking->trailer)
                                                                {{ $booking->trailer ? $booking->trailer->registration_number : "" }} {{ $booking->trailer->fleet_number ? "(".$booking->trailer->fleet_number.")" : "" }}
                                                        @endif
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('selectedBooking') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        @elseif($requisition_for == "Purchase")
                                            <div class="form-group">
                                                <label for="country">Purchase Orders</label>
                                                <input type="text" wire:model.debounce.300ms="searchPurchase" placeholder="Search with order#, vendor, currency..." class="form-control">
                                                    <select wire:model.debounce.300ms="selectedPurchase" class="form-control" size="8">
                                                        <option value="">Select Purchase Order</option>
                                                        @foreach ($purchases as $purchase)
                                                            <option value="{{ $purchase->id }}">{{ $purchase->purchase_number }} | 
                                                                {{ $purchase->date}} |
                                                                {{ $purchase->vendor ? $purchase->vendor->name : "" }} |
                                                                {{ $purchase->currency ? $purchase->currency->name : "" }} {{ $purchase->currency ? $purchase->currency->symbol : "" }}{{ number_format($purchase->total ? $purchase->total : 0,2)}}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                @error('selectedPurchase') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        @endif
                                    @endif
                                @endif
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name">Date<span class="required" style="color: red">*</span></label>
                                            <input type="date"  class="form-control" wire:model.debounce.300ms="requisition_date" placeholder="Enter Date" required />
                                            @error('requisition_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="country">RequestedBy<span class="required" style="color: red">*</span></label>
                                        <select wire:model.debounce.300ms="employee_id" class="form-control" required >
                                            <option value="">Select Employee</option>
                                            @foreach ($employees as $employee)
                                            <option value="{{ $employee->id }}">{{ $employee->name }} {{ $employee->surname }}</option>
                                            @endforeach
                                        </select>
                                            @error('employee_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name">Subject</label>
                                            <input type="text"  class="form-control" wire:model.debounce.300ms="subject" placeholder="Enter Requisition Subject" />
                                            @error('subject') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="country">Departments<span class="required" style="color: red">*</span></label>
                                        <select wire:model.debounce.300ms="department_id" class="form-control" required >
                                            <option value="">Select Department</option>
                                            @foreach ($departments as $department)
                                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                                            @endforeach
                                            
                                        </select>
                                            @error('department_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        @if (in_array($this->requisition_for, ['Trip', 'Purchase', 'Booking']))
                                            <div class="form-group">
                                                <label for="Product">Expense Category</label>
                                                <select wire:model.debounce.300ms="selectedAccount" class="form-control" disabled>
                                                    <option value="">Select Category</option>
                                                    @foreach ($expense_accounts as $account)
                                                    <option value="{{$account->id}}">{{$account->name}}</option>
                                                    @endforeach
                                                </select>
                                                @error('selectedAccount') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                        @else
                                            <div class="form-group">
                                                <label for="Product">Expense Category<span class="required" style="color: red">*</span></label>
                                                <select wire:model.debounce.300ms="selectedAccount" class="form-control" required>
                                                    <option value="">Select Category</option>
                                                    @foreach ($expense_accounts as $account)
                                                    <option value="{{$account->id}}">{{$account->name}}</option>
                                                    @endforeach
                                                </select>
                                                @error('selectedAccount') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                @if ($requisition_items)
                                    @foreach ($requisition_items as $key => $value)
                                        <div class="mt-15" style="background-color: lightgrey; padding:5px; border: 1px solid #333; border-radius: 5px;">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    @if ($requisition_for == "Trip")
                                                    @if (isset($current_expense_id[$key]))
                                                    <div class="form-group">
                                                            <label for="country">Expenses<span class="required" style="color: red">*</span></label>
                                                            <select wire:model.debounce.300ms="current_expense_id.{{ $key }}" {{in_array($this->requisition_for, ['Trip', 'Purchase']) ? "disabled" : ""}} class="form-control" required >
                                                                <option value="">Select Expense</option>
                                                                @foreach ($expenses as $expense)
                                                                <option value="{{ $expense->id }}">{{ $expense->name }}</option>
                                                                @endforeach
                                                            </select>
                                                            @error('current_expense_id.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                        </div>
                                                    @else
                                                    <div class="form-group">
                                                            <label for="country">Allowances<span class="required" style="color: red">*</span></label>
                                                            <select wire:model.debounce.300ms="current_allowance_id.{{ $key }}" {{in_array($this->requisition_for, ['Trip', 'Purchase']) ? "disabled" : ""}} class="form-control" required >
                                                                <option value="">Select Allowance</option>
                                                                @foreach ($allowances as $allowance)
                                                                <option value="{{ $allowance->id }}">{{ $allowance->name }}</option>
                                                                @endforeach
                                                            </select>
                                                            @error('current_allowance_id.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                        </div>
                                                    @endif
                                                    
                                                    @else
                                                        <div class="form-group">
                                                            <label for="country">Items<span class="required" style="color: red">*</span></label>
                                                            <select wire:model.debounce.300ms="current_selectedProduct.{{$key}}" class="form-control" required {{in_array($this->requisition_for, ['Trip', 'Purchase']) ? "disabled" : ""}}>
                                                                <option value="">Select Item</option>
                                                                @foreach ($products as $product)
                                                                    <option value="{{$product->id}}">
                                                                        <strong>{{$product->name}}</strong> {{$product->description ? "| ".$product->description : ""}}
                                                                    </option> 
                                                                @endforeach
                                                            </select>
                                                            <small>
                                                                <a href="{{route('product_services.all',['category' => 'bills'])}}" target="_blank"><i class="fa fa-plus-square-o"></i> New Product / Service</a>
                                                                <a href="#" wire:click.prevent="refresh('products')"  style="float: right; margin-top:5px;"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                                            </small> 
                                                            @error('current_selectedProduct.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label for="country">Currencies<span class="required" style="color: red">*</span></label>
                                                    <select wire:model.debounce.300ms="current_selectedCurrency.{{$key}}" {{in_array($this->requisition_for, ['Trip', 'Purchase']) ? "disabled" : ""}} class="form-control" required >
                                                        <option value="">Select Currency</option>
                                                        @foreach ($currencies as $currency)
                                                        <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                                        @endforeach
                                                    </select>
                                                        @error('current_selectedCurrency.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                    </div>
                                                    @if (!empty($selectedCurrency) && isset($selectedCurrency[0]))
                                                        @if ($company && $selectedCurrency[0] != $company->currency_id)
                                                            @php
                                                                $currency = \App\Models\Currency::find($selectedCurrency[0] ?? null);
                                                            @endphp
                                                            <div class="form-group">
                                                                <label for="customer">Conversion Rate<span class="required" style="color: red">*</span></label>
                                                                <input type="number" step="any" min="0" class="form-control"
                                                                    wire:model.debounce.300ms="current_exchange_rate.{{$key}}"
                                                                    placeholder="Exchange Rate 
                                                                        {{ $currency ? 'From '.$currency->name : '' }}
                                                                        {{ $company->currency ? 'To '.$company->currency->name : '' }}"
                                                                    required {{in_array($this->requisition_for, ['Trip', 'Purchase']) ? "disabled" : ""}}>
                                                                    @error('current_exchange_rate.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror

                                                            <small style="color: green">
                                                                {{ $currency ? '1 '.$currency->name.' is how much in' : '' }}
                                                                {{ $company->currency ? $company->currency->name.'?' : '' }}
                                                            </small>
                                                                <small>
                                                                    {{ $current_exchange_amount[$key] ?? '' ? 'The converted amount is: '.$current_exchange_amount[$key] : '' }}
                                                                </small>
                                                                <br>
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label for="country">Payment Methods</label>
                                                        <select wire:model.debounce.300ms="payment_method_id.{{$key}}"  class="form-control"  >
                                                            <option value="">Select Payment Method</option>
                                                            @foreach ($payment_methods as $payment_method)
                                                            <option value="{{ $payment_method->id }}">{{ $payment_method->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('payment_method_id.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-1">
                                                    <div class="form-group">
                                                        <label for="name">Qty<span class="required" style="color: red">*</span></label>
                                                        <input type="number" class="form-control" wire:model.debounce.300ms="current_qty.{{$key}}" {{in_array($this->requisition_for, ['Trip', 'Purchase']) ? "disabled" : ""}} required />
                                                        @error('current_qty.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label for="name">Amount<span class="required" style="color: red">*</span></label>
                                                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="current_amount.{{$key}}" {{in_array($this->requisition_for, ['Trip', 'Purchase']) ? "disabled" : ""}}  required />
                                                        @error('current_amount.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-1">
                                                    <div class="form-group">
                                                        <label for=""></label>
                                                        <button class="btn btn-danger btn-rounded xs" style="margin-top:23px"  wire:click.prevent="remove({{$key}})"> <i class="fa fa-times"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <br>
                                    @endforeach
                                @endif
                                @foreach ($inputs as $key => $value)
                                    <div class="mt-15" style="background-color: lightgrey; padding:5px; border: 1px solid #333; border-radius: 5px;">
                                        <div class="row">
                                            <div class="col-md-4">
                                                @if ($requisition_for == "Trip")
                                                    @if (isset($expense_id[$value]))
                                                        <div class="form-group">
                                                            <label for="country">Expenses<span class="required" style="color: red">*</span></label>
                                                            <select wire:model.debounce.300ms="expense_id.{{ $value }}" {{in_array($this->requisition_for, ['Trip', 'Purchase']) ? "disabled" : ""}} class="form-control" required >
                                                                <option value="">Select Expense</option>
                                                                @foreach ($expenses as $expense)
                                                                <option value="{{ $expense->id }}">{{ $expense->name }}</option>
                                                                @endforeach
                                                            </select>
                                                            @error('expense_id.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                        </div>
                                                    @else
                                                        <div class="form-group">
                                                            <label for="country">Allowances<span class="required" style="color: red">*</span></label>
                                                            <select wire:model.debounce.300ms="allowance_id.{{ $value }}" {{in_array($this->requisition_for, ['Trip', 'Purchase']) ? "disabled" : ""}} class="form-control" required >
                                                                <option value="">Select Allowance</option>
                                                                @foreach ($allowances as $allowance)
                                                                <option value="{{ $allowance->id }}">{{ $allowance->name }}</option>
                                                                @endforeach
                                                            </select>
                                                            @error('allowance_id.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                        </div>
                                                    @endif
                                                @else
                                                    <div class="form-group">
                                                        <label for="country">Items<span class="required" style="color: red">*</span></label>
                                                        <select wire:model.debounce.300ms="selectedProduct.{{$value}}" class="form-control" required>
                                                        <option value="">Select Item</option>
                                                            @foreach ($products as $product)
                                                                <option value="{{$product->id}}">
                                                                    <strong>{{$product->name}}</strong> {{$product->description ? "| ".$product->description : ""}}
                                                                </option> 
                                                            @endforeach
                                                        </select>
                                                        <small>
                                                            <a href="{{route('product_services.all',['category' => 'bills'])}}" target="_blank"><i class="fa fa-plus-square-o"></i> New Product / Service</a>
                                                            <a href="#" wire:click.prevent="refresh('products')"  style="float: right; margin-top:5px;"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                                        </small> 
                                                        @error('selectedProduct.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label for="country">Currencies<span class="required" style="color: red">*</span></label>
                                                    <select wire:model.debounce.300ms="selectedCurrency.{{$value}}" {{in_array($this->requisition_for, ['Trip', 'Purchase']) ? "disabled" : ""}} class="form-control" required >
                                                        <option value="">Select Currency</option>
                                                        @foreach ($currencies as $currency)
                                                        <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('selectedCurrency.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                                @if (!empty($selectedCurrency) && isset($selectedCurrency[$value]))
                                                    @if ($company && $selectedCurrency[$value] != $company->currency_id)
                                                        @php
                                                            $currency = \App\Models\Currency::find($selectedCurrency[$value] ?? null);
                                                        @endphp
                                                        <div class="form-group">
                                                            <label for="customer">Conversion Rate<span class="required" style="color: red">*</span></label>
                                                            <input type="number" step="any" min="0" class="form-control"
                                                                wire:model.debounce.300ms="exchange_rate.{{$value}}"
                                                                placeholder="Exchange Rate 
                                                                    {{  $currency ? 'From '. $currency->name : '' }}
                                                                    {{ $company->currency ? 'To '.$company->currency->name : '' }}"
                                                                required {{in_array($this->requisition_for, ['Trip', 'Purchase']) ? "disabled" : ""}}>
                                                                @error('exchange_rate.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror

                                                            <small style="color: green">
                                                                {{ $currency ? '1 '.$currency->name.' is how much in' : '' }}
                                                                {{ $company->currency ? $company->currency->name.'?' : '' }}
                                                            </small>
                                                            <small>
                                                                {{ $exchange_amount[$value] ?? '' ? 'The converted amount is: '.$exchange_amount[$value] : '' }}
                                                            </small>
                                                            <br>
                                                        </div>
                                                    @endif
                                                @endif
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label for="country">Payment Methods</label>
                                                    <select wire:model.debounce.300ms="payment_method_id.{{$value}}"  class="form-control"  >
                                                        <option value="">Select Payment Method</option>
                                                        @foreach ($payment_methods as $payment_method)
                                                        <option value="{{ $payment_method->id }}">{{ $payment_method->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('payment_method_id.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <div class="form-group">
                                                    <label for="name">Qty<span class="required" style="color: red">*</span></label>
                                                    <input type="number" class="form-control" wire:model.debounce.300ms="qty.{{ $value }}" {{in_array($this->requisition_for, ['Trip', 'Purchase']) ? "disabled" : ""}} required />
                                                    @error('qty.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label for="name">Amount<span class="required" style="color: red">*</span></label>
                                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount.{{ $value }}" {{in_array($this->requisition_for, ['Trip', 'Purchase']) ? "disabled" : ""}}  required />
                                                    @error('amount.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                           
                                            <div class="col-md-1">
                                                <div class="form-group">
                                                    <label for=""></label>
                                                    <button class="btn btn-danger btn-rounded xs" style="margin-top:23px"  wire:click.prevent="remove({{$key}})"> <i class="fa fa-times"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <br>
                                @endforeach
                                @if ($this->requisition_for == "Other")
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i>Item</button>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <div class="form-group">
                                    <label for="name">Notes</label>
                                    <textarea class="form-control" wire:model.debounce.300ms="description" cols="30" rows="4"></textarea>
                                    @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            @endif
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


    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="product_serviceModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog  mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> New Product / Service<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="comment">Name<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="item_name" placeholder="Enter Item Name" required>
                                @error('item_name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="comment">Description</label>
                            <textarea class="form-control" wire:model.debounce.300ms="item_description" cols="30" rows="4"></textarea>
                                @error('item_description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-10">
                                <label for=""></label>
                                <input type="checkbox" wire:model.debounce.300ms="sell"   class="line-style" />
                                <label for="one" class="radio-label">Sell this?</label>
                                @error('sell') <span class="text-danger error">{{ $message }}</span>@enderror
                                <br>
                                <small>Allow this product or service to be added to Invoices.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-10">
                                <input type="checkbox" wire:model.debounce.300ms="buy"   class="line-style" disabled/>
                                <label for="one" class="radio-label">Buy this?</label>
                                @error('buy') <span class="text-danger error">{{ $message }}</span>@enderror
                                <br>
                                <small>Allow this product or service to be added to Bills.</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        @if (!is_null($sell) && $sell == True)
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="subheading">Income Accounts<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="income_account_id" class="form-control" required>
                                    <option value="">Select Income Account</option>
                                        @foreach ($income_accounts as $account)
                                        <option value="{{$account->id}}">{{$account->name}} </option> 
                                        @endforeach
                                    </select>
                                    <small><a href="{{ route('accounts.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Income Account</a></small> 
                                @error('income_account_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="name">Selling Price</label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="sell_price" placeholder="Enter Selling Price">
                                @error('sell_price') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        @else
                        <div class="col-md-6">

                        </div>
                        @endif
                              
                        @if (!is_null($buy) && $buy == True)
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="subheading">Expense Category<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="expense_account_id" class="form-control" required>
                                    <option value="">Select Category</option>
                                        @foreach ($expense_accounts as $account)
                                        <option value="{{$account->id}}">{{$account->name}} </option> 
                                        @endforeach
                                    </select> 
                                @error('expense_account_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="name">Buying Price</label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="buy_price" placeholder="Enter Buying Price">
                                @error('buy_price') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        @endif
                        
                    </div>
                    <div class="form-group">
                        <label for="subheading">Sales Tax</label>
                        <select wire:model.debounce.300ms="tax_id" class="form-control">
                            <option value="">Select Tax</option>
                                @foreach ($tax_accounts as $tax)
                                <option value="{{$tax->id}}">{{$tax->abbreviation}}</option> 
                                @endforeach
                            </select>
                            <small><a href="{{ route('taxes.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Tax</a></small> 
                        @error('tax_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button wire:click.prevent="storeItem()" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Save</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>



    </div>
