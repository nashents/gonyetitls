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
                                        <th class="th-sm">Ccy
                                        </th>
                                        <th class="th-sm">Total
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
                                                @elseif($requisition_item->product)
                                                    {{ $requisition_item->product->brand ? $requisition_item->product->brand->name : ""}} {{ $requisition_item->product ? $requisition_item->product->name : ""}}
                                                @elseif($requisition_item->inventory)
                                                    {{ $requisition_item->inventory->product->brand ? $requisition_item->inventory->product->brand->name : ""}} {{ $requisition_item->inventory->product ? $requisition_item->inventory->product->name : ""}}
                                                @endif
                                                @   @if ($requisition_item->amount)
                                                {{ $requisition_item->requisition->currency ? $requisition_item->requisition->currency->symbol : ""}}{{ number_format($requisition_item->amount,2)}}
                                            @endif
                                                @if (!$loop->last),@endif
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

                                            @elseif ($requisition->purchase_id)
                                                @php
                                                    $purchase = \App\Models\Purchase::find($requisition->purchase_id);
                                                @endphp
                                                @if($purchase)
                                                   
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
                                                Description: {{ $requisition->description }}
                                            @endif
                                        </td>
                                        <td>{{$requisition->date }}</td>
                                        <td>{{$requisition->currency ? $requisition->currency->name : "" }}</td>
                                        <td>{{$requisition->currency ? $requisition->currency->symbol : "" }}{{number_format($requisition->total,2)}}</td>
                                        <td><span class="label label-{{($requisition->status == 'Paid') ? 'success' : (($requisition->status == 'Partial') ? 'warning' : 'danger') }}">{{ $requisition->status }}</span></td>
                                        <td><span class="badge bg-{{($requisition->authorization == 'approved') ? 'success' : (($requisition->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($requisition->authorization == 'approved') ? 'approved' : (($requisition->authorization == 'rejected') ? 'rejected' : 'pending') }}</span>
                                            @if ($requisition->reason)
                                            <br>
                                            <small><strong style="background-color: orange">{{$requisition->reason}}</strong></small>  
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
                                                    <li><a href="#" wire:click="showPayment({{$requisition->id}})"  ><i class="fas fa-check color-success"></i> Mark as paid</a></li>
                                                    @endif
                                                  
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
            <div class="modal-dialog  mw-100 w-50" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Requisition <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                    </div>
                    <form wire:submit.prevent="store()" >
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="country">RequestedBy<span class="required" style="color: red">*</span></label>
                                   <select wire:model.defer="employee_id" class="form-control" required >
                                       <option value="">Select Employee</option>
                                       @foreach ($employees as $employee)
                                       <option value="{{ $employee->id }}">{{ $employee->name }} {{ $employee->surname }}</option>
                                       @endforeach
                                   </select>
                                    @error('employee_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="country">Departments<span class="required" style="color: red">*</span></label>
                                   <select wire:model.defer="department_id" class="form-control" required >
                                       <option value="">Select Department</option>
                                       @foreach ($departments as $department)
                                       <option value="{{ $department->id }}">{{ $department->name }}</option>
                                       @endforeach
                                      
                                   </select>
                                    @error('department_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
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
                            </div>
                            
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="name">Subject</label>
                                    <input type="text" min="1" class="form-control" wire:model.debounce.300ms="subject" placeholder="Enter Requisition Subject" />
                                    @error('subject') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="name">Date<span class="required" style="color: red">*</span></label>
                                    <input type="date" min="1" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter Date" required />
                                    @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="country">Currencies<span class="required" style="color: red">*</span></label>
                                   <select wire:model.debounce.300ms="currency_id" class="form-control" required >
                                       <option value="">Select Currency</option>
                                       @foreach ($currencies as $currency)
                                       <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                       @endforeach
                                   </select>
                                    @error('currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                                @if (!is_null($currency_id))
                                @if ($company)
                                    @if ($currency_id != $company->currency_id)
                                    <div class="form-group">
                                        <label for="customer">Conversion Rate</label>
                                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="exchange_rate"  placeholder="Exchange Rate {{$selected_currency ? "From ".$selected_currency->name : ""}} {{$company->currency ? "To ".$company->currency->name : ""}}" >
                                        @error('exchange_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                        <small style="color: green">{{$selected_currency ? " 1 ".$selected_currency->name." is how much in" : ""}} {{$company->currency ? $company->currency->name." ?" : ""}}</small>
                                        <small>{{$exchange_amount ? "The converted amount is: ".$exchange_amount : ""}}</small> 
                                    </div> 
                                    @endif
                                @endif
                            @endif
                            </div>
                            
                        </div>
                        <div class="form-group" >
                            <label for="name">Attach requisition to a</label>
                           
                            <label class="radio-inline">
                                <input type="radio" wire:model.debounce.300ms="requisition_for" value="Booking" name="optradio">Garage Booking
                            </label>
                            <label class="radio-inline">
                                <input type="radio" wire:model.debounce.300ms="requisition_for" value="Purchase" name="optradio">Purchase Order
                            </label>
                             <label class="radio-inline">
                                <input type="radio" wire:model.debounce.300ms="requisition_for" value="Trip" name="optradio" >Trip
                            </label>
                        </div>
                        @if (isset($requisition_for))
                            @if ($requisition_for == "Trip")
                                <div class="form-group">
                                    <label for="country">Trips</label>
                                    <input type="text" wire:model.debounce.300ms="searchTrip" placeholder="Search with trip#, trip ref, horse reg#..." class="form-control">
                                <select wire:model.debounce.300ms="trip_id" class="form-control" size="8">
                                    <option value="">Select Trip</option>
                                    @if (isset($trips))
                                        @foreach ($trips as $trip)
                                        <option value="{{ $trip->id }}">{{ $trip->trip_number }} | {{ $trip->horse ? $trip->horse->registration_number : "" }} 
                                            @if ($trip->driver)
                                            {{ $trip->driver->employee ? $trip->driver->employee->name : "" }} {{ $trip->driver->employee ? $trip->driver->employee->surname : "" }}
                                            @endif
                                            | {{ $trip->customer ? $trip->customer->name : "" }} | {{ $trip->loading_point ? $trip->loading_point->name : "" }} - {{ $trip->offloading_point ? $trip->offloading_point->name : "" }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                    @error('trip_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            @elseif($requisition_for == "Booking")
                                <div class="form-group">
                                    <label for="country">Garage Bookings</label>
                                    <input type="text" wire:model.debounce.300ms="searchBooking" placeholder="Search with booking#, horse, vehicle, trailer reg#..." class="form-control">
                                    <select wire:model.debounce.300ms="booking_id" class="form-control" size="8">
                                        <option value="">Select Booking</option>
                                        @foreach ($bookings as $booking)
                                            <option value="{{ $booking->id }}">{{ $booking->booking_number }} | 
                                                {{ $booking->service_type ? $booking->service_type->name : "" }} |
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
                                    @error('booking_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            @elseif($requisition_for == "Purchase")
                                <div class="form-group">
                                        <label for="country">Purchase Orders</label>
                                        <input type="text" wire:model.debounce.300ms="searchPurchase" placeholder="Search with booking#, horse, vehicle, trailer reg#..." class="form-control">
                                        <select wire:model.debounce.300ms="purchase_id" class="form-control" size="8">
                                            <option value="">Select Booking</option>
                                            @foreach ($purchases as $purchase)
                                                <option value="{{ $purchase->id }}">{{ $purchase->purchase_number }} | 
                                                     {{ $purchase->date}} |
                                                    {{ $purchase->vendor ? $purchase->vendor->name : "" }} |
                                                    {{ $purchase->currency ? $purchase->currency->name : "" }} {{ $purchase->currency ? $purchase->currency->symbol : "" }}{{ number_format($purchase->total ? $purchase->total : 0,2)}}
                                                </option>
                                            @endforeach
                                            
                                        </select>
                                        @error('purchase_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            @endif
                        @endif
                        <div class="mb-20 mt-10">
                                <input type="checkbox" wire:model.debounce.300ms="items" {{$requisition_for == "Purchase" ? "disabled" : ""}}  class="line-style" />
                                <label for="one" class="radio-label">Add expense items to requisition.</label>
                                @error('items') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        @if ($items == True)
                        <div class="row">
                            <div class="col-md-8">
                                @if ($requisition_for == "Trip")
                                    <div class="form-group">
                                        <label for="country">Expenses<span class="required" style="color: red">*</span></label>
                                        <select wire:model.defer="expense_id.0" class="form-control" required >
                                            <option value="">Select Expense</option>
                                            @foreach ($expenses as $expense)
                                            <option value="{{ $expense->id }}">{{ $expense->name }}</option>
                                            @endforeach
                                        </select>
                                        {{-- <small><a href="{{ route('expenses.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Expense</a></small>  --}}
                                        @error('expense_id.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                @else
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
                                          <small><a href="#" wire:click.prevent="showItem({{0}})"><i class="fa fa-plus-square-o"></i> New Product / Service</a></small> 
                                        @error('selectedProduct.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="name">Qty<span class="required" style="color: red">*</span></label>
                                    <input type="number" min="1" class="form-control" wire:model.debounce.300ms="qty.0"  required />
                                    @error('qty.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="name">Amount<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" min="1" class="form-control" wire:model.debounce.300ms="amount.0" required />
                                    @error('amount.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                       
                       
                        @foreach ($inputs as $key => $value)
                        <div class="row">
                            
                            <div class="col-md-6">
                                 @if ($requisition_for == "Trip")
                                <div class="form-group">
                                    <label for="country">Expenses<span class="required" style="color: red">*</span></label>
                                   <select wire:model.debounce.300ms="expense_id.{{ $value }}" class="form-control" required >
                                       <option value="">Select Expense</option>
                                       @foreach ($expenses as $expense)
                                       <option value="{{ $expense->id }}">{{ $expense->name }}</option>
                                       @endforeach
                                   </select>
                                    @error('expense_id.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
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
                                         <small><a href="#" wire:click.prevent="showItem({{$value}})"><i class="fa fa-plus-square-o"></i> New Product / Service</a></small> 
                                        @error('selectedProduct.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="name">Qty<span class="required" style="color: red">*</span></label>
                                    <input type="number" class="form-control" wire:model.debounce.300ms="qty.{{ $value }}"  required />
                                    @error('qty.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="name">Amount<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount.{{ $value }}"  required />
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
                      
                    @endforeach
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
                            <textarea class="form-control" wire:model.defer="description" cols="30" rows="4"></textarea>
                            @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                            <button type="submit" class="btn bg-success btn-wide btn-rounded" {{$items == False ? "disabled" : ""}}><i class="fa fa-save"></i>Save</button>
                        </div>
                        <!-- /.btn-group -->
                    </div>
                </form>
                </div>
            </div>
        </div>
        <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="requisitionEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
            <div class="modal-dialog  mw-100 w-50" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Edit Requisition <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                    </div>
                    <form wire:submit.prevent="update()" >
                    <div class="modal-body">
                        <div class="row">
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
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="name">Subject</label>
                                    <input type="text" min="1" class="form-control" wire:model.debounce.300ms="subject" placeholder="Enter Requisition Subject" />
                                    @error('subject') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="name">Date<span class="required" style="color: red">*</span></label>
                                    <input type="date" min="1" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter Date" required />
                                    @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="country">Currencies<span class="required" style="color: red">*</span></label>
                                   <select wire:model.debounce.300ms="currency_id" class="form-control" required >
                                       <option value="">Select Currency</option>
                                       @foreach ($currencies as $currency)
                                       <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                       @endforeach
                                   </select>
                                    @error('currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                                @if (!is_null($currency_id))
                                @if ($company)
                                    @if ($currency_id != $company->currency_id)
                                    <div class="form-group">
                                        <label for="customer">Conversion Rate</label>
                                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="exchange_rate"  placeholder="Exchange Rate {{$selected_currency ? "From ".$selected_currency->name : ""}} {{$company->currency ? "To ".$company->currency->name : ""}}" >
                                        @error('exchange_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                        <small style="color: green">{{$selected_currency ? " 1 ".$selected_currency->name." is how much in" : ""}} {{$company->currency ? $company->currency->name." ?" : ""}}</small>
                                        <small>{{$exchange_amount ? "The fuel converted amount is: ".$exchange_amount : ""}}</small> 
                                    </div> 
                                    @endif
                                @endif
                            @endif
                            </div>
                            
                        </div>
                      <div class="form-group" >
                            <label for="name">Attach requisition to a</label>
                           
                            <label class="radio-inline">
                                <input type="radio" wire:model.debounce.300ms="requisition_for" value="Booking" name="optradio">Garage Booking
                            </label>
                            <label class="radio-inline">
                                <input type="radio" wire:model.debounce.300ms="requisition_for" value="Purchase" name="optradio">Purchase Order
                            </label>
                             <label class="radio-inline">
                                <input type="radio" wire:model.debounce.300ms="requisition_for" value="Trip" name="optradio" >Trip
                            </label>
                        </div>
                        @if (isset($requisition_for))
                            @if ($requisition_for == "Trip")
                                <div class="form-group">
                                    <label for="country">Trips</label>
                                <select wire:model.debounce.300ms="trip_id" class="form-control" >
                                    <option value="">Select Trip</option>
                                    @if (isset($trips))
                                        @foreach ($trips as $trip)
                                        <option value="{{ $trip->id }}">{{ $trip->trip_number }} | {{ $trip->horse ? $trip->horse->registration_number : "" }}
                                            @if ($trip->driver)
                                            {{ $trip->driver->employee ? $trip->driver->employee->name : "" }} {{ $trip->driver->employee ? $trip->driver->employee->surname : "" }}
                                            @endif
                                            | {{ $trip->customer ? $trip->customer->name : "" }} | {{ $trip->loading_point ? $trip->loading_point->name : "" }} - {{ $trip->offloading_point ? $trip->offloading_point->name : "" }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                    @error('trip_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            @elseif($requisition_for == "Booking")
                                <div class="form-group">
                                    <label for="country">Garage Bookings</label>
                                <select wire:model.debounce.300ms="booking_id" class="form-control" >
                                    <option value="">Select Booking</option>
                                    @foreach ($bookings as $booking)
                                        <option value="{{ $booking->id }}">{{ $booking->booking_number }} | 
                                            {{ $booking->service_type ? $booking->service_type->name : "" }} |
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
                                    @error('booking_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                                 @elseif($requisition_for == "Purchase")
                                <div class="form-group">
                                        <label for="country">Purchase Orders</label>
                                        <input type="text" wire:model.debounce.300ms="searchPurchase" placeholder="Search with booking#, horse, vehicle, trailer reg#..." class="form-control">
                                        <select wire:model.debounce.300ms="purchase_id" class="form-control" size="8">
                                            <option value="">Select Booking</option>
                                            @foreach ($purchases as $purchase)
                                                <option value="{{ $purchase->id }}">{{ $purchase->purchase_number }} | 
                                                     {{ $purchase->date}} |
                                                    {{ $purchase->vendor ? $purchase->vendor->name : "" }} |
                                                    {{ $purchase->currency ? $purchase->currency->name : "" }} {{ $purchase->currency ? $purchase->currency->symbol : "" }}{{ number_format($purchase->total ? $purchase->total : 0,2)}}
                                                </option>
                                            @endforeach
                                            
                                        </select>
                                        @error('purchase_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            @endif
                        @endif
                        
                        <div class="form-group">
                            <label for="name">Notes<span class="required" style="color: red">*</span></label>
                            <textarea class="form-control" wire:model.debounce.300ms="description" cols="30" rows="4"></textarea>
                            @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
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


          <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="product_serviceModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog  mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> New Product / Service<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="storeItem()" >
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
                                <option value="{{$tax->id}}">{{$tax->abbreviation}} {{$tax->rate ? $tax->rate."%" : ""}}</option> 
                                @endforeach
                            </select>
                            <small><a href="{{ route('taxes.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Tax</a></small> 
                        @error('tax_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Save</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>



    </div>
