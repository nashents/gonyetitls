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
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Payment Account(s)</label>
                                        <select class="form-control" wire:model.debounce.300ms="selectedAccount">
                                            <option value="">Select Account</option>
                                            <option value="All">All Accounts {{Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->name : ""}} {{$this->calculateTotalPaymentsInDefault()}}</option>
                                            @foreach ($accounts as $account)
                                                <option value="{{ $account->id }}">{{ $account->name }} {{$account->currency ? $account->currency->name : ""}} {{$account->currency ? $account->currency->symbol : ""}}{{$this->calculateTotalPayments($account->id)}}</option>
                                            @endforeach
                                            @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-8 mb-15">
                                    <label for="">Transactions</label>
                                    <button type="button" wire:click.prevent="showDepositModal" class="btn btn-default border-primary btn-rounded btn-wide">Add Deposit</button>
                                    <button type="button"  wire:click.prevent="showWithdrawalModal" class="btn btn-default border-primary btn-rounded btn-wide">Add Withdrawal</button>
                                    <button type="button"  data-toggle="modal" data-target="#journalModal" class="btn btn-default border-primary btn-rounded btn-wide">Add Journal Entry</button>

                                </div>
                           
                            </div>
                           
                            <table id="paymentsTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Date
                                    </th>
                                    <th class="th-sm">Description
                                    </th>
                                    <th class="th-sm">Type
                                    </th>
                                    <th class="th-sm">Account
                                    </th>
                                    <th class="th-sm">Category
                                    </th>
                                    <th class="th-sm">Currency
                                    </th>
                                    <th class="th-sm">Amount
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if ($payments->count()>0)
                                <tbody>
                                  @foreach ($payments as $payment)

                                  <tr>
                                    <td>{{Carbon\Carbon::parse($payment->date)->format('d M Y')}}</td>
                                    <td>
                                        @if ($payment->invoice)
                                            {{$payment->customer ? $payment->customer->name : ""}} Payment for invoice# <a href="{{route('invoices.show',$payment->invoice->id)}}" style="color: blue">{{$payment->invoice ? $payment->invoice->invoice_number : ""}}</a> <br>
                                        @elseif($payment->bill)
                                        Bill# <a href="{{route('bills.show',$payment->bill->id)}}" style="color: blue">{{$payment->bill ? $payment->bill->bill_number : ""}}</a> Payment to {{$payment->vendor ? $payment->vendor->name : ""}} <br>
                                        @endif
                                        @if ($payment->description)
                                        {{$payment->description}}
                                    @endif
                                    </td>
                                    <td>{{$payment->transaction_type ? $payment->transaction_type->name : ""}}</td>
                                    <td>
                                        @if ($payment->account)
                                            <a href="{{ route('accounts.show',$payment->account->id) }}" style="color: blue" >{{$payment->account ? $payment->account->name : ""}} {{$payment->currency ? $payment->currency->name : ""}}</a>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($payment->transaction_category)
                                        {{$payment->transaction_category}}
                                        @elseif($payment->invoice)
                                        Invoice# <a href="{{route('invoices.show',$payment->invoice->id)}}" style="color: blue">{{$payment->invoice ? $payment->invoice->invoice_number : ""}}</a> | Payment from {{$payment->customer ? $payment->customer->name : ""}}<br>
                                        @elseif($payment->bill)
                                        Bill# <a href="{{route('bills.show',$payment->bill->id)}}" style="color: blue">{{$payment->bill ? $payment->bill->bill_number : ""}}</a> Payment to {{$payment->vendor ? $payment->vendor->name : ""}} <br>
                                        @endif
                                    </td>
                                    <td>{{$payment->currency ? $payment->currency->name : ""}}</td>
                                    <td>
                                            @if (isset($payment->amount))
                                            {{$payment->currency ? $payment->currency->symbol : ""}}{{number_format($payment->amount,2)}}
                                            <br>
                                            @endif
                                            @if ($payment->exchange_rate)
                                            <small style="color: green">{{ Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->name : "" }} {{ Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->symbol : "" }}{{ number_format($payment->exchange_amount,2)}} @ {{$payment->exchange_rate}}</small>
                                            @endif
                                    </td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{route('transactions.show',$payment->id)}}"  ><i class="fa fa-eye color-default"></i> View</a></li>
                                                {{-- <li><a href="#" wire:click="edit({{$payment->id}})"  ><i class="fa fa-edit color-success"></i> Edit</a></li> --}}
                                                <li><a href="#" wire:click="delete({{$payment->id}})" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                       
                                </td>
                                  </tr>
                                  @endforeach
                                </tbody>
                                @else
                                    <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                                 @endif
                              </table>

                            <!-- /.col-md-12 -->
                        </div>
                    </div>
                </div>

            </div>
            <!-- /.row -->

        </div>
        <!-- /.container-fluid -->
    </section>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal fade" id="paymentDeleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                   <center> <strong>Are you sure you want to delete this Transaction?</strong> </center> 
                </div>
                <form wire:submit.prevent="deleteTransaction()"  >
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="journalModal" tabindex="-1" role="dialog">
    <div class="modal-dialog mw-100 w-50" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><i class="fas fa-book"></i> Manual Journal Entry
                    <button type="button" class="close" data-dismiss="modal"><span>×</span></button>
                </h4>
            </div>
            <form wire:submit.prevent="storeJournalEntry">
                <div class="modal-body">

                    {{-- Header --}}
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="date" required />
                                @error('date') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Journal Number</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="journal_number" placeholder="Auto-generated" readonly />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Reference</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="reference" placeholder="e.g. INV-001" />
                                @error('reference') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Status</label>
                                <select class="form-control" wire:model.debounce.300ms="status">
                                    <option value="draft">Draft</option>
                                    <option value="posted">Post Immediately</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Description / Narration</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="description" placeholder="Enter journal narration" />
                                @error('description') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Currency <span class="text-danger">*</span></label>
                                <select class="form-control" wire:model="currency_id">
                                    <option value="">Select Currency</option>
                                    @foreach ($currencies as $currency)
                                           <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>                                      
                                    @endforeach
                                </select>
                                @error('currency_id') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Exchange Rate</label>
                                <input type="number" step="any" min="0" class="form-control"
                                    wire:model.debounce.300ms="exchange_rate"
                                    placeholder="{{ $isForeignCurrency ? 'Enter rate' : 'Base currency' }}"
                                    @if(!$isForeignCurrency) disabled @endif />
                                @if($isForeignCurrency)
                                    <small class="text-muted">Rate against base currency</small>
                                @endif
                                @error('exchange_rate') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    {{-- Journal Lines --}}
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width:38%">Description</th>
                                    <th style="width:30%">Account <span class="text-danger">*</span></th>
                                    <th style="width:12%" class="text-right">Debit</th>
                                    <th style="width:12%" class="text-right">Credit</th>
                                    <th style="width:8%"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($lines as $index => $line)
                                <tr wire:key="line-{{ $index }}">
                                     <td>
                                        <input type="text" class="form-control form-control-sm"
                                            wire:model.debounce.300ms="lines.{{ $index }}.description"
                                            placeholder="Optional" />
                                    </td>
                                    <td>
                                        <select class="form-control form-control-sm" wire:model="lines.{{ $index }}.account_id">
                                            <option value="">Select Account</option>
                                                @foreach ($journal_transaction_account_types as $group)
                                                    <optgroup label="{{ $group->name }}">
                                                        @foreach ($group->account_types as $account_type)
                                                            @foreach ($account_type->accounts as $account)
                                                                <option value="{{ $account->id }}">{{ $account->name }}</option>
                                                            @endforeach
                                                        @endforeach
                                                    </optgroup>
                                                @endforeach
                                        </select>
                                        @error("lines.{$index}.account_id") <span class="text-danger small">{{ $message }}</span> @enderror
                                    </td>
                                    <td>
                                        <input type="number" step="any" min="0" class="form-control form-control-sm text-right"
                                            wire:model.debounce.300ms="lines.{{ $index }}.debit" placeholder="0.00" />
                                        @error("lines.{$index}.debit") <span class="text-danger small">{{ $message }}</span> @enderror
                                    </td>
                                    <td>
                                        <input type="number" step="any" min="0" class="form-control form-control-sm text-right"
                                            wire:model.debounce.300ms="lines.{{ $index }}.credit" placeholder="0.00" />
                                        @error("lines.{$index}.credit") <span class="text-danger small">{{ $message }}</span> @enderror
                                    </td>
                                   
                                    <td class="text-center">
                                        @if (count($lines) > 2)
                                        <button type="button" class="btn btn-sm btn-link text-danger p-0" wire:click="removeLine({{ $index }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="thead-light">
                                <tr>
                                    <td class="font-weight-bold">Totals</td>
                                    <td class="text-right font-weight-bold">{{ number_format($totalDebit, 2) }}</td>
                                    <td class="text-right font-weight-bold">{{ number_format($totalCredit, 2) }}</td>
                                    <td colspan="2"></td>
                                </tr>
                                <tr>
                                    <td colspan="5">
                                        @if ($totalDebit > 0 || $totalCredit > 0)
                                            @if (abs($totalDebit - $totalCredit) < 0.01)
                                                <span class="text-success small"><i class="fas fa-check-circle"></i> Balanced</span>
                                            @else
                                                <span class="text-danger small"><i class="fas fa-exclamation-triangle"></i> Out of balance by {{ number_format(abs($totalDebit - $totalCredit), 2) }}</span>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="mb-3">
                        <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="addLine">
                            <i class="fas fa-plus"></i> Add Line
                        </button>
                    </div>

                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal">
                            <i class="fa fa-times"></i> Close
                        </button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"
                            @if(abs($totalDebit - $totalCredit) >= 0.01 || $totalDebit == 0) disabled @endif>
                            <i class="fa fa-save"></i> {{ $status === 'posted' ? 'Post Journal' : 'Save Draft' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="depositModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add a deposit transaction <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="storeDepositTransaction()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Date<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="date" placeholder="Date" required />
                                @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Description</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="description">
                                @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Accounts<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="account_id" class="form-control" required>
                                        <option value="">Select Account</option>
                                        @foreach ($transaction_account_types as $account_type)
                                            <optgroup label="{{ $account_type->name }}">
                                                @foreach ($account_type->accounts as $account)
                                                    <option value="{{ $account->id }}">{{ $account->name }} {{ $account->currency ? $account->currency->name : "" }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                @error('account_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                            @if (!is_null($selectedCurrency))
                            @if (Auth::user()->employee->company)
                                @if ($selectedCurrency != Auth::user()->employee->company->currency_id)
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
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Transaction Type<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="transaction_type_id" class="form-control" required>
                                        <option value="">Select Type</option>
                                        @foreach ($transaction_types as $transaction_type)
                                            <option value="{{$transaction_type->id}}">{{$transaction_type->name}}</option>
                                        @endforeach
                                      
                                    </select>
                                @error('transaction_type_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>

                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Amount<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount" placeholder="Enter Amount" required />
                                @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Category</label>
                                    <select wire:model.debounce.300ms="transaction_category" class="form-control">
                                        <option value="">Select Category Type</option>
                                        @foreach ($account_types as $account_type)
                                        <optgroup label="{{ $account_type->name }}">
                                            @foreach ($account_type->accounts as $account)
                                                <option value="{{$account->name}}">{{ $account->name }} {{ $account->currency ? $account->currency->name : "" }}</option>
                                            @endforeach
                                        </optgroup>
                                        @endforeach
                                    </select>
                                    @error('transaction_category') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
               
                    <div class="mb-10">
                        <input type="checkbox" wire:model.debounce.300ms="customer"   class="line-style" />
                        <label for="one" class="radio-label">Add Customer</label>
                        @error('customer') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>
                    @if ($customer == TRUE)
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Customers</label>
                                    <select wire:model.debounce.300ms="customer_id" class="form-control">
                                        <option value="">Select Customer</option>
                                        @foreach ($customers as $customer)
                                             <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                        @endforeach
                                     
                                    </select>
                                @error('customer_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    @endif
                   
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Notes</label>
                               <textarea class="form-control" wire:model.debounce.300ms="notes" cols="30" rows="3" placeholder="Enter Notes"></textarea>
                                @error('notes') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Receipt</label>
                               <input type="file" class="form-control" wire:model.debounce.300ms="receipt">
                                @error('receipt') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="withdrawalModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add a withdrawal transaction <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="storeWithdrawalTransaction()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Date<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="date" placeholder="Date" required />
                                @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Description</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="description">
                                @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Accounts<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="account_id" class="form-control" required>
                                        <option value="">Select Account</option>
                                        @foreach ($transaction_account_types as $account_type)
                                            <optgroup label="{{ $account_type->name }}">
                                                @foreach ($account_type->accounts as $account)
                                                    <option value="{{ $account->id }}">{{ $account->name }} {{ $account->currency ? $account->currency->name : "" }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                @error('account_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Transaction Type<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="transaction_type_id" class="form-control" required>
                                        <option value="">Select Type</option>
                                        @foreach ($transaction_types as $transaction_type)
                                            <option value="{{$transaction_type->id}}">{{$transaction_type->name}}</option>
                                        @endforeach
                                      
                                    </select>
                                @error('transaction_type_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>

                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Amount<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount" placeholder="Enter Amount" required />
                                @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Category</label>
                                    <select wire:model.debounce.300ms="transaction_category" class="form-control">
                                        <option value="">Select Category Type</option>
                                        @foreach ($account_types as $account_type)
                                        <optgroup label="{{ $account_type->name }}">
                                            @foreach ($account_type->accounts as $account)
                                                <option value="{{ $account->name }}">{{ $account->name }} {{ $account->currency ? $account->currency->name : "" }}</option>
                                            @endforeach
                                        </optgroup>
                                        @endforeach
                                    </select>
                                @error('transaction_category') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="mb-10">
                        <input type="checkbox" wire:model.debounce.300ms="customer"   class="line-style" />
                        <label for="one" class="radio-label">Add Customer</label>
                        <input type="checkbox" wire:model.debounce.300ms="vendor"   class="line-style" />
                        <label for="one" class="radio-label">Add Vendor</label>
                        @error('vendor') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>
                 
                    <div class="row">
                        @if ($customer == TRUE)
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Customers</label>
                                    <select wire:model.debounce.300ms="customer_id" class="form-control">
                                        <option value="">Select Customer</option>
                                        @foreach ($customers as $customer)
                                             <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                        @endforeach
                                     
                                    </select>
                                @error('customer_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        @endif
                        @if ($vendor == TRUE)
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Vendors</label>
                                    <select wire:model.debounce.300ms="vendor_id" class="form-control">
                                        <option value="">Select Vendor</option>
                                        @foreach ($vendors as $vendor)
                                             <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                        @endforeach
                                     
                                    </select>
                                @error('vendor_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        @endif
                    </div>
                   
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Notes</label>
                               <textarea class="form-control" wire:model.debounce.300ms="notes" cols="30" rows="3" placeholder="Enter Notes"></textarea>
                                @error('notes') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Receipt</label>
                               <input type="file" class="form-control" wire:model.debounce.300ms="receipt">
                                @error('receipt') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

