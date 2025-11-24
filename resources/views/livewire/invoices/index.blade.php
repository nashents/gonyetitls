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
                                                <select wire:model.debounce.300ms="invoice_filter" class="form-control" aria-label="..." >
                                                    <option value="created_at">Invoice Created At</option>
                                                    <option value="date">Invoice Date</option>
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
                                </div>
                            </div>

                            <div class="panel-title" style="margin-left:-15px;" >
                                <div class="col-lg-3">
                                    <div class="input-group">
                                        <span class="input-group-addon">Tax Status</span>
                                        <select wire:model.debounce.300ms="tax_status" class="form-control" aria-label="..." >
                                          <option value="all">All Invoices</option>
                                          <option value="taxed">Taxed Invoices</option>
                                          <option value="non-taxed">Non Taxed Invoices</option>
                                        </select>
                                    </div>
                                </div>
                               
                                <a href="#" wire:click="exportSalesExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                <a href="#" wire:click="exportSalesCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                                <a href="#" wire:click="exportSalesPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a> 
                            </div>
                            <div class="panel-title" style="margin-top:10px; margin-left:-1px">
                                <a href="{{route('invoices.create')}}"  class="btn btn-default"><i class="fa fa-plus-square-o"></i>Invoice</a>
                                @if (Auth::user()->is_admin())
                                <a href="#" wire:click="showBulkInvoices()"  class="btn btn-default"><i class="fa fa-copy"></i>Bulk Invoices</a>
                                <a href="#" wire:click="showInvoiceUpdate()"  class="btn btn-default"><i class="fa fa-copy"></i>Update Invoice Balances</a>
                                @endif
                                <a href="#" type="button" data-toggle="modal" data-target="#paymentDrawdownModal" class="btn btn-default btn-rounded btn-wide"><i class="fa fa-credit-card"></i>Bulk Invoices Payments</a>
                            </div>
                           
                            
                            <div class="col-md-3" style="float: right; padding-right:0px; ">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search invoices...">
                                </div>
                            </div>
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Invoice#
                                    </th>
                                    <th class="th-sm">CreatedBy
                                    </th>
                                    <th class="th-sm">Customer
                                    </th>
                                    <th class="th-sm">Date
                                    </th>
                                    <th class="th-sm">Due
                                    </th>
                                    <th class="th-sm">Status
                                    </th>  
                                    <th class="th-sm">Ccy
                                    </th>
                                    <th class="th-sm">Subtotal
                                    </th>
                                    <th class="th-sm">Tax Amt
                                    </th>
                                    <th class="th-sm">Total
                                    </th>
                                    <th class="th-sm">Paid
                                    </th>
                                    <th class="th-sm">Due
                                    </th>
                                    {{-- <th class="th-sm">Accrual Bal
                                    </th> --}}
                                    <th class="th-sm">Auth
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($invoices))
                                <tbody>
                                    @forelse ($invoices as $invoice)
                                    @php
                                        $expiry = $invoice->expiry;
                                        $now = new DateTime();
                                        $expiry_date = new DateTime($expiry);
                                    @endphp
                                  <tr>
                                    <td>
                                        {{$invoice->invoice_number}}
                                        <small>
                                            @if ($invoice->sales_order_number)
                                                <br>
                                                <strong>S.O.#:</strong> {{$invoice->sales_order_number}}
                                                <br>
                                            @endif
                                            @if ($invoice->pat_number)
                                                <strong>PAT#:</strong>{{$invoice->pat_number}}
                                                <br>
                                            @endif
                                            @if($invoice->purchase_order_number)
                                                <strong>P.O.#</strong>{{$invoice->purchase_order_number}}
                                                <br>
                                            @endif
                                        </small>
                                    </td>
                                     <td>{{$invoice->user ? $invoice->user->name : ""}} {{$invoice->user ? $invoice->user->surname : ""}}</td>
                                    <td>
                                        {{$invoice->customer ? $invoice->customer->name : ""}}
                                    </td>
                                    <td>{{$invoice->date}}</td>
                                    <td><span class="label label-{{$now < $expiry_date ? 'success' : 'danger' }}">{{$invoice->expiry}}</span></td>
                                    <td><span class="label label-{{($invoice->status == 'Paid') ? 'success' : (($invoice->status == 'Partial') ? 'warning' : 'danger') }}">{{ $invoice->status }}</span></td>
                                    <td>
                                        {{$invoice->currency ? $invoice->currency->name : ""}}
                                    </td>
                                    <td>
                                        @if ($invoice->subtotal)
                                        {{$invoice->currency ? $invoice->currency->symbol : ""}}{{number_format($invoice->subtotal,2)}}
                                        @endif
                                    </td>
                                    <td>
                                      
                                        {{$invoice->currency ? $invoice->currency->symbol : ""}}{{number_format($invoice->tax_amount ? $invoice->tax_amount : 0,2)}}
                                       
                                    </td>
                                    <td>
                                        @if ($invoice->total)
                                        {{$invoice->currency ? $invoice->currency->symbol : ""}}{{number_format($invoice->total,2)}}
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $amount_paid = $invoice->payments->sum('amount');
                                            $amount_paid_bulk = App\Models\InvoicePayment::where('invoice_id', $invoice->id)
                                                ->whereHas('payment', fn($query) => $query->where('transaction_category', 'Customer Deposits'))
                                                ->sum('amount'); // no need for get()

                                            $total_paid = $amount_paid + $amount_paid_bulk;  
                                        @endphp
                                        {{$invoice->currency ? $invoice->currency->symbol : ""}}{{number_format($total_paid,2)}}
                                    </td>
                                    <td>
                                       
                                        {{-- @if ($invoice->balance) --}}
                                        {{$invoice->currency ? $invoice->currency->symbol : ""}}{{number_format($invoice->balance,2)}}
                                        
                                        @if ($invoice->accrual_balance)
                                            <br>
                                            <small>
                                                <strong>Accrual Bal: </strong>  {{number_format($invoice->accrual_balance,2)}} <a href="#" wire:click.prevent="showAccrual({{$invoice->id}})"><i class="fas fa-edit"></i></a>
                                            </small>
                                        @endif
                                       
                                        {{-- @endif --}}
                                    </td>
                                    <td><span class="badge bg-{{($invoice->authorization == 'approved') ? 'success' : (($invoice->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($invoice->authorization == 'approved') ? 'approved' : (($invoice->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                   
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{route('invoices.show',$invoice->id)}}"  ><i class="fas fa-eye color-default"></i> View</a></li>
                                                @if ($invoice->authorization == "approved")
                                                <li><a href="{{route('invoices.preview',$invoice->id)}}"  ><i class="fas fa-file-invoice color-primary"></i> Preview</a></li>
                                                <li><a href="#" wire:click.prevent="showPayment({{$invoice->id}})"  ><i class="fas fa-credit-card color-primary"></i> Record Payment</a></li>
                                                @endif
                                                {{-- @if ($invoice->payments->isEmpty()) --}}
                                                <li><a href="{{route('invoices.edit',$invoice->id)}}"  ><i class="fas fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#invoiceDeleteModal{{ $invoice->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li> 
                                                {{-- @endif --}}
                                            </ul>
                                        </div>
                                        @include('invoices.delete')
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="13">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Invoices Found ....
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
                                    @if (isset($invoices))
                                        {{ $invoices->links() }} 
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="updateInvoicesModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog  mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-copy"></i> Update all invoices<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="bulkUpdateInvoices()" >
                <div class="modal-body">
                    <p>Update all invoices to the current and accurate balances</p>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Update Invoices</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="bulkInvoicesModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog  mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-copy"></i> Create bulk invoices<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="createInvoices()" >
                <div class="modal-body">
                    <p>Bulk create invoices feature for un invoiced trips is used when capturing historical invoices assuming payments were already made. </p>
                    <div class="row">
                        <div class="col-md-5">
                            <div class="input-group">
                                <span class="input-group-addon">
                          Filter By
                          </span>
                          <select wire:model.debounce.300ms="trip_filter" class="form-control" aria-label="..." >
                            <option value="created_at">Trip Created At</option>
                            <option value="start_date">Trip Started At</option>
                          </select>
                            </div>
                            <!-- /input-group -->
                        </div>
                        <div class="col-md-3" >
                            <div class="input-group">
                                <span class="input-group-addon">
                          From
                          </span>
                          <input type="date" wire:model.debounce.300ms="from"  class="form-control" aria-label="...">
                            </div>
                            <!-- /input-group -->
                        </div>
                        <div class="col-md-3" style="margin-left: 27px">
                            <div class="input-group">
                                <span class="input-group-addon">
                          To
                          </span>
                          <input type="date" wire:model.debounce.300ms="to"  class="form-control" aria-label="...">
                            </div>
                            <!-- /input-group -->
                        </div>
                    </div>
                    @if (isset($uninvoiced_trips))
                        <p>Are you sure you want to create bulk invoices for {{$uninvoiced_trips ? $uninvoiced_trips->count() : ""}} trips?</p>
                    @endif
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Create Invoices</button>
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
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Record a payment for this invoice <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="recordPayment()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Date<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter Payment Date" required >
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
                                <label for="country">Receiving Accounts<span class="required" style="color: red">*</span> </label>
                               <select wire:model.debounce.300ms="account_id" class="form-control" required  {{$mode_of_payment == "Loan" ? "disabled" : ""}}>
                                   <option value="">Select Receiving Account</option>
                                 @foreach ($accounts as $account)
                                    @if ($invoice_currency && $account->currency)
                                        @if ($account->currency->id == $invoice_currency->id)
                                            <option value="{{ $account->id }}">{{ $account->name }} {{ $account->currency ? $account->currency->name : ""}}</option>
                                        @endif     
                                    @else  
                                        select currency for invoice
                                    @endif
                                 @endforeach
                               </select>
                                @error('account_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                <small style="color: green">Any account into which you deposit and withdraw funds from.</small> <br>
                                <small><a href="{{ route('accounts.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Account</a></small> <a href="#" wire:click.prevent="refresh('accounts')" class="float-end"><i class="fa fa-refresh"></i></a>
                                
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
                            @if (!is_null($invoice_currency))
                                @if (Auth::user()->employee->company)
                                    @if ($invoice_currency->id != Auth::user()->employee->company->currency_id)
                                    <div class="form-group">
                                        <label for="customer">Conversion Rate<span class="required" style="color: red">*</span></label>
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
                      
                       
                            @foreach ($inputs as $key => $value)
                            <div class="row">
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
                        </div>
                            @endforeach
                       

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i>Denomination</button>
                                </div>
                            </div>
                        </div>
        
                
                    @endif
                
                    <div class="row">
                        @if ($mode_of_payment == "Other")
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Value<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" max="{{ $invoice_balance }}"  {{ $amount > $invoice_balance ? "disabled" : "" }} class="form-control" wire:model.debounce.300ms="amount" placeholder="Enter Value" required >
                                @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                @if ($amount > $invoice_balance)
                                <small style="color: red">Amount should be less than or equal to invoice balance.</small>   
                                @endif
                            </div>
                        </div>
                        @else   
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Amount<span class="required" style="color: red">*</span></label>
                                <input type="number" max="{{ $invoice_balance }}" {{ $amount > $invoice_balance ? "disabled" : "" }} step="any"  class="form-control" wire:model.debounce.300ms="amount" placeholder="Enter Amount" required >
                                @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                @if ($amount > $invoice_balance)
                                <small style="color: red">Amount should be less than or equal to invoice balance.</small>   
                                @endif
                                
                            </div>
                        </div>
                        @endif
                       
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Balance<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="current_balance" placeholder="Current Balance"  required disabled>
                                @error('current_balance') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                @if ($current_balance < 0)
                                <small style="color: red">Invoice balance can not be negative</small>
                                @endif
                               
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="">Memo / Notes (Optional)</label>
                        <textarea class="form-control" wire:model.debounce.300ms="notes" cols="30" rows="5"></textarea>
                        @error('notes') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>

                </div>
                
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        @if ($current_balance >= 0)
                        <button type="submit" class="btn bg-success btn-wide btn-rounded" ><i class="fa fa-save" ></i>Save</button>      
                        @elseif ($amount > $invoice_balance)
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
                                <label for="name">Customers<span class="required" style="color: red">*</span></label>
                               <select  class="form-control" wire:model.debounce.300ms="selectedCustomer" required>
                                <option value="">Select Customer</option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @endforeach
                               </select>
                                @error('selectedCustomer') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
                        @if (isset($selected_customer) && isset($selected_currency) && isset($last_payment) && $last_payment->drawdown_balance > 0 )
                            <blockquote>
                                {{$selected_customer->name}} has {{$selected_currency->name}} {{$selected_currency->symbol}}{{number_format($last_payment->drawdown_balance ? $last_payment->drawdown_balance : 0,2)}}
                            </blockquote>
                        @endif
                    <div class="form-group">
                        <label for="country">Invoices<span class="required" style="color: red">*</span> </label>
                        <select wire:model.debounce.300ms="selectedInvoice" class="form-control" required>
                            <option value="">Select Invoice</option>
                            @if (!is_null($selectedCustomer) && !is_null($selectedCurrency) )
                                @foreach ($unpaid_invoices as $invoice)
                                    <option value="{{ $invoice->id }}">{{$invoice->invoice_number}} | {{$invoice->customer ? $invoice->customer->name : ""}} | Balance: {{$invoice->currency ? $invoice->currency->name : ""}} {{$invoice->currency ? $invoice->currency->symbol : ""}}{{number_format($invoice->balance ? $invoice->balance : 0,2)}} | {{ $invoice->status }}</option>
                                @endforeach
                            @endif 
                        </select>
                         @error('selectedInvoice') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="invoice_drawdown_balance" placeholder="Invoice Balance" disabled required >
                                @error('invoice_drawdown_balance') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
            
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        @if (isset($selected_customer) && isset($selected_currency) && isset($last_payment) && $last_payment->drawdown_balance > 0 )
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

