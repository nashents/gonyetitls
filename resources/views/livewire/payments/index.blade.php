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
                                          <select wire:model.debounce.300ms="payment_filter" class="form-control" aria-label="..." >
                                            <option value="created_at">Payment Recorded At</option>
                                            <option value="date">Payment Date</option>
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
                                            <span class="input-group-addon">Movement</span>
                                            <select wire:model.debounce.300ms="movement" class="form-control" aria-label="..." >
                                            <option value="all">All Payments</option>
                                            <option value="Debit">Debit</option>
                                            <option value="Credit">Credit</option>
                                            </select>
                                        </div>
                                    </div>
                                    {{-- <a href="#" wire:click="exportPaymentsExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                    <a href="#" wire:click="exportPaymentsCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                                    <a href="#" wire:click="exportPaymentsPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a>  --}}
                                </div>
                                <div class="panel-title" style="margin-left:-15px;" >
                                    <div class="col-lg-3">
                                        <a href="" data-toggle="modal" data-target="#paymentModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Record payment</a>
                                    </div>
                                </div>
                            </div>
                            <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                                <div class="col-md-3" style="float: right; padding-right:0px; ">
                                    <div class="form-group">
                                        <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search payments...">
                                    </div>
                                </div>
                                <table  class="table  table-spaymented table-bordered table-sm table-responsive" cellspacing="0" width="100%" style=" width:100%; height:100%;">
                                    <thead >
                                        <th class="th-sm">Payment#
                                        </th>
                                        <th class="th-sm">Date
                                        </th>
                                        <th class="th-sm">Description
                                        </th>
                                        <th class="th-sm">MOP
                                        </th>
                                        <th class="th-sm">Account
                                        </th>
                                        <th class="th-sm">Category
                                        </th>
                                        <th class="th-sm">Movement
                                        </th>
                                        <th class="th-sm">Currency
                                        </th>
                                        <th class="th-sm">Amt
                                        </th>
                                        <th class="th-sm">Accrual Bal
                                        </th>
                                        <th class="th-sm">Actions
                                        </th>

                                      </tr>
                                    </thead>
                               
                                    @if (isset($payments))
                                    <tbody>
                                        @forelse ($payments as $payment)
                                      <tr>
                                        <td>{{$payment->payment_number}}</td>
                                        <td>{{Carbon\Carbon::parse($payment->date)->format('d M Y')}}</td>
                                        <td>
                                            @if ($payment->invoice)
                                                {{$payment->customer ? $payment->customer->name : ""}} Payment for invoice# <a href="{{route('invoices.show',$payment->invoice->id)}}" style="color: blue">{{$payment->invoice ? $payment->invoice->invoice_number : ""}}</a> <br>
                                            @elseif ($payment->invoice_payment)
                                                {{$payment->customer ? $payment->customer->name : ""}} Setoff payment for invoice# <a href="{{ route('invoices.show',$payment->invoice_payment->invoice_id) }}" style="color:blue">{{$payment->invoice_payment->invoice ? $payment->invoice_payment->invoice->invoice_number : ""}}</a>
                                            @elseif ($payment->bill_payment)
                                                {{$payment->vendor ? $payment->vendor->name : ""}} Setoff payment for bill# <a href="{{ route('bills.show',$payment->bill_payment->bill_id) }}" style="color:blue">{{$payment->bill_payment->bill ? $payment->bill_payment->bill->bill_number : ""}}</a>
                                            @elseif($payment->bill)
                                                Bill# <a href="{{route('bills.show',$payment->bill->id)}}" style="color: blue">{{$payment->bill ? $payment->bill->bill_number : ""}}</a> Payment to {{$payment->vendor ? $payment->vendor->name : ""}} <br>
                                            @elseif ($payment->customer && !$payment->invoice)
                                                <a href="{{ route('customers.show',$payment->customer->id) }}" style="color:blue">{{$payment->customer ? $payment->customer->name : ""}}</a> deposit
                                            @elseif ($payment->vendor && !$payment->bill)
                                                <a href="{{ route('vendors.show',$payment->vendor->id) }}" style="color:blue">{{$payment->vendor ? $payment->vendor->name : ""}}</a> payment
                                            @elseif ($payment->recovery)
                                            Recovery# <a href="{{ route('recoveries.show',$payment->recovery->id) }}" style="color:blue">{{$payment->recovery ? $payment->recovery->recovery_number : ""}}</a> payment. <br>
                                            @endif
                                           
                                            @if ($payment->description)
                                                {{$payment->description}}
                                            @endif
                                        </td>
                                        <td>{{$payment->mode_of_payment}}</td>
                                        <td>{{$payment->account ? $payment->account->name : ""}}</td>
                                        <td>
                                            @if ($payment->transaction_category)
                                                {{$payment->transaction_category}}
                                            @elseif($payment->invoice)
                                                Invoice# <a href="{{route('invoices.show',$payment->invoice->id)}}" style="color: blue">{{$payment->invoice ? $payment->invoice->invoice_number : ""}}</a> | Payment from {{$payment->customer ? $payment->customer->name : ""}}<br>
                                            @elseif($payment->bill)
                                                Bill# <a href="{{route('bills.show',$payment->bill->id)}}" style="color: blue">{{$payment->bill ? $payment->bill->bill_number : ""}}</a> Payment to {{$payment->vendor ? $payment->vendor->name : ""}} <br>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($payment->invoice)
                                               Crt
                                            @elseif ($payment->invoice_payment)
                                                Crt
                                            @elseif($payment->bill)
                                               Dbt
                                            @elseif($payment->bill_payment)
                                               Dbt
                                            @elseif ($payment->vendor && !$payment->bill)
                                                Dbt
                                            @elseif ($payment->customer && !$payment->invoice)
                                                Crt
                                            @elseif ($payment->recovery)
                                                Crt
                                            @endif
                                        </td>
                                        <td>{{$payment->currency ? $payment->currency->name : ""}}</td>
                                        <td>
                                            @if ($payment->amount)
                                                {{$payment->currency ? $payment->currency->symbol : ""}}{{number_format($payment->amount,2)}}
                                                <br>
                                            @endif
                                            @if ($payment->exchange_rate)
                                                <small style="color: green">{{ Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->name : "" }} {{ Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->symbol : "" }}{{ number_format($payment->exchange_amount,2)}} @ {{$payment->exchange_rate}}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($payment->accrual_balance)
                                            {{$payment->currency ? $payment->currency->symbol : ""}} {{number_format($payment->accrual_balance,2)}} <a href="#" wire:click.prevent="showAccrual({{$payment->id}})"><i class="fas fa-edit"></i></a>
                                            @endif
                                        </td>
                                         <td class="w-10 line-height-35 table-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                   <li><a href="{{route('payments.show', $payment->id)}}"><i class="fas fa-eye color-default"></i>View</a></li>
                                                    
                                                   @if ($payment->receipt)
                                                   <li><a href="{{route('receipts.preview',$payment->receipt->id)}}"  ><i class="fas fa-receipt color-primary"></i> Receipt</a></li>
                                                   @endif    
                                                   {{-- @if (Auth::user()->is_admin()) --}}
                                                        <li><a href="#" data-toggle="modal" data-target="#paymentDeleteModal{{$payment->id}}"><i class="fas fa-trash color-danger"></i>Delete</a></li> 
                                                   {{-- @endif --}}
                                                </ul>
                                            </div>
                                     
                                            @include('payments.delete')
                                    </td>
                                      </tr>
                                      @empty
                                  <tr>
                                    <td colspan="9">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Payments Found ....
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
                                        @if (isset($payments))
                                            {{ $payments->links() }} 
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

          <!-- Modal -->
          <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
            <div class="modal-dialog mw-100 w-60" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Record bulk payment <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                    </div>
                    <form wire:submit.prevent="recordPayment()" >
                    <div class="modal-body">
                        <p>Record all bulk payments received from your customers to offset pending invoices OR sent to vendors to offset pending bills.</p>
                      
                        <h5 class="underline mt-10">Select Source / Destination</h5>
                        <div class="mb-10">
                            <label class="radio-inline">
                                <input type="radio" wire:model.debounce.300ms="source_destination" value="Customer" name="optradio" >Customer
                                </label>
                            <label class="radio-inline">
                                <input type="radio" wire:model.debounce.300ms="source_destination" value="Vendor" name="optradio">Vendor
                            </label>
                            @error('source_destination') <span class="text-danger error">{{ $message }}</span>@enderror
                        </div>
                     
                            <div class="row">
                                <div class="col-md-6">
                                    @if ($source_destination == "Customer")
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
                                    @elseif($source_destination == "Vendor")
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
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">Date Of Payment<span class="required" style="color: red">*</span></label>
                                        <input type="date" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter Date" required >
                                        @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                   
                                </div>
                              
                            </div>
          
                      
                        <div class="row">
                                <div class="col-md-4">
                                <div class="form-group">
                                    <label for="name">Currencies<span class="required" style="color: red">*</span></label>
                                   <select  class="form-control" wire:model.debounce.300ms="selectedCurrency" required>
                                    <option value="">Select Currency</option>
                                        @foreach ($currencies as $currency)
                                        <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>                                              
                                        @endforeach
                                   </select>
                                    @error('selectedCurrency') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                                @if (!is_null($selectedCurrency))
                                @if (Auth::user()->employee->company)
                                    @if ($selectedCurrency != Auth::user()->employee->company->currency_id)
                                    <div class="row">
                                        <div class="form-group">
                                            <label for="customer">Conversion Rate<span class="required" style="color: red">*</span></label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="exchange_rate"  placeholder="Exchange Rate" required>
                                            @error('exchange_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                            <small>{{$exchange_amount ? "The converted amount is: ".$exchange_amount : ""}}</small>
                                        </div> 
                                    </div>
                                    @endif
                                @endif
                            @endif 
                            </div>
                            <div class="col-md-4">
                                @if ($source_destination == "Customer")
                                    <div class="form-group">
                                        <label for="country">Receiving Accounts<span class="required" style="color: red">*</span> </label>
                                        <select wire:model.debounce.300ms="selectedAccount" class="form-control" required>
                                            <option value="">Select Receiving Account</option>
                                                @foreach ($accounts as $account)
                                                    <option value="{{ $account->id }}">{{ $account->name }} {{ $account->currency ? $account->currency->name : ""}}</option>    
                                                @endforeach
                                        </select>
                                        @error('selectedAccount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                 @elseif($source_destination == "Vendor")   
                                    <div class="form-group">
                                        <label for="country">Sending Accounts<span class="required" style="color: red">*</span> </label>
                                        <select wire:model.debounce.300ms="selectedAccount" class="form-control" required>
                                            <option value="">Select Sending Account</option>
                                                @foreach ($accounts as $account)
                                                    <option value="{{ $account->id }}">{{ $account->name }} {{ $account->currency ? $account->currency->name : ""}}</option>    
                                                @endforeach
                                        </select>
                                        @error('selectedAccount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                @endif
                            </div>
                        
                            <div class="col-md-4">
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

                        @elseif ($mode_of_payment == "OTHER")
                        <div class="form-group">
                            <label for="">Specify Other</label>
                            <textarea wire:model.debounce.300ms="specify_other" class="form-control" cols="30" rows="2"></textarea>
                        </div>
                    
                        @endif
                     
                        <div class="row">
                            @if ($mode_of_payment == "OTHER")
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Value<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount" placeholder="Enter Value" required >
                                    @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            @else   
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Amount<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount" placeholder="Enter Amount" required >
                                    @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            @endif
                           <div class="col-md-6">
                            <div class="form-group">
                                <label for="">Memo / Notes (Optional)</label>
                                <textarea class="form-control" wire:model.debounce.300ms="notes" cols="30" rows="5"></textarea>
                                @error('notes') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
