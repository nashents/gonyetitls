<div>
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
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                            <div class="panel-title">
                                <div class="row">
                                
                                <div class="col-lg-3">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            Filter By
                                        </span>
                                        <select wire:model.debounce.300ms="purchase_filter" class="form-control" aria-label="..." >
                                            <option value="created_at">Purchase Order Created At</option>
                                            <option value="date">Purchase Order Date</option>
                                        </select>
                                    </div>
                                    <!-- /input-group -->
                                </div>
                               
                                <div class="col-lg-2" style=" margin-left:-15px;">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            From
                                        </span>
                                        <input type="date" wire:model.debounce.300ms="from" class="form-control" aria-label="...">
                                    </div>
                                    <!-- /input-group -->
                                </div>
                                <div class="col-lg-2" >
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            To
                                        </span>
                                        <input type="date" wire:model.debounce.300ms="to" class="form-control" aria-label="...">
                                    </div>
                                    <!-- /input-group -->
                                </div>
                          
                              
                               
                                <!-- /input-group -->
                            </div>
                          
                           
                            </div>
                            <div class="panel-title" >
                                <a href="#"  data-toggle="modal" data-target="#purchaseModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Purchase Order</a>
                                <a href="#" wire:click.prevent="exportPurchasesExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                <a href="#" wire:click.prevent="exportPurchasesCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                                <a href="#" wire:click.prevent="exportPurchasesPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a> 
                            </div>
                            <br>
                            <div class="col-md-3" style="float: right; padding-right:0px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search purchase orders...">
                                </div>
                            </div>
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th class="th-sm">Purchase#
                                        </th>
                                        <th class="th-sm">CreatedBy
                                        </th>
                                        <th class="th-sm">
                                            Date
                                           <hr style="margin-top:2px; margin-bottom:2px">
                                            Expiry
                                        </th>
                                      
                                        <th class="th-sm">Vendor
                                        </th>
                                        <th class="th-sm">Summary
                                        </th>
                                        <th class="th-sm">Ccy
                                        </th>
                                        <th class="th-sm">Total
                                        </th>
                                        <th class="th-sm">Paid
                                        </th>
                                        <th class="th-sm">Item(s)
                                        </th>
                                        <th class="th-sm">Received
                                        </th>
                                        <th class="th-sm">Sent
                                        </th>
                                        <th class="th-sm">Auth
                                        </th>
                                        <th class="th-sm">Action
                                        </th>
                                      </tr>
                                </thead>
                                @if (isset($purchases))
                                <tbody>
                                    @forelse ($purchases as $purchase)
                                    <tr>
                                        <td>
                                            {{$purchase->purchase_number}}
                                            @if ($this->sageEnabled)
                                                @php $sm = $purchase->sageMapping; $ss = optional($sm)->sync_status; $approved = $purchase->authorization === 'approved'; @endphp
                                                <br>
                                                <small class="badge bg-{{ $sm ? ($ss === 'synced' ? 'success' : ($ss === 'failed' ? 'danger' : ($ss === 'requires_attention' ? 'warning' : 'secondary'))) : 'secondary' }}"
                                                       title="{{ optional($sm)->last_error ?? '' }}">Sage PO: {{ $sm ? ucwords(str_replace('_',' ', $ss)) : 'Not synced' }}</small>
                                                @if ($approved)
                                                    @if ($ss === 'synced')
                                                        <a href="#" wire:click.prevent="syncPurchaseToSage({{ $purchase->id }})" wire:loading.attr="disabled" title="Re-sync to Sage"><i class="fa fa-refresh"></i></a>
                                                    @elseif (in_array($ss, ['failed','requires_attention']))
                                                        <a href="#" wire:click.prevent="syncPurchaseToSage({{ $purchase->id }})" wire:loading.attr="disabled" style="color:#d9534f" title="Retry Sage sync"><i class="fa fa-refresh"></i> Retry</a>
                                                    @else
                                                        <a href="#" wire:click.prevent="syncPurchaseToSage({{ $purchase->id }})" wire:loading.attr="disabled" title="Sync to Sage"><i class="fa fa-cloud-upload"></i> Sync</a>
                                                    @endif
                                                @endif
                                            @endif
                                            @if ($purchase->employee)
                                                <br>
                                                <small><strong>RequestedBy:</strong> {{$purchase->employee ? $purchase->employee->name : ""}} {{$purchase->employee ? $purchase->employee->surname : ""}}</small>
                                            @endif
                                            @if ($purchase->booking)

                                                <small>
                                                    <strong>Booking#:</strong>{{$purchase->booking->booking_number}} Date: {{$purchase->booking->in_date}} JobType: {{$purchase->booking->service_type ? $purchase->booking->service_type->name : ""}}
                                                    @if ($purchase->booking->horse)
                                                        {{ $purchase->booking->horse ? $purchase->booking->horse->registration_number : "" }} {{ $purchase->booking->horse->fleet_number ? "(".$purchase->booking->horse->fleet_number.")" : "" }}
                                                    @elseif ($purchase->booking->vehicle)
                                                        {{ $purchase->booking->vehicle ? $purchase->booking->vehicle->registration_number : "" }} {{ $purchase->booking->vehicle->fleet_number ? "(".$purchase->booking->vehicle->fleet_number.")" : "" }}
                                                    @elseif ($purchase->booking->trailer)
                                                        {{ $purchase->booking->trailer ? $purchase->booking->trailer->registration_number : "" }} {{ $purchase->booking->trailer->fleet_number ? "(".$purchase->booking->trailer->fleet_number.")" : "" }}
                                                    @endif
                                                </small>
                                                <br>
                                            @endif
                                            @if (!is_null($purchase->requisition_id))
                                                <small>
                                                    <strong>Requsition: </strong> {{$this->purchaseRequisition($purchase->requisition_id)}}
                                                </small>
                                                <br>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($purchase->user)
                                                 {{$purchase->user ? $purchase->user->name : ""}} {{$purchase->user ? $purchase->user->surname : ""}}
                                                @if ($purchase->user->employee)
                                                    <br>
                                                    <small><strong>{{$purchase->user->employee->departments ? $purchase->user->employee->departments->first()->name : ""}}</strong></small>
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            {{$purchase->date}}
                                           <hr style="margin-top:2px; margin-bottom:2px">
                                            <span class="badge bg-{{Carbon\Carbon::now() < $purchase->expiry ? 'success' : 'danger' }}">{{Carbon\Carbon::parse($purchase->expiry)->format('d-m-Y')}}</span>
                                        </td>

                                        <td>{{$purchase->vendor ? $purchase->vendor->name : ""}}</td>
                                        <td>
                                           
                                            @foreach ($purchase->purchase_products as $purchase_product )
                                                @if ($purchase_product->product)
                                                        {{$purchase_product->product ? $purchase_product->product->name : ""}} {{$purchase_product->product->brand ? $purchase_product->product->brand->name : ""}}
                                                         {{$purchase_product->qty ? "(".$purchase_product->qty.")" : ""}}
                                                            @if ($purchase_product->amount)
                                                                @ {{ $purchase_product->currency ? $purchase_product->currency->name : ""}} {{ $purchase_product->currency ? $purchase_product->currency->symbol : ""}}{{ number_format($purchase_product->amount,2)}}
                                                            @endif
                                                         {{$purchase_product->payment_method ? $purchase_product->payment_method->name : ""}}@if (!$loop->last),@endif  <br>
                                                @endif
                                            @endforeach
                                            
                                            @if ($purchase->description)
                                                <br>
                                                <i><strong>Notes: </strong> {{$purchase->description}}</i>
                                            @endif
                                        </td>
                                        <td>{{$purchase->currency ? $purchase->currency->name : ""}}</td>
                                        <td>
                                            {{$purchase->currency ? $purchase->currency->symbol : ""}}{{number_format($purchase->total ? $purchase->total : 0,2)}}
                                            @if (Auth::user()->employee->company->currency_id != $purchase->currency_id)
                                            <br>
                                            <small>
                                                <strong>Exc Rate:</strong> {{$purchase->exchange_rate}} <br>
                                                <strong>Exc Total:</strong> {{Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->name : ""}} {{Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->symbol : ""}}{{number_format($purchase->exchange_amount,2)}}
                                            </small>
                                        @endif
                                        </td>
                                        <td>
                                            @if ($purchase->bill)
                                                 {{$purchase->bill->currency ? $purchase->bill->currency->symbol : ""}}{{number_format($purchase->bill->payments->sum('amount'),2)}}
                                            @else
                                            {{$purchase->currency ? $purchase->currency->symbol : ""}}0.00
                                            @endif
                                        </td>
                                        <td>{{ $purchase->purchase_products->sum('qty') }}</td>
                                        <td>

                                            @if ($department == "tyre")
                                                {{$purchase->tyres->count()}}
                                            @elseif($department == "inventory")
                                                {{$purchase->inventories->count()}}
                                            @elseif($department == "asset")
                                                {{$purchase->assets->count()}}
                                            @endif
                                        </td>
                                        <td>
                                           <span class="badge bg-{{$purchase->is_sent == True ? 'success' :  'primary' }}">{{ $purchase->is_sent == True ? "Sent" : "Not Sent" }}</span>
                                        </td>
                                        <td>
                                            @if(blank($purchase->authorization))
                                                <span class="badge bg-secondary">&nbsp;</span>
                                            @else
                                                <span class="badge bg-{{$purchase->authorization === 'approved' ? 'success' : ($purchase->authorization === 'rejected' ? 'danger' : 'warning') }}">
                                                    {{ $purchase->authorization }}
                                                </span>
                                            @endif 
                                            @php
                                                $user = App\Models\User::find($purchase->authorized_by_id);
                                            @endphp
                                            @if ($user)
                                                <br>
                                               <small  style="background-color: orange"><strong>AuthBy: </strong>{{$user->name}} {{$user->surname}}</small>  
                                            @endif
                                            @if ($purchase->authorization_date)
                                                <br>
                                               <small  style="background-color: orange"><strong>Date: </strong> {{$purchase->authorization_date}}</small>  
                                            @endif
                                            @if ($purchase->authorization_comments)
                                            <br>
                                            <small style="background-color: orange"><strong>Comments: </strong> {{$purchase->authorization_comments}}</small>  
                                            @endif 
                                        </td>
                                        <td class="w-10 line-height-35 table-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="{{route('purchases.show',$purchase->id)}}" ><i class="fa fa-eye color-default"></i> View</a></li>
                                                    <li>
                                                        <a href="#" wire:click.prevent="updateStar({{ $purchase->id }})">
                                                            @if ($purchase->star)
                                                                <i class="fa fa-star color-warning"></i> Unstar
                                                            @else
                                                                <i class="fa fa-star color-default"></i> Star
                                                            @endif
                                                        </a>
                                                    </li>
                                                    @if ($purchase->authorization == "approved" || $purchase->authorization == "rejected")
                                                        <li><a href="{{route('purchases.preview',$purchase->id)}}"  ><i class="fas fa-file-invoice color-primary"></i> Preview</a></li>
                                                        @if ($purchase->is_sent == False)
                                                            <li><a href="" wire:click.prevent="markSent({{$purchase->id}})"  ><i class="fas fa-check color-secondary"></i> Mark as sent</a></li>
                                                        @endif
                                                    @endif
                                                    @if ($purchase->authorization == "pending" || Auth::user()->is_admin() )
                                                        <li><a href="#"  wire:click="edit({{$purchase->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                    @endif
                                                    @if ($purchase->authorization == "pending" || Auth::user()->is_admin() || Auth::user()->id == $purchase->authorized_by_id)
                                                        <li><a href="#" data-toggle="modal" data-target="#purchaseDeleteModal{{ $purchase->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                                    @endif
                                                </ul>
                                            </div>
                                            @include('purchases.delete')
                                    </td>
                                      </tr>
                                  @empty
                                  <tr>
                                    <td colspan="13">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Purchase Orders Found ....
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
                                    @if (isset($purchases))
                                        {{ $purchases->links() }} 
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal fade" id="showModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-default">
                <div class="modal-body">
                   <center><strong>Mark this PO as star</strong></center>
                </div>
                <form wire:submit.prevent="removePurchaseItem()" >
                <div class="modal-footer no-border">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-black btn-wide btn-rounded" ><i class="fa fa-refresh"></i>Update</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>


    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="purchaseModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-70" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Create Purchase Order <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    @if (!is_null($department))
                    @if ($department == "tyre" || $department == "inventory" )
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="Product">Vendors<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="vendor_id" class="form-control" required>
                                        <option value="">Select Selected Vendor</option>
                                        @foreach ($vendors as $vendor)
                                        <option value="{{$vendor->id}}">{{$vendor->name}}</option>
                                    @endforeach
                                    </select>
                                    <small>  <a href="#" data-toggle="modal" data-target="#vendorModal" ><i class="fa fa-plus-square-o"></i> New Vendor</a></small><a href="#" wire:click.prevent="refresh('vendors')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
                                <br> 
                                    <br>
                                    <small style="color: green">Select the prefered vendor of choice for the purchase order.</small>
                                    @error('vendor_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="name">Date<span class="required" style="color: red">*</span></label>
                                    <input type="date" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter Purchase Order Date" required >
                                    @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                             <div class="col-md-3">
                                <label for="title">Requested By</label>
                                <select wire:model.debounce.300ms="employee_id" class="form-control">
                                    <option value="">Select Employee</option>
                                    @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}">
                                       {{$employee->name}} {{$employee->surname}}
                                    </option>                                      
                                    @endforeach
                                </select>
                                @error('employee_id') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                            <div class="col-md-3">
                                <div class="form-group" >
                                    <label for="name">Attach PO to a</label>
                                    <label class="radio-inline">
                                        <input type="radio" wire:model.debounce.300ms="attach_to" value="Booking" name="optradio">Booking
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" wire:model.debounce.300ms="attach_to" value="Requisition" name="optradio">Requisition
                                    </label>
                                </div>
                                @if (!is_null($attach_to))
                                    <div class="form-group">
                                        @if ($attach_to == "Booking")
                                            <label for="title">Bookings</label>
                                            <select wire:model.debounce.300ms="booking_id" class="form-control">
                                                <option value="">Select Booking</option>
                                                @foreach ($bookings as $booking)
                                                <option value="{{ $booking->id }}">
                                                    {{ $booking->booking_number }} 
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
                                            @error('booking_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                        @elseif ($attach_to == "Requisition")
                                            <label for="title">Requisitions</label>
                                            <select wire:model.debounce.300ms="requisition_id" class="form-control">
                                                <option value="">Select Requisition</option>
                                                @foreach ($requisitions as $requisition)
                                                <option value="{{ $requisition->id }}">
                                                    Req#: {{ $requisition->requisition_number }} 
                                                    Date: {{ $requisition->date}} 
                                                    ReqBy: {{ucfirst($requisition->employee ? $requisition->employee->name : "")}} {{ucfirst($requisition->employee ? $requisition->employee->surname : "")}}
                                                </option>                                      
                                                @endforeach
                                            </select>
                                            @error('requisition_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else  
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="Product">Vendors<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="vendor_id" class="form-control" required>
                                    <option value="">Select Selected Vendor</option>
                                    @foreach ($vendors as $vendor)
                                    <option value="{{$vendor->id}}">{{$vendor->name}}</option>
                                @endforeach
                                </select>
                                <small>  <a href="#" data-toggle="modal" data-target="#vendorModal" ><i class="fa fa-plus-square-o"></i> New Vendor</a></small><a href="#" wire:click.prevent="refresh('vendors')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
                                <br>
                                <small style="color: green">Select the prefered vendor of choice for the purchase order.</small>
                                @error('vendor_id') <span class="text-danger error">{{ $message }}</span>@enderror
                            
                            </div>
                        </div>
                        <div class="col-md-4">
                                <label for="title">Requested By</label>
                                <select wire:model.debounce.300ms="employee_id" class="form-control">
                                    <option value="">Select Employee</option>
                                    @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}">
                                       {{$employee->name}} {{$employee->surname}}
                                    </option>                                      
                                    @endforeach
                                </select>
                                @error('employee_id') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="name">Date<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter Purchase Order Date" required >
                                @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    @endif
                    @endif
              
                  
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="title">Currencies<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="selectedCurrency" class="form-control" required>
                                    <option value="">Select Currency</option>
                                    @foreach ($currencies as $currency)
                                    <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>                                      
                                    @endforeach
                                </select>
                                @error('selectedCurrency') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                             @if (!is_null($selectedCurrency))
                                @if ($company)
                                    @if ($selectedCurrency != $company->currency_id)
                                    <div class="form-group">
                                        <label for="customer">Conversion Rate<span class="required" style="color: red">*</span></label>
                                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="exchange_rate"  placeholder="Exchange Rate {{$selected_currency ? "From ".$selected_currency->name : ""}} {{$company->currency ? "To ".$company->currency->name : ""}}" required>
                                        @error('exchange_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                        <small style="color: green">{{$selected_currency ? " 1 ".$selected_currency->name." is how much in" : ""}} {{$company->currency ? $company->currency->name." ?" : ""}}</small>
                                        <small>{{$exchange_amount ? "The converted amount is: ".$exchange_amount : ""}}</small> <br>
                                    </div> 
                                    @endif
                                @endif
                            @endif
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="Product">Expense Category<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="selectedAccount" class="form-control" required>
                                    <option value="">Select Category</option>
                                    @foreach ($expense_accounts as $account)
                                      <option value="{{$account->id}}">{{$account->name}}</option>
                                    @endforeach
                                </select>
                                <small>  <a href="{{ route('accounts.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Category</a></small><a href="#" wire:click.prevent="refresh('accounts')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
                                @error('selectedAccount') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                       </div>
                  
                       <div class="form-group">
                        <label for="name">Additional Notes</label>
                       <textarea class="form-control" wire:model.debounce.300ms="description" cols="30" rows="2" placeholder="Enter purchase order notes"></textarea>
                        @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                      
                    <h5 class="underline mt-n">Select products</h5>
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="title">Products<span class="required" style="color: red">*</span></label>
                                <div class="mb-10">
                                   <input type="checkbox" wire:model.debounce.300ms="all_products"   class="line-style" />
                                    <label for="one" class="radio-label">Show all products</label>
                                   @error('all_products') <span class="text-danger error">{{ $message }}</span>@enderror
                               </div>
                                <select wire:model.debounce.300ms="selectedProduct.0" class="form-control" required>
                                    <option value="">Select Product</option>
                                    @foreach ($products as $product)
                                    <option value="{{$product->id}}"> {{$product->name}} {{$product->brand ? $product->brand->name : ""}} {{$product->identification_number}} ({{$product->product_number}})</option>
                                    @endforeach
                                </select>
                                @if ($department == "tyre")
                                    <small><a href="{{ route('tyre_products.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Product</a></small><a href="#" wire:click.prevent="refresh('products')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
                                @elseif ($department == "inventory")
                                    <small><a href="{{ route('inventory_products.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Product</a></small><a href="#" wire:click.prevent="refresh('products')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
                                @elseif ($department == "asset")
                                    <small><a href="{{ route('products.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Product</a></small><a href="#" wire:click.prevent="refresh('products')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
                                @endif
                                @error('selectedProduct.0') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
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
                                <label for="Product">Qty<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" min="1" class="form-control" wire:model.debounce.300ms="qty.0"  required>
                                @error('qty.0') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="Product">Rate<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" min="0.01" class="form-control" wire:model.debounce.300ms="amount.0"   required>
                                @error('amount.0') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="subheading">Taxes</label>
                                <select wire:model.debounce.300ms="selectedTax.{{$value}}"  class="form-control">
                                        <option value="">Select Tax Category</option>
                                        @foreach ($tax_accounts as $tax)
                                           <option value="{{$tax->id}}">{{$tax->abbreviation}}</option> 
                                        @endforeach
                                    </select>
                                    <small><a href="{{ route('taxes.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Tax</a></small> 
                                @error('selectedTax.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>      
                       
                    </div>

                        @foreach ($inputs as $key => $value)
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="title">Products<span class="required" style="color: red">*</span></label>
                                     <div class="mb-10">
                                        <input type="checkbox" wire:model.debounce.300ms="all_products"   class="line-style" />
                                        <label for="one" class="radio-label">Show all products</label>
                                        @error('all_products') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                    <select wire:model.debounce.300ms="selectedProduct.{{$value}}" class="form-control" required>
                                        <option value="">Select Product</option>
                                        @foreach ($products as $product)
                                        <option value="{{$product->id}}"> {{$product->name}} {{$product->brand ? $product->brand->name : ""}} {{$product->identification_number}} ({{$product->product_number}})</option>
                                        @endforeach
                                    </select>
                                    @error('selectedProduct.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
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
                                    <label for="Product">Qty<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" min="1" class="form-control" wire:model.debounce.300ms="qty.{{$value}}"  required>
                                    @error('qty.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="Product">Rate<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" min="0.01" class="form-control" wire:model.debounce.300ms="amount.{{$value}}"   required>
                                    @error('amount.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="subheading">Taxes</label>
                                    <select wire:model.debounce.300ms="selectedTax.{{$value}}"  class="form-control">
                                            <option value="">Select Tax Category</option>
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
                                    <button class="btn btn-danger btn-rounded xs" style="margin-top:23px"  wire:click.prevent="remove({{$key}})"> <i class="fa fa-times"></i></button>
                                </div>
                            </div>
                            <!-- /.col-md-6 -->
                        </div>
                        @endforeach
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i> Product</button>
                                </div>
                            </div>
                        </div>

                        
                        <h5 class="underline mt-n">Upload Purchase Order Attachments</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="title">Titles</label>
                                    <select wire:model.debounce.300ms="title.0" class="form-control" >
                                        <option value="">Select Title</option>
                                        <option value="Invoice">Invoice</option>
                                        <option value="Quotation 1">Quotation 1</option>
                                        <option value="Quotation 2">Quotation 2</option>
                                        <option value="Quotation 3">Quotation 3</option>
                                        <option value="Receipt">Receipt</option>
                                    </select>
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
                                    <label for="title">Titles</label>
                                    <select wire:model.debounce.300ms="title.{{$value}}" class="form-control">
                                        <option value="">Select Title</option>
                                        <option value="Invoice">Invoice</option>
                                        <option value="Quotation 1">Quotation 1</option>
                                        <option value="Quotation 2">Quotation 2</option>
                                        <option value="Quotation 3">Quotation 3</option>
                                        <option value="Receipt">Receipt</option>
                                    </select>
                                    @error('title.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="file">File</label>
                                    <input type="file" class="form-control" wire:model.debounce.300ms="file.{{$value}}"  placeholder="Upload File ">
                                    @error('file.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-3">
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="purchaseEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-70" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit Purchase Order <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                    <div class="modal-body">
                        @if (!is_null($department))
                        @if ($department == "tyre" || $department == "inventory" )
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="Product">Vendors<span class="required" style="color: red">*</span></label>
                                        <select wire:model.debounce.300ms="vendor_id" class="form-control" required>
                                            <option value="">Select Selected Vendor</option>
                                            @foreach ($vendors as $vendor)
                                            <option value="{{$vendor->id}}">{{$vendor->name}}</option>
                                        @endforeach
                                        </select>
                                        <small>  <a href="#" data-toggle="modal" data-target="#vendorModal" ><i class="fa fa-plus-square-o"></i> New Vendor</a></small> 
                                        <br>
                                        <small style="color: green">Select the prefered vendor of choice for the purchase order.</small>
                                        @error('vendor_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                    
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="name">Date<span class="required" style="color: red">*</span></label>
                                        <input type="date" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter Purchase Order Date" required >
                                        @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                <label for="title">Requested By</label>
                                <select wire:model.debounce.300ms="employee_id" class="form-control">
                                    <option value="">Select Employee</option>
                                    @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}">
                                       {{$employee->name}} {{$employee->surname}}
                                    </option>                                      
                                    @endforeach
                                </select>
                                @error('employee_id') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                               
                                <div class="col-md-3">
                                <div class="form-group" >
                                    <label for="name">Attach PO to a</label>
                                    <label class="radio-inline">
                                        <input type="radio" wire:model.debounce.300ms="attach_to" value="Booking" name="optradio">Booking
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" wire:model.debounce.300ms="attach_to" value="Requisition" name="optradio">Requisition
                                    </label>
                                </div>
                                @if (!is_null($attach_to))
                                    <div class="form-group">
                                        @if ($attach_to == "Booking")
                                            <label for="title">Bookings</label>
                                            <select wire:model.debounce.300ms="booking_id" class="form-control">
                                                <option value="">Select Booking</option>
                                                @foreach ($bookings as $booking)
                                                <option value="{{ $booking->id }}">
                                                    {{ $booking->booking_number }} 
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
                                            @error('booking_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                        @elseif ($attach_to == "Requisition")
                                            <label for="title">Requisitions</label>
                                            <select wire:model.debounce.300ms="requisition_id" class="form-control">
                                                <option value="">Select Rquisition</option>
                                                @foreach ($requisitions as $requisition)
                                                <option value="{{ $requisition->id }}">
                                                    Req#: {{ $requisition->requisition_number }} 
                                                    Date: {{ $requisition->date}} 
                                                    ReqBy: {{ucfirst($requisition->employee ? $requisition->employee->name : "")}} {{ucfirst($requisition->employee ? $requisition->employee->surname : "")}}
                                                </option>                                      
                                                @endforeach
                                            </select>
                                            @error('requisition_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                        @endif
                                    </div>
                                @endif
                            </div>
                            </div>
                        @else  
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="Product">Vendors<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="vendor_id" class="form-control" required>
                                        <option value="">Select Selected Vendor</option>
                                        @foreach ($vendors as $vendor)
                                        <option value="{{$vendor->id}}">{{$vendor->name}}</option>
                                    @endforeach
                                    </select>
                                    <small>  <a href="#" data-toggle="modal" data-target="#vendorModal" ><i class="fa fa-plus-square-o"></i> New Vendor</a></small> 
                                    <br>
                                    <small style="color: green">Select the prefered vendor of choice for the purchase order.</small>
                                    @error('vendor_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="name">Date<span class="required" style="color: red">*</span></label>
                                    <input type="date" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter Purchase Order Date" required >
                                    @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        @endif
                        @endif
                      
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Currencies<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="selectedCurrency" class="form-control" required>
                                        <option value="">Select Currency</option>
                                        @foreach ($currencies as $currency)
                                        <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>                                      
                                        @endforeach
                                    </select>
                                    @error('selectedCurrency') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                                 @if (!is_null($selectedCurrency))
                                    @if ($company)
                                        @if ($selectedCurrency != $company->currency_id)
                                        <div class="form-group">
                                            <label for="customer">Conversion Rate<span class="required" style="color: red">*</span></label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="exchange_rate"  placeholder="Exchange Rate {{$selected_currency ? "From ".$selected_currency->name : ""}} {{$company->currency ? "To ".$company->currency->name : ""}}" required>
                                            @error('exchange_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                            <small style="color: green">{{$selected_currency ? " 1 ".$selected_currency->name." is how much in" : ""}} {{$company->currency ? $company->currency->name." ?" : ""}}</small>
                                            <small>{{$exchange_amount ? "The converted amount is: ".$exchange_amount : ""}}</small> <br>
                                        </div> 
                                        @endif
                                    @endif
                                @endif
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="Product">Expense Category<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="selectedAccount" class="form-control" required>
                                        <option value="">Select Category</option>
                                        @foreach ($expense_accounts as $account)
                                          <option value="{{$account->id}}">{{$account->name}}</option>
                                        @endforeach
                                    </select>
                                    <small>  <a href="{{ route('accounts.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Category</a></small> 
                                    @error('selectedAccount') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                           </div>
                      
                           <div class="form-group">
                            <label for="name">Additional Notes</label>
                           <textarea class="form-control" wire:model.debounce.300ms="description" cols="30" rows="2" placeholder="Enter purchase order notes"></textarea>
                            @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                          
                        
                        <h5 class="underline mt-n">Select products</h5>
                        @foreach ($purchase_order_products as $key => $value)
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="title">Products<span class="required" style="color: red">*</span></label>
                                    <div class="mb-10">
                                        <input type="checkbox" wire:model.debounce.300ms="all_products"   class="line-style" />
                                            <label for="one" class="radio-label">Show all products</label>
                                        @error('all_products') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                    <select wire:model.debounce.300ms="selectedCurrentProduct.{{$key}}" class="form-control" required>
                                        <option value="">Select Product</option>
                                        @foreach ($products as $product)
                                        <option value="{{$product->id}}"> {{$product->name}} {{$product->brand ? $product->brand->name : ""}} {{$product->identification_number}} ({{$product->product_number}})</option>
                                        @endforeach
                                    </select>
                                    <small>  <a href="{{ route('products.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Product</a></small> 
                                    @error('selectedCurrentProduct.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                <label for="country">Payment Methods</label>
                                <select wire:model.debounce.300ms="current_payment_method_id.{{$key}}"  class="form-control"  >
                                    <option value="">Select Payment Method</option>
                                    @foreach ($payment_methods as $payment_method)
                                    <option value="{{ $payment_method->id }}">{{ $payment_method->name }}</option>
                                    @endforeach
                                </select>
                                @error('current_payment_method_id.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label for="Product">Qty<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" min="1" class="form-control" wire:model.debounce.300ms="current_qty.{{$key}}"  required>
                                    @error('current_qty.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="Product">Rate<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" min="0.01" class="form-control" wire:model.debounce.300ms="current_amount.{{$key}}"   required>
                                    @error('current_amount.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="subheading">Taxes</label>
                                    <select wire:model.debounce.300ms="selectedCurrentTax.{{$key}}"  class="form-control">
                                        <option value="">Select Tax</option>
                                            @foreach ($tax_accounts as $tax)
                                               <option value="{{$tax->id}}">{{$tax->abbreviation}} </option> 
                                            @endforeach
                                        </select>
                                        <small><a href="{{ route('taxes.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Tax</a></small> 
                                    @error('selectedCurrentTax.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>    
                            <div class="col-md-1">
                                <div class="form-group" style="margin-top: 29px; ">
                                    <a href="#" wire:click="removeShow({{ $value->id }})"  ><i class="fa fa-trash color-danger"></i></a>
                                </div>
                            </div>   
                           
                        </div>
                        @endforeach
                  
    
                            @foreach ($inputs as $key => $value)
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="title">Products<span class="required" style="color: red">*</span></label>
                                        <div class="mb-10">
                                            <input type="checkbox" wire:model.debounce.300ms="all_products"   class="line-style" />
                                                <label for="one" class="radio-label">Show all products</label>
                                            @error('all_products') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                        <select wire:model.debounce.300ms="selectedProduct.{{$value}}" class="form-control" required>
                                            <option value="">Select Product</option>
                                            @foreach ($products as $product)
                                            <option value="{{$product->id}}">{{$product->name}} {{$product->brand ? $product->brand->name : ""}} {{$product->identification_number}} ({{$product->product_number}})</option>
                                            @endforeach
                                        </select>
                                        @error('selectedProduct.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
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
                                        <label for="Product">Qty<span class="required" style="color: red">*</span></label>
                                        <input type="number" step="any" min="1" class="form-control" wire:model.debounce.300ms="qty.{{$value}}"  required>
                                        @error('qty.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="Product">Rate<span class="required" style="color: red">*</span></label>
                                        <input type="number" step="any" min="0.01" class="form-control" wire:model.debounce.300ms="amount.{{$value}}"   required>
                                        @error('amount.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="subheading">Taxes</label>
                                        <select wire:model.debounce.300ms="selectedTax.{{$value}}"  class="form-control">
                                            <option value="">Select Tax</option>
                                                @foreach ($tax_accounts as $tax)
                                                   <option value="{{$tax->id}}">{{$tax->abbreviation}} </option> 
                                                @endforeach
                                            </select>
                                            <small><a href="{{ route('taxes.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Tax</a></small> 
                                        @error('selectedTax.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>      
                              
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label for=""></label>
                                        <button class="btn btn-danger btn-rounded xs" style="margin-top:23px"  wire:click.prevent="remove({{$key}})"> <i class="fa fa-times"></i></button>
                                    </div>
                                </div>
                                <!-- /.col-md-6 -->
                            </div>
                            @endforeach
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i> Product</button>
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal fade" id="removeModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                   <center> <strong>Are you sure you want to delete this Purchase Order Item</strong> </center>
                </div>
                <form wire:submit.prevent="removePurchaseItem()" >
                <div class="modal-footer no-border">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-black btn-wide btn-rounded" ><i class="fa fa-trash"></i>Delete</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>


    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="vendorModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Vendor <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="storeVendor()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Name<span class="required" style="color: red">*</span></label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Name of a business or person." required />
                        @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                  
                   
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" wire:model.debounce.300ms="email" placeholder="Enter Email"  />
                                @error('email') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phonenumber">Phonenumber</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="phonenumber" placeholder="Enter Phonenumber"  />
                                @error('phonenumber') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                       
                    </div>
   
                    <h5 class="underline mt-30">Billing Details</h5>
                   
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="country">Currency</label>
                               <select class="form-control" wire:model.debounce.300ms="currency_id">
                                    <option value="">Select Currency</option>
                                    @foreach ($currencies as $currency)
                                        <option value="{{ $currency->id }}">{{ $currency->name }}</option>
                                    @endforeach
                               </select>
                                @error('currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="country">Country</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="country" placeholder="Enter Country"  />
                                @error('country') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="city">City</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="city" placeholder="Enter City"  />
                                @error('city') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Suburb</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="suburb" placeholder="Enter Suburb" />
                                @error('suburb') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="street_address">Street Address</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="street_address" placeholder="Enter Street Address" />
                                @error('street_address') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
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

