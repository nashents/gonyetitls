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

                            <div class="panel-title">
                                <div class="row">
                                
                                <div class="col-lg-3">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                  Filter By
                                  </span>
                                  <select wire:model.debounce.300ms="bill_filter" class="form-control" aria-label="..." >
                                    <option value="created_at">Bill Created At</option>
                                    <option value="bill_date">Bill Date</option>
                                </select>
                                    </div>

                                    <!-- /input-group -->
                                </div>

                            
                                <div class="col-lg-2" style="margin-right: 7px; margin-left:-15px;">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                From
                                </span>
                                <input type="date" wire:model.debounce.300ms="from"  class="form-control" aria-label="...">
                                    </div>
                                    <!-- /input-group -->
                                </div>
                                <div class="col-lg-2" style="margin-left: 7px">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                To
                                </span>
                                <input type="date" wire:model.debounce.300ms="to"  class="form-control" aria-label="...">
                                    </div>
                                    <!-- /input-group -->
                                </div>
                          
                               
                               
                                <!-- /input-group -->
                            </div>
                          
                           
                            </div>
                             <div class="panel-title" style="margin-left:-15px;" >
                                <div class="col-lg-3">
                                    <div class="input-group">
                                        <span class="input-group-addon">Tax Status</span>
                                        <select wire:model.debounce.300ms="tax_status" class="form-control" aria-label="..." >
                                          <option value="all">All Bills</option>
                                          <option value="taxed">Taxed Bills</option>
                                          <option value="non-taxed">Non Taxed Bills</option>
                                        </select>
                                    </div>
                                </div>
                               
                               <a href="#" wire:click="exportBillsExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                <a href="#" wire:click="exportBillsCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                                <a href="#" wire:click="exportBillsPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a>

                            </div>
                            <div class="panel-title" style="margin-top:10px; margin-left:-1px">
                                <a href="{{route('bills.create')}}"  class="btn btn-default"><i class="fa fa-plus-square-o"></i>Bill</a>
                                <a href="#" type="button" data-toggle="modal" data-target="#paymentDrawdownModal" class="btn btn-default btn-rounded btn-wide"><i class="fa fa-credit-card"></i>Bulk Bills Payments</a>
                            </div>
                            
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                            <div class="col-md-3" style="float: right; padding-right:0px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search bills...">
                                </div>
                            </div>

                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Bill#
                                    </th>
                                    <th class="th-sm">CreatedBy
                                    </th>
                                    <th class="th-sm">Narration
                                    </th>
                                    <th class="th-sm">Items
                                    </th>
                                    <th class="th-sm">Date
                                    </th>
                                    <th class="th-sm">Ccy
                                    </th>
                                    <th class="th-sm">Subtotal
                                    </th>
                                    <th class="th-sm">Tax
                                    </th>
                                    <th class="th-sm">Total
                                    </th>
                                    <th class="th-sm">Paid
                                    </th>
                                    <th class="th-sm">Due
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Auth
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($bills))
                                <tbody>
                                    @forelse ($bills as $bill)
                                  <tr>
                                    <td>{{$bill->bill_number}}</td>
                                     <td>{{$bill->user ? $bill->user->name : ""}} {{$bill->user ? $bill->user->surname : ""}}</td>
                                    <td>
                                        @if ($bill->transporter)
                                            Transporter | <a href="{{ route('transporters.show',$bill->transporter->id) }}" style="color: blue" target="_blank">{{ $bill->transporter ? $bill->transporter->name  : ""}}</a> 
                                        @elseif($bill->vendor)
                                            Vendor | <a href="{{ route('vendors.show',$bill->vendor->id) }}" style="color: blue" target="_blank">{{ $bill->vendor ? $bill->vendor->name : "" }}</a> 
                                            @if ($bill->horse)
                                                <br>
                                                Horse | <a href="{{route('horses.show', $bill->horse->id)}}" style="color: blue" target="_blank">{{$bill->horse ? $bill->horse->registration_number : ""}} {{$bill->horse->fleet_number ? "(".$bill->horse->fleet_number.")" : ""}} {{$bill->horse->horse_make ? $bill->horse->horse_make->name : ""}} {{$bill->horse->horse_model ? $bill->horse->horse_model->name : ""}}</a> 
                                            @elseif ($bill->vehicle)
                                                <br>
                                                Vehicle | <a href="{{route('vehicles.show', $bill->vehicle->id)}}" style="color: blue" target="_blank">{{$bill->vehicle ? $bill->vehicle->registration_number : ""}} {{$bill->vehicle->fleet_number ? "(".$bill->vehicle->fleet_number.")" : ""}} {{$bill->vehicle->vehicle_make ? $bill->vehicle->vehicle_make->name : ""}} {{$bill->vehicle->vehicle_model ? $bill->vehicle->vehicle_model->name : ""}}</a> 
                                            @elseif ($bill->trailer)
                                                <br>
                                                Trailer | <a href="{{route('trailers.show', $bill->trailer->id)}}" style="color: blue" target="_blank">{{$bill->trailer ? $bill->trailer->registration_number : ""}} {{$bill->trailer->fleet_number ? "(".$bill->trailer->fleet_number.")" : ""}} {{$bill->trailer->make}} {{$bill->trailer->model}}</a> 
                                            @elseif ($bill->driver)
                                                <br>
                                                Driver | <a href="{{route('drivers.show', $bill->driver->id)}}" style="color: blue" target="_blank">{{$bill->driver->employee ? $bill->driver->employee->name : ""}} {{$bill->driver->employee ? $bill->driver->employee->surname : ""}} </a> 
                                            @endif
                                           
                                          
                                        @elseif ( $bill->container && $bill->top_up)
                                            Fuel Topup | <a href="{{ route('containers.show', $bill->container->id) }}" style="color: blue" target="_blank">{{ $bill->container ? $bill->container->name : "" }}</a> 
                                        @elseif ( $bill->retread)
                                            Tyre Retread | <a href="{{ route('retreads.show', $bill->retread->id) }}" style="color: blue" target="_blank">{{ $bill->retread ? $bill->retread->retread_number : "" }}</a> 
                                        @elseif ( $bill->fuel)
                                            @if ($bill->trip)
                                            Trip Expense - Fuel Order | <a href="{{ route('fuels.show', $bill->fuel->id) }}" style="color: blue" target="_blank">{{ $bill->fuel ? $bill->fuel->order_number : "" }}</a> | <a href="{{ route('trips.show', $bill->trip->id) }}" style="color: blue" target="_blank">{{ $bill->trip->trip_number }}</a> 
                                            @else
                                            Fuel Order | <a href="{{ route('fuels.show', $bill->fuel->id) }}" style="color: blue" target="_blank">{{ $bill->fuel ? $bill->fuel->order_number : "" }}</a> 
                                            @endif
                                           
                                        @elseif ( $bill->requisition)
                                            Requisition | <a href="{{ route('requisitions.show', $bill->requisition->id) }}" style="color: blue" target="_blank">{{ $bill->requisition ? $bill->requisition->requisition_number : "" }}</a> 
                                        @elseif ( $bill->invoice)
                                            Invoice VAT | <a href="{{ route('invoices.show', $bill->invoice->id) }}" style="color: blue" target="_blank">{{ $bill->invoice ? $bill->invoice->invoice_number : "" }}</a> 
                                         @elseif ($bill->ticket || $bill->ticket_inventory || $bill->ticket_expense)
                                            @if ($bill->ticket_inventory)
                                                Workshop Ticket | <a href="{{ route('tickets.show', $bill->ticket_inventory->ticket->id) }}" style="color: blue" target="_blank">{{  $bill->ticket_inventory->ticket ? $bill->ticket_inventory->ticket->ticket_number : "" }}</a> 
                                            @else
                                                Workshop Ticket | <a href="{{ route('tickets.show', $bill->ticket->id) }}" style="color: blue" target="_blank">{{  $bill->ticket ? $bill->ticket->ticket_number : "" }}</a> 
                                            @endif
                                            @if ($bill->horse)
                                                <br>
                                                Horse | <a href="{{route('horses.show', $bill->horse->id)}}" style="color: blue" target="_blank">{{$bill->horse ? $bill->horse->registration_number : ""}} {{$bill->horse->fleet_number ? "(".$bill->horse->fleet_number.")" : ""}} {{$bill->horse->horse_make ? $bill->horse->horse_make->name : ""}} {{$bill->horse->horse_model ? $bill->horse->horse_model->name : ""}}</a> 
                                            @elseif ($bill->vehicle)
                                                <br>
                                                Vehicle | <a href="{{route('vehicles.show', $bill->vehicle->id)}}" style="color: blue" target="_blank">{{$bill->vehicle ? $bill->vehicle->registration_number : ""}} {{$bill->vehicle->fleet_number ? "(".$bill->vehicle->fleet_number.")" : ""}} {{$bill->vehicle->vehicle_make ? $bill->vehicle->vehicle_make->name : ""}} {{$bill->vehicle->vehicle_model ? $bill->vehicle->vehicle_model->name : ""}}</a> 
                                            @elseif ($bill->trailer)
                                                <br>
                                                Trailer | <a href="{{route('trailers.show', $bill->trailer->id)}}" style="color: blue" target="_blank">{{$bill->trailer ? $bill->trailer->registration_number : ""}} {{$bill->trailer->fleet_number ? "(".$bill->trailer->fleet_number.")" : ""}} {{$bill->trailer->make}} {{$bill->trailer->model}}</a> 
                                            @endif
                                        @elseif ($bill->trip && ($bill->horse || $bill->driver || $bill->driver))
                                            Trip Expense | <a href="{{ route('trips.show', $bill->trip->id) }}" style="color: blue" target="_blank">{{ $bill->trip->trip_number }}</a> 
                                        @elseif ($bill->purchase)
                                            {{ $bill->category }} | <a href="{{ route('purchases.show', $bill->purchase->id) }}" style="color: blue" target="_blank">{{ $bill->purchase->purchase_number }}</a> 
                                        @elseif ($bill->workshop_service)
                                            Service | {{$bill->workshop_service->account ? $bill->workshop_service->account->name : ""}} | <a href="{{ route('workshop_services.show', $bill->workshop_service->id) }}" style="color: blue" target="_blank">{{ $bill->workshop_service->workshop_service_number }}</a> 
                                        @elseif ($bill->horse && !$bill->vendor)
                                            Horse | <a href="{{route('horses.show', $bill->horse->id)}}" style="color: blue" target="_blank">{{$bill->horse ? $bill->horse->registration_number : ""}} {{$bill->horse->fleet_number ? "(".$bill->horse->fleet_number.")" : ""}} {{$bill->horse->horse_make ? $bill->horse->horse_make->name : ""}} {{$bill->horse->horse_model ? $bill->horse->horse_model->name : ""}}</a> 
                                        @elseif ($bill->vehicle && !$bill->vendor)
                                           
                                            Vehicle | <a href="{{route('vehicles.show', $bill->vehicle->id)}}" style="color: blue" target="_blank">{{$bill->vehicle ? $bill->vehicle->registration_number : ""}} {{$bill->vehicle->fleet_number ? "(".$bill->vehicle->fleet_number.")" : ""}} {{$bill->vehicle->vehicle_make ? $bill->vehicle->vehicle_make->name : ""}} {{$bill->vehicle->vehicle_model ? $bill->vehicle->vehicle_model->name : ""}}</a> 
                                        @elseif ($bill->trailer && !$bill->vendor)
                                           
                                            Trailer | <a href="{{route('trailers.show', $bill->trailer->id)}}" style="color: blue" target="_blank">{{$bill->trailer ? $bill->trailer->registration_number : ""}} {{$bill->trailer->fleet_number ? "(".$bill->trailer->fleet_number.")" : ""}} {{$bill->trailer->make}} {{$bill->trailer->model}}</a> 
                                        @elseif ($bill->driver && !$bill->vendor)
                                          
                                            Driver | <a href="{{route('drivers.show', $bill->driver->id)}}" style="color: blue" target="_blank">{{$bill->driver->employee ? $bill->driver->employee->name : ""}} {{$bill->driver->employee ? $bill->driver->employee->surname : ""}} </a> 
                                        @endif
                                        
                                        @if ($bill->description)
                                        <br>
                                        {{$bill->description}}
                                        @endif
                                       
                                    </td>
                                  
                                    <td>
                                        @if ($bill->bill_expenses)
                                            @foreach ($bill->bill_expenses as $bill_expense)
                                                @if ($bill_expense->expense)
                                                    {{$bill_expense->expense ? $bill_expense->expense->name : ""}}
                                                @elseif($bill_expense->product)
                                                    {{ $bill_expense->product->brand ? $bill_expense->product->brand->name : ""}} {{ $bill_expense->product ? $bill_expense->product->name : ""}}
                                                @elseif($bill_expense->inventory)
                                                    {{ $bill_expense->inventory->product->brand ? $bill_expense->inventory->product->brand->name : ""}} {{ $bill_expense->inventory->product ? $bill_expense->inventory->product->name : ""}}
                                                @endif
                                                @if (!$loop->last),@endif
                                            @endforeach
                                        @endif
                                    </td>
                                    <td>{{$bill->bill_date}}</td>
                                    <td>{{$bill->currency ? $bill->currency->name : ""}}</td> 
                                    <td>
                                        @if ($bill->subtotal)
                                        {{$bill->currency ? $bill->currency->symbol : ""}}{{number_format($bill->subtotal,2)}}
                                        @else
                                            @if ($bill->total)
                                                {{$bill->currency ? $bill->currency->symbol : ""}}{{number_format($bill->total,2)}}
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                      
                                        {{$bill->currency ? $bill->currency->symbol : ""}}{{number_format($bill->tax_amount ? $bill->tax_amount : 0,2)}}
                                       
                                    </td>
                                    <td>
                                        @if ($bill->total)
                                             {{$bill->currency ? $bill->currency->symbol : ""}}{{number_format($bill->total,2)}}
                                        @endif
                                    </td>
                                   <td>
                                        @php
                                            $amount_paid = $bill->payments->sum('amount');
                                            $amount_paid_bulk = App\Models\BillPayment::where('bill_id', $bill->id)
                                                ->whereHas('payment', fn($query) => $query->where('transaction_category', 'Vendor Payments'))
                                                ->sum('amount'); // no need for get()
                                            $total_paid = $amount_paid + $amount_paid_bulk;  
                                        @endphp
                                        {{$bill->currency ? $bill->currency->symbol : ""}}{{number_format($total_paid,2)}}
                                    </td>
                                    <td>
                                        @if ($bill->balance)
                                             {{$bill->currency ? $bill->currency->symbol : ""}}{{number_format($bill->balance,2)}}

                                        @endif
                                         @if ($bill->accrual_balance)
                                            <br>
                                            <small>
                                                <strong>Accrual Bal: </strong>  {{number_format($bill->accrual_balance,2)}} <a href="#" wire:click.prevent="showAccrual({{$bill->id}})"><i class="fas fa-edit"></i></a>
                                            </small>
                                        @endif
                                    </td>
                                    <td><span class="label label-{{($bill->status == 'Paid') ? 'success' : (($bill->status == 'Partial') ? 'warning' : 'danger') }}">{{ $bill->status }}</span></td>
                                    <td><span class="badge bg-{{($bill->authorization == 'approved') ? 'success' : (($bill->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($bill->authorization == 'approved') ? 'approved' : (($bill->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{route('bills.show',$bill->id)}}"  ><i class="fas fa-eye color-default"></i>View</a></li>
                                                @if ($bill->authorization == "approved")
                                                     <li><a href="#" wire:click="showPayment({{$bill->id}})"  ><i class="fas fa-credit-card color-primary"></i> Record Payment</a></li>
                                                @endif
                                                @if (isset($bill->bill_for))
                                                @if ($bill->payments->isEmpty())
                                                <li><a href="{{route('bills.edit',$bill->id)}}"  ><i class="fas fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#billDeleteModal{{ $bill->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                                @endif
                                                @endif
                                                
                                            </ul>
                                        </div>
                                        @include('bills.delete')
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="13">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Bills Found ....
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
                                    @if (isset($bills))
                                        {{ $bills->links() }} 
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

       <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="accrualModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog  mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-copy"></i> Update Accrual Balance<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="updateAccrualBalance()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="">Accrual Balance<span class="required" style="color: red">*</span></label>
                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="accrual_balance" placeholder="Enter Accrual Balance" required >
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Update Balance</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Record a manual payment <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="recordPayment()" >
                <div class="modal-body">
                   
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Date Of Payment<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter Date" required />
                                @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Method Of Payment<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="mode_of_payment" class="form-control" required >
                                   <option value="">Select Method Of Payment</option>
                                   <option value="Cash">Cash</option>
                                    <option value="Bank Payment">Bank Payment</option>
                                    <option value="Credit Card">Credit Card</option>
                                    <option value="Loan">Loan</option>
                                    <option value="Paypal">Paypal</option>
                                    <option value="Other">Other</option>   
                               </select>
                                @error('mode_of_payment') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Payment Accounts<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="account_id" class="form-control" required>
                                   <option value="">Select Payment Account</option>
                                   @foreach ($accounts as $account)
                                   @if ($bill_currency && $account->currency)
                                        @if ($account->currency->id == $bill_currency->id)
                                        <option value="{{ $account->id }}">{{ $account->name }} {{ $account->currency ? $account->currency->name : ""}}</option>
                                        @endif     
                                   @else  
                                   select currency for bill
                                   @endif
                                   @endforeach
                               </select>
                                @error('account_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                <small style="color: green">Any account into which you deposit and withdraw funds from.</small> <br>
                                <small><a href="{{ route('accounts.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Account</a></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="vat">Currencies<span class="required" style="color: red">*</span></label>
                               <select class="form-control" wire:model.debounce.300ms="selectedCurrency" required disabled>
                                <option value="">Select Currency</option>
                                @foreach ($currencies as $currency)
                                <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>                                              
                                @endforeach
                               </select>
                                @error('selectedCurrency') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                            @if (!is_null($bill_currency))
                                @if (Auth::user()->employee->company)
                                    @if ($bill_currency->id != Auth::user()->employee->company->currency_id)
                                    <div class="form-group">
                                        <label for="vendor">Conversion Rate<span class="required" style="color: red">*</span></label>
                                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="exchange_rate"  placeholder="Exchange Rate" required>
                                        @error('exchange_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                        <small>{{$exchange_amount ? "The converted amount is: ".$exchange_amount : ""}}</small>
                                    </div> 
                                    @endif
                                @endif
                            @endif 
                        </div>
                    </div>
                  
               
                    @if ($mode_of_payment == "Bank Payment" || $mode_of_payment == "Credit Card" || $mode_of_payment == "Paypal")
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Reference Code</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="reference_code" placeholder="Enter Reference / Approval code"  >
                                @error('reference_code') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Proof Of Payment</label>
                                <input type="file" class="form-control" wire:model.debounce.300ms="pop" placeholder="Upload Pop" >
                                @error('pop') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                      
                    </div>
                    @elseif ($mode_of_payment == "Loan")
                    <div class="form-group">
                        <label for="country">Pending Loans</label>
                        <select wire:model.debounce.300ms="selectedLoan" class="form-control"  >
                            <option value="">Select Loan</option>
                            @foreach ($loans as $loan)
                                <option value="{{$loan->id}}">{{$loan->loan_number}} {{$loan->vendor ? $loan->vendor->name : ""}} {{$loan->currency ? $loan->currency->name : ""}} {{$loan->currency ? $loan->currency->symbol : ""}}{{number_format($loan->amount,2)}} {{$loan->interest ? '@ '.$loan->interest."%" : ""}} - {{$loan->currency ? $loan->currency->symbol : ""}}{{number_format($loan->total,2)}} | Balance: {{$loan->currency ? $loan->currency->symbol : ""}}{{number_format($loan->balance,2)}} | Installments: {{$loan->currency ? $loan->currency->symbol : ""}}{{number_format($loan->payment_per_month,2)}}</option>
                            @endforeach
                        </select>
                        @error('selectedLoan') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    @elseif ($mode_of_payment == "Cash")
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Denomination</label>
                               <select wire:model.debounce.300ms="denomination.0" class="form-control"  >
                                   <option value="">Select Denomination</option>
                                   <option value="1">1</option>
                                   <option value="2">2</option>
                                   <option value="5">5</option>
                                   <option value="10">10</option>
                                   <option value="20">20</option>
                                   <option value="50">50</option>
                                   <option value="100">100</option>
                                   <option value="200">200</option>
                               </select>
                                @error('denomination.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="name">Quantity</label>
                            <input type="number" step="any" class="form-control" wire:model.debounce.300ms="denomination_qty.0" placeholder="Enter Quantity"  >
                            @error('denomination_qty.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                        </div>
                      
                        <div class="row">
                            @foreach ($inputs as $key => $value)
                            <div class="col-md-5">
                                <div class="form-group">
                                    {{-- <label for="country">Denomination</label> --}}
                                   <select wire:model.debounce.300ms="denomination.{{ $value }}" class="form-control"  >
                                       <option value="">Select Denomination</option>
                                       <option value="1">1</option>
                                       <option value="2">2</option>
                                       <option value="5">5</option>
                                       <option value="10">10</option>
                                       <option value="20">20</option>
                                       <option value="50">50</option>
                                       <option value="100">100</option>
                                       <option value="200">200</option>
                                   </select>
                                    @error('denomination.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-5">
                                {{-- <label for="name">Quantity</label> --}}
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="denomination_qty.{{ $value }}" placeholder="Enter Quantity"  >
                                @error('denomination_qty.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label for=""></label>
                                    <button class="btn btn-danger btn-rounded xs"   wire:click.prevent="remove({{$key}})" > <i class="fa fa-times"></i></button>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i>Denomination</button>
                                </div>
                            </div>
                        </div>
        
                
                    @endif
                  
                    
                    <div class="row">
                        @if ($mode_of_payment == "OTHER")
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Value<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" {{ $amount > $bill_balance ? "disabled" : "" }} class="form-control" wire:model.debounce.300ms="amount" placeholder="Enter Value" required />
                                @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                @if ($amount > $bill_balance)
                                    <small style="color: red">Amount should be less than or equal to bill balance.</small>   
                                @endif
                            </div>
                        </div>
                        @else   
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Amount<span class="required" style="color: red">*</span></label>
                                <input type="number"  max="{{ $bill_balance }}" step="any" {{ $amount > $bill_balance ? "disabled" : "" }} class="form-control" wire:model.debounce.300ms="amount" placeholder="Enter Amount" required />
                                @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                @if ($amount > $bill_balance)
                                    <small style="color: red">Amount should be less than or equal to bill balance.</small>   
                                @endif
                            </div>
                        </div>
                        @endif
                       
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Balance<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="current_balance" placeholder="Current Balance" required disabled />
                                @error('current_balance') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                @if ($current_balance < 0)
                                <small style="color: red">Bill balance can not be negative</small>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="">Memo / Notes (Optional)</label>
                        <textarea class="form-control" wire:model.debounce.300ms="notes" cols="30" rows="5" placeholder="Write payment notes..."></textarea>
                        @error('notes') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>

                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        @if ($current_balance >= 0)
                            <button type="submit" class="btn bg-success btn-wide btn-rounded" ><i class="fa fa-save" ></i>Save</button>      
                        @elseif ($amount > $bill_balance)
                            <button type="submit" class="btn bg-success btn-wide btn-rounded" disabled ><i class="fa fa-save" ></i>Save</button> 
                        @else
                            <button type="submit" class="btn bg-success btn-wide btn-rounded" disabled ><i class="fa fa-save" ></i>Save</button> 
                        @endif
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>

      <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="paymentDrawdownModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Payment Drawdown <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="drawdownPayments()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Vendors<span class="required" style="color: red">*</span></label>
                               <select  class="form-control" wire:model.debounce.300ms="selectedVendor" required>
                                <option value="">Select Vendor</option>
                                    @foreach ($vendors as $vendor)
                                        <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                    @endforeach
                               </select>
                                @error('selectedVendor') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="vat">Currencies<span class="required" style="color: red">*</span></label>
                               <select class="form-control" wire:model.debounce.300ms="selectedCurrency" required>
                                <option value="">Select Currency</option>
                                @foreach ($currencies as $currency)
                                <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>                                              
                                @endforeach
                               </select>
                                @error('selectedCurrency') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                       
                    </div>
                  
                    @if (isset($selected_vendor) && isset($selected_currency) && isset($last_payment) && $last_payment->drawdown_balance > 0 )
                        <blockquote>
                            {{$selected_vendor->name}} has {{$selected_currency->name}} {{$selected_currency->symbol}}{{number_format($last_payment->drawdown_balance ? $last_payment->drawdown_balance : 0,2)}}
                        </blockquote>
                    @endif
                    <div class="form-group">
                        <label for="country">Bills<span class="required" style="color: red">*</span> </label>
                        <select wire:model.debounce.300ms="selectedBill" class="form-control" required>
                            <option value="">Select Bill</option>
                            @if (!is_null($selectedVendor) && !is_null($selectedCurrency) )
                                @foreach ($unpaid_bills as $bill)
                                    <option value="{{ $bill->id }}">Bill#: {{$bill->bill_number}} Date: {{$bill->bill_date}} Vendor: {{$bill->vendor ? $bill->vendor->name : ""}} Bal: {{$bill->currency ? $bill->currency->name : ""}} {{$bill->currency ? $bill->currency->symbol : ""}}{{number_format($bill->balance ? $bill->balance : 0,2)}} Status: {{ $bill->status }}</option>
                                @endforeach
                            @endif 
                        </select>
                         @error('selectedBill') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Drawdown Balance<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="payment_drawdown_balance" placeholder="Payment Drawdown Balance" disabled required >
                                @error('payment_drawdown_balance') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Balance<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="bill_drawdown_balance" placeholder="Bill Balance" disabled required >
                                @error('bill_drawdown_balance') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
            
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        @if (isset($selected_vendor) && isset($selected_currency) && isset($last_payment) && $last_payment->drawdown_balance > 0 )
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Save</button>
                        @else
                        <button type="submit" class="btn bg-success btn-wide btn-rounded" disabled><i class="fa fa-save"></i>Save</button>
                        @endif
                       
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>

  
</div>

