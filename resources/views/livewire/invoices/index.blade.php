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
                                        
                             
                                        <div class="col-lg-3" >
                                            <div class="input-group">
                                                <span class="input-group-addon">
                                        From
                                        </span>
                                        <input type="date" wire:model.debounce.300ms="from"  class="form-control" aria-label="...">
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                        <div class="col-lg-3" >
                                            <div class="input-group">
                                                <span class="input-group-addon">
                                        To
                                        </span>
                                        <input type="date" wire:model.debounce.300ms="to"  class="form-control" aria-label="...">
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                         <div class="col-lg-3">
                                        <div class="input-group">
                                            <span class="input-group-addon">Transporters</span>
                                            <select wire:model.debounce.300ms="transporter_id" class="form-control" aria-label="..." >
                                            <option value="">Select Transporter</option>
                                            @foreach ($transporters as $transporter)
                                                <option value="{{$transporter->id}}">{{$transporter->name}}</option>
                                            @endforeach
                                            </select>
                                        </div>
                                    </div>
                                      
                                </div>
                                <div class="row">
                                    <div class="col-lg-3">
                                        <div class="input-group">
                                            <span class="input-group-addon">Customers</span>
                                            <select wire:model.debounce.300ms="customer_id" class="form-control" aria-label="..." >
                                            <option value="">Select Customer</option>
                                            @foreach ($customers as $customer)
                                                <option value="{{$customer->id}}">{{$customer->name}}</option>
                                            @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="input-group">
                                            <span class="input-group-addon">Currencies</span>
                                            <select wire:model.debounce.300ms="currency_id" class="form-control" aria-label="..." >
                                                <option value="">Select Currency</option>
                                                @foreach ($currencies as $currency)
                                                    <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
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
                                   
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <a href="#" wire:click="exportSalesExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                        <a href="#" wire:click="exportSalesCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                                        <a href="#" wire:click="exportSalesPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a> 
                                    </div>
                                </div>
                            </div>

                            <div class="panel-title" style="margin-top:10px; margin-left:-1px">
                                <a href="{{route('invoices.create')}}"  class="btn btn-default"><i class="fa fa-plus-square-o"></i>Invoice</a>
                                @if (Auth::user()->is_admin())
                                <a href="#" wire:click="showBulkInvoices()"  class="btn btn-default"><i class="fa fa-copy"></i>Bulk Invoices</a>
                                <a href="#" wire:click="showBulkDeleteInvoices()"  class="btn btn-default border-danger"><i class="fa fa-trash"></i>Bulk Delete Invoices</a>
                                @endif
                                <a href="#" type="button" data-toggle="modal" data-target="#paymentDrawdownModal" class="btn btn-default btn-rounded btn-wide"><i class="fa fa-credit-card"></i>Bulk Invoices Payments</a>
                                @if ($this->unpostedInvoicesCount > 0)
                                    <a href="#" wire:click.prevent="bulkPostToLedger()" wire:loading.attr="disabled" onclick="return confirm('Post {{ $this->unpostedInvoicesCount }} approved invoice(s) to the general ledger?')" class="btn btn-default border-danger btn-rounded btn-wide" title="Post every approved, unposted invoice to the ledger"><i class="fa fa-book"></i> Bulk Post to Ledger ({{ $this->unpostedInvoicesCount }})</a>
                                @endif
                                @if (Auth::user()->is_admin())
                                    <a href="#" wire:click.prevent="fixForexAmounts()" wire:loading.attr="disabled" onclick="return confirm('Recalculate the FX amounts for every foreign-currency invoice whose exchange rate/total are set but the converted amount is missing or out of date? This updates historical invoices.')" class="btn btn-default btn-rounded btn-wide" title="Recalculate exchange_amount from exchange_rate and total for foreign-currency invoices"><i class="fa fa-money"></i> Fix Forex Amounts</a>
                                @endif
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
                                    <th class="th-sm">InvoiceTo
                                    </th>
                                    <th class="th-sm">Item(s)
                                    </th>
                                     <th class="th-sm">
                                        Date
                                        <hr style="margin-top:2px; margin-bottom:2px">
                                        Due
                                    </th>
                                    <th class="th-sm">Status
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
                                    <th class="th-sm">Auth
                                    </th>
                                    <th class="th-sm">Ledger
                                    </th>
                                    <th class="th-sm">Account
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($invoices))
                                <tbody>
                                    @forelse ($invoices as $invoice)
                                    <tr wire:key="invoice-row-{{ $invoice->id }}">
                                        <td>
                                            {{$invoice->invoice_number}} <br>
                                            <small>
                                                <strong>By: </strong>{{$invoice->user ? $invoice->user->name : ""}} {{$invoice->user ? $invoice->user->surname : ""}} <br>
                                                <strong>On: </strong>{{ \Carbon\Carbon::parse($invoice->created_at)->format('d M Y H:i:s') }}
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
                                            @if ($this->sageEnabled && config('sageintacct.invoice.push'))
                                                @php $sm = $invoice->sageMapping; $ss = optional($sm)->sync_status; $approved = $invoice->authorization === 'approved'; @endphp
                                                <br>
                                                <small class="badge bg-{{ $sm ? ($ss === 'synced' ? 'success' : ($ss === 'failed' ? 'danger' : ($ss === 'requires_attention' ? 'warning' : 'secondary'))) : 'secondary' }}"
                                                       title="{{ optional($sm)->last_error ?? '' }}">Sage: {{ $sm ? ucwords(str_replace('_',' ', $ss)) : 'Not synced' }}</small>
                                                @if ($approved)
                                                    @if ($ss === 'synced')
                                                        <a href="#" wire:click.prevent="syncInvoiceToSage({{ $invoice->id }})" wire:loading.attr="disabled" title="Re-sync to Sage"><i class="fa fa-refresh"></i></a>
                                                    @elseif (in_array($ss, ['failed','requires_attention']))
                                                        <a href="#" wire:click.prevent="syncInvoiceToSage({{ $invoice->id }})" wire:loading.attr="disabled" style="color:#d9534f" title="Retry Sage sync"><i class="fa fa-refresh"></i> Retry</a>
                                                    @else
                                                        <a href="#" wire:click.prevent="syncInvoiceToSage({{ $invoice->id }})" wire:loading.attr="disabled" title="Sync to Sage"><i class="fa fa-cloud-upload"></i> Sync</a>
                                                    @endif
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            @if ($invoice->customer)
                                                {{$invoice->customer->name}}
                                            @elseif($invoice->transporter)
                                                {{$invoice->transporter->name}}
                                            @endif
                                        </td>
                                        <td>
                                            <small>
                                                    @if ($invoice->invoice_items)
                                                        @foreach ($invoice->invoice_items as $item)
                                                            @if ($item->product)
                                                                <strong>{{$item->product ? $item->product->name : ""}} {{$item->product ? $item->product->identification_number : ""}} {{$item->inventory ? $item->inventory->serial_number : ""}}</strong>  
                                                            @elseif($item->trip)  
                                                                <a href="{{route('trips.show',$item->trip?->id)}}" target="_blank" style="color: blue"><strong>{{$item->trip ? $item->trip->trip_number : ""}}</strong></a>
                                                            @elseif($item->trip_transport_order)  
                                                                <a href="{{route('trip_transport_orders.show', $item->trip_transport_order->id)}}" target="_blank" style="color: blue"><strong>{{$item->trip_transport_order->transport_order ? $item->trip_transport_order->tto_number : ""}}</strong></a>  
                                                            @elseif($item->rental)  
                                                                <strong>{{$item->rental ? $item->rental->car_rental_number : ""}}</strong>  
                                                            @endif 
                                                            {{-- {{$item->description}} --}}
                                                            @ {{number_format($item->subtotal_incl,2)}} @if (!$loop->last),@endif
                                                        @endforeach
                                                    @endif
                                            </small>
                                        </td>
                                        <td>
                                            {{$invoice->date}}
                                                <hr style="margin-top:5px; margin-bottom:5px">  
                                            <span class="label label-{{$this->checkExpiry($invoice->expiry) ? 'success' : 'danger' }}">{{$invoice->expiry}}</span>
                                        </td>
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

                                            @if ($invoice->currency_id && (int) $invoice->currency_id !== (int) $company->currency_id)
                                                <hr class="my-1">
                                                <small>
                                                    {{ $company->currency?->name }} {{ $company->currency?->symbol }}
                                                    {{ number_format(
                                                            (float) (is_numeric($invoice->exchange_amount)
                                                                ? $invoice->exchange_amount
                                                                : preg_replace('/[^\d\.\-]/', '', (string) ($invoice->exchange_amount ?? 0))
                                                            ),
                                                            2
                                                        ) }}
                                                    <br>
                                                    <strong>Rate:</strong>
                                                    {{ is_numeric($invoice->exchange_rate)
                                                        ? number_format((float) $invoice->exchange_rate, 4)
                                                        : ($invoice->exchange_rate ?: '-') }}
                                                </small>
                                            @endif
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
                                        {{$invoice->currency ? $invoice->currency->symbol : ""}}{{number_format($total_paid ? $total_paid : 0,2)}}
                                    </td>
                                    <td>
                                       
                                        {{-- @if ($invoice->balance) --}}
                                        {{$invoice->currency ? $invoice->currency->symbol : ""}}{{number_format($invoice->balance ? $invoice->balance : 0,2)}}
                                    </td>
                                    <td>
                                        <span class="badge bg-{{($invoice->authorization == 'approved') ? 'success' : (($invoice->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($invoice->authorization == 'approved') ? 'approved' : (($invoice->authorization == 'rejected') ? 'rejected' : 'pending') }}</span>
                                         @if ($invoice->authorized_by_id)
                                            @php
                                                $user = App\Models\User::find($invoice->authorized_by_id);
                                            @endphp
                                            <br>
                                            <small style="background-color: orange"><strong >AuthBy: </strong> {{$user?->name}} {{$user?->surname}}</small>  
                                        @endif
                                        @if ($invoice->authorization_date)
                                            <br>
                                            <small style="background-color: orange"><strong >Date: </strong> {{$invoice->authorization_date}}</small>  
                                        @endif
                                        @if ($invoice->comments)
                                            <br>
                                            <small style="background-color: orange"><strong >Comments: </strong> {{$invoice->comments}}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($invoice->journal_entry)
                                            <span class="badge bg-success" title="Journal {{ $invoice->journal_entry->journal_number }}">Posted</span>
                                            <br>
                                            <a href="#" wire:click.prevent="resyncLedger({{ $invoice->id }})" wire:loading.attr="disabled" onclick="return confirm('Reverse the existing journal entry and repost this invoice using its current figures? Use this after correcting a mistake (e.g. exchange rate) on an already-posted invoice.')" style="color:#337ab7" title="Reverse and repost using this invoice's current figures"><i class="fa fa-refresh"></i> Resync to Ledger</a>
                                        @elseif ($invoice->authorization == 'approved')
                                            <span class="badge bg-danger">Not Posted</span>
                                            <br>
                                            <a href="#" wire:click.prevent="postToLedger({{ $invoice->id }})" wire:loading.attr="disabled" style="color:#d9534f" title="Post to General Ledger"><i class="fa fa-book"></i> Post to Ledger</a>
                                        @else
                                            <span class="badge bg-secondary">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php $revenueAccountLabel = $invoice->revenue_account_label; @endphp
                                        <span class="label label-{{ $revenueAccountLabel === 'Sales' ? 'info' : ($revenueAccountLabel === 'Sales (Recognized)' ? 'success' : 'warning') }}">{{ $revenueAccountLabel }}</span>
                                    </td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{route('invoices.show',$invoice->id)}}"  ><i class="fas fa-eye color-default"></i> View</a></li>

                                                @if ($company->invoice_template == "classic")
                                                    <li><a href="{{route('invoices.classic',$invoice->id)}}"  ><i class="fas fa-file-invoice color-primary"></i> Preview</a></li>
                                                @elseif ($company->invoice_template == "transport")
                                                    <li><a href="{{route('invoices.transport',$invoice->id)}}"  ><i class="fas fa-file-invoice color-primary"></i> Preview</a></li>
                                                @elseif ($company->invoice_template == "modern")
                                                    <li><a href="{{route('invoices.modern',$invoice->id)}}"  ><i class="fas fa-file-invoice color-primary"></i> Preview</a></li>
                                                @endif
                                                @if ($invoice->authorization == "approved" && $invoice->balance > 0 )
                                                    <li wire:key="invoice-pay-{{ $invoice->id }}"><a href="#" wire:click.prevent="showPayment({{$invoice->id}})"><i class="fas fa-credit-card color-primary"></i> Record Payment</a></li>
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
                                    <td colspan="14">
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


    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="bulkDeleteInvoicesModal" tabindex="-1" role="dialog" aria-labelledby="bulkDeleteInvoicesModalLabel" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="bulkDeleteInvoicesModalLabel"><i class="fa fa-trash color-danger"></i> Bulk delete invoices by trip date<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="bulkDeleteInvoicesByTripDate()">
                <div class="modal-body">
                    <p class="text-danger"><strong>Warning:</strong> this permanently deletes every matching invoice, reversing any payments and journal entries recorded against them (and any bills raised off them). This cannot be undone from the UI.</p>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-addon">Trip Date</span>
                                <select wire:model.debounce.300ms="bulk_delete_trip_filter" class="form-control" aria-label="...">
                                    <option value="end_date">Trip Ended</option>
                                    <option value="trip_status_date">Trip Status Changed</option>
                                    <option value="start_date">Trip Started</option>
                                    <option value="created_at">Trip Created At</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-addon">From</span>
                                <input type="date" wire:model.debounce.300ms="bulk_delete_from" class="form-control" aria-label="...">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-addon">To<span class="required" style="color: red">*</span></span>
                                <input type="date" wire:model.debounce.300ms="bulk_delete_to" class="form-control" aria-label="..." required>
                            </div>
                            @error('bulk_delete_to') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="form-group" style="margin-top:10px">
                        <label for="bulk_delete_reason">Reason (optional)</label>
                        <input type="text" id="bulk_delete_reason" class="form-control" wire:model.debounce.300ms="bulk_delete_reason" placeholder="e.g. Trips done before 1 Aug - client requested cleanup">
                    </div>

                    @if (isset($bulk_delete_invoices) && filled($bulk_delete_to))
                        <p style="margin-top:10px">
                            <strong>{{ $bulk_delete_invoices->count() }}</strong> invoice(s) matched. Total value:
                            @foreach ($bulk_delete_invoices->groupBy('currency_id') as $currency_invoices)
                                {{ $currency_invoices->first()->currency ? $currency_invoices->first()->currency->symbol : '' }}{{ number_format($currency_invoices->sum('total'), 2) }}&nbsp;
                            @endforeach
                        </p>
                        @if ($bulk_delete_invoices->count() > 0)
                            <div style="max-height:200px; overflow-y:auto; border:1px solid #ddd; padding:5px">
                                <ul style="margin-bottom:0">
                                    @foreach ($bulk_delete_invoices as $invoice)
                                        <li>{{ $invoice->invoice_number }} - {{ $invoice->customer ? $invoice->customer->name : '' }} - {{ $invoice->currency ? $invoice->currency->symbol : '' }}{{ number_format($invoice->total,2) }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    @endif
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        @if (isset($bulk_delete_invoices) && $bulk_delete_invoices->count() > 0)
                            <button type="submit" class="btn btn-danger btn-wide btn-rounded" onclick="return confirm('Permanently delete {{ $bulk_delete_invoices->count() }} invoice(s) and reverse their payments/journal entries? This cannot be undone.')"><i class="fa fa-trash"></i>Delete {{ $bulk_delete_invoices->count() }} Invoice(s)</button>
                        @else
                            <button type="submit" class="btn btn-danger btn-wide btn-rounded" disabled><i class="fa fa-trash"></i>Delete</button>
                        @endif
                    </div>
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
                    @php
                        $base_currency = Auth::user()->employee->company ? Auth::user()->employee->company->currency : null;
                        $cross_currency = $invoice_currency && $payment_currency_id && $payment_currency_id != $invoice_currency->id;
                    @endphp
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="vat">Payment Currency<span class="required" style="color: red">*</span></label>
                               <select class="form-control" wire:model="payment_currency_id" required {{$mode_of_payment == "Loan" ? "disabled" : ""}}>
                                <option value="">Select Currency</option>
                                @foreach ($currencies as $currency)
                                <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                @endforeach
                               </select>
                                @error('payment_currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                @if ($cross_currency)
                                <small style="color: green">Invoice is in {{ $invoice_currency->name }} - the payment is applied to the invoice and shown on the statement in {{ $invoice_currency->name }}.</small>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Receiving Accounts<span class="required" style="color: red">*</span> </label>
                               <select wire:model.debounce.300ms="account_id" class="form-control" required  {{$mode_of_payment == "Loan" ? "disabled" : ""}}>
                                   <option value="">Select Receiving Account</option>
                                 @foreach ($accounts as $account)
                                    @if ($payment_currency_id && $account->currency_id == $payment_currency_id)
                                        <option value="{{ $account->id }}">{{ $account->name }} {{ $account->currency ? $account->currency->name : ""}}</option>
                                    @endif
                                 @endforeach
                               </select>
                                @error('account_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                <small style="color: green">Only accounts held in the selected payment currency are listed.</small> <br>
                                <small><a href="{{ route('accounts.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Account</a></small> <a href="#" wire:click.prevent="refresh('accounts')" class="float-end"><i class="fa fa-refresh"></i></a>

                            </div>
                        </div>
                    </div>
                    @if ($cross_currency)
                    <div class="row" wire:key="payment-cross-currency">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Amount Received ({{ $payment_currency ? $payment_currency->name : "" }})<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="paid_amount" placeholder="Amount received in {{ $payment_currency ? $payment_currency->name : "" }}" required >
                                @error('paid_amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        @if ($base_currency && $payment_currency_id != $base_currency->id)
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="customer">Conversion Rate ({{ $payment_currency ? $payment_currency->name : "" }} to {{ $base_currency->name }})<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="paid_exchange_rate"  placeholder="1 {{ $payment_currency ? $payment_currency->name : "" }} = ? {{ $base_currency->name }}" required>
                                @error('paid_exchange_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                @if (is_numeric($paid_amount) && is_numeric($paid_exchange_rate))
                                <small>The converted amount is: {{ $base_currency->symbol }}{{ number_format($paid_amount * $paid_exchange_rate, 2) }}</small>
                                @endif
                            </div>
                        </div>
                        @endif
                        @if ($base_currency && $invoice_currency->id != $base_currency->id)
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="customer">Conversion Rate ({{ $payment_currency ? $payment_currency->name : "" }} to {{ $invoice_currency->name }})<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="invoice_conversion_rate"  placeholder="1 {{ $payment_currency ? $payment_currency->name : "" }} = ? {{ $invoice_currency->name }}" required>
                                @error('invoice_conversion_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                <small style="color: green">The rate this payment was agreed at - it sets how much of the invoice is settled.@if ($invoice_conversion_rate_auto && is_numeric($invoice_conversion_rate)) Started at the rate the invoice was booked at.@endif</small>
                            </div>
                        </div>
                        @endif
                    </div>
                    @elseif (!is_null($invoice_currency) && $base_currency && $invoice_currency->id != $base_currency->id)
                    <div class="form-group" wire:key="payment-conversion-rate">
                        <label for="customer">Conversion Rate<span class="required" style="color: red">*</span></label>
                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="exchange_rate"  placeholder="Exchange Rate" required>
                        @error('exchange_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                        <small>{{$exchange_amount ? "The converted amount is: ".$exchange_amount : ""}}</small>
                    </div>
                    @endif


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
                                <label for="name">{{ $cross_currency ? "Amount Applied To Invoice (".$invoice_currency->name.")" : "Amount" }}<span class="required" style="color: red">*</span></label>
                                <input type="number" max="{{ $invoice_balance }}" {{ $amount > $invoice_balance ? "disabled" : "" }} step="any"  class="form-control" wire:model.debounce.300ms="amount" placeholder="Enter Amount" required >
                                @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                @if ($amount > $invoice_balance)
                                <small style="color: red">Amount should be less than or equal to invoice balance.</small>   
                                @endif
                                @php
                                    // What the amount received converts to in the invoice's currency
                                    $paid_to_invoice_rate = $cross_currency ? ($base_currency && $invoice_currency->id == $base_currency->id ? $paid_exchange_rate : $invoice_conversion_rate) : null;
                                    $paid_worth = is_numeric($paid_amount) && is_numeric($paid_to_invoice_rate) ? round($paid_amount * $paid_to_invoice_rate, 2) : null;
                                @endphp
                                @if (!is_null($paid_worth) && is_numeric($invoice_balance) && $paid_worth > $invoice_balance + 0.005)
                                <small style="color: red">The amount received converts to {{ $invoice_currency->symbol }}{{ number_format($paid_worth, 2) }}, more than the invoice balance. Only the balance can be applied here - the excess would post as an exchange gain, so record it as a separate customer deposit instead.</small>
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
                            @if ($amount > $invoice_balance)
                                <button type="submit" class="btn bg-success btn-wide btn-rounded" disabled ><i class="fa fa-save" ></i>Save</button>
                            @else
                                <button type="submit" class="btn bg-success btn-wide btn-rounded" ><i class="fa fa-save" ></i>Save</button>     
                            @endif 
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
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Bulk Invoices Payments <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
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
                    @php
                        $drawdown_ready = filled($selectedCustomer) && filled($selectedCurrency);
                        $drawdown_symbol = isset($selected_currency) ? $selected_currency->symbol : "";
                        $drawdown_invoices = $drawdown_ready && $unpaid_invoices ? $unpaid_invoices : collect();
                        $to_apply = round($allocating['payments']->sum(), 2);
                        $over_allocated = $deposits->contains(fn ($deposit) => ($allocating['payments'][$deposit->payment->id] ?? 0) > $deposit->available + 0.005)
                            || $drawdown_invoices->contains(fn ($invoice) => ($allocating['invoices'][$invoice->id] ?? 0) > (float) $invoice->balance + 0.005);
                        $incomplete_line = collect($allocations)->contains(fn ($line) => (filled($line['payment_id']) || filled($line['invoice_id']) || filled($line['amount'])) && (blank($line['payment_id']) || blank($line['invoice_id']) || !is_numeric($line['amount']) || $line['amount'] <= 0));
                    @endphp
                    @if ($drawdown_ready)
                        <blockquote>
                            {{ isset($selected_customer) ? $selected_customer->name : "" }} has {{ isset($selected_currency) ? $selected_currency->name : "" }} {{ $drawdown_symbol }}{{ number_format($deposits->sum('available'), 2) }} available across {{ $deposits->count() }} payment(s)
                        </blockquote>

                        <div class="form-group">
                            <label>Apply Payments To Invoices<span class="required" style="color: red">*</span></label>
                            <br>
                            <small style="color: green">Each line applies one payment to one invoice. Add more lines to split a payment across invoices, or to put several payments on one invoice.</small>
                            <table class="table table-bordered table-condensed" style="margin-bottom: 5px">
                                <thead>
                                    <tr>
                                        <th>Payment</th>
                                        <th>Invoice</th>
                                        <th style="width: 150px">Amount</th>
                                        <th style="width: 40px"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($allocations as $index => $line)
                                    <tr wire:key="allocation-{{ $line['key'] }}">
                                        <td>
                                            <select class="form-control" wire:model="allocations.{{ $index }}.payment_id">
                                                <option value="">Select Payment</option>
                                                @foreach ($deposits as $deposit)
                                                <option value="{{ $deposit->payment->id }}">{{ $deposit->payment->payment_number }} | {{ $deposit->payment->date }} | Available: {{ $drawdown_symbol }}{{ number_format($deposit->available, 2) }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select class="form-control" wire:model="allocations.{{ $index }}.invoice_id">
                                                <option value="">Select Invoice</option>
                                                @foreach ($drawdown_invoices as $invoice)
                                                <option value="{{ $invoice->id }}">{{ $invoice->invoice_number }} | {{ $invoice->date }} | Balance: {{ $drawdown_symbol }}{{ number_format((float) $invoice->balance, 2) }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="allocations.{{ $index }}.amount" placeholder="Amount">
                                        </td>
                                        <td>
                                            @if (count($allocations) > 1)
                                            <button type="button" class="btn btn-danger btn-rounded xs" wire:click.prevent="removeAllocation({{ $index }})"><i class="fa fa-times"></i></button>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <button type="button" class="btn btn-success btn-rounded" wire:click.prevent="addAllocation()"><i class="fa fa-plus"></i>Line</button>
                        </div>

                        <div class="form-group">
                            <label>Payments</label>
                            <div style="max-height: 200px; overflow-y: auto;">
                                <table class="table table-bordered table-condensed" style="margin-bottom: 0">
                                    <thead>
                                        <tr>
                                            <th>Payment#</th>
                                            <th>Date</th>
                                            <th>Reference</th>
                                            <th class="text-right">Amount</th>
                                            <th class="text-right">Available</th>
                                            <th class="text-right">Applying Now</th>
                                            <th class="text-right">Left After</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($deposits as $deposit)
                                        @php
                                            $applying = $allocating['payments'][$deposit->payment->id] ?? 0;
                                            $left = round($deposit->available - $applying, 2);
                                        @endphp
                                        <tr wire:key="drawdown-deposit-{{ $deposit->payment->id }}">
                                            <td>{{ $deposit->payment->payment_number }}</td>
                                            <td>{{ $deposit->payment->date }}</td>
                                            <td>{{ $deposit->payment->reference_code }} {{ $deposit->payment->mode_of_payment }}</td>
                                            <td class="text-right">{{ $drawdown_symbol }}{{ number_format((float) $deposit->payment->amount, 2) }}</td>
                                            <td class="text-right">{{ $drawdown_symbol }}{{ number_format($deposit->available, 2) }}</td>
                                            <td class="text-right">{{ $drawdown_symbol }}{{ number_format($applying, 2) }}</td>
                                            <td class="text-right" style="{{ $left < 0 ? 'color: red' : '' }}">{{ $drawdown_symbol }}{{ number_format($left, 2) }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="text-center">No payments with funds available for this customer and currency.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Invoices</label>
                            <div style="max-height: 200px; overflow-y: auto;">
                                <table class="table table-bordered table-condensed" style="margin-bottom: 0">
                                    <thead>
                                        <tr>
                                            <th>Invoice#</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th class="text-right">Total</th>
                                            <th class="text-right">Balance</th>
                                            <th class="text-right">Applying Now</th>
                                            <th class="text-right">Balance After</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($drawdown_invoices as $invoice)
                                        @php
                                            $applying = $allocating['invoices'][$invoice->id] ?? 0;
                                            $left = round((float) $invoice->balance - $applying, 2);
                                        @endphp
                                        <tr wire:key="drawdown-invoice-{{ $invoice->id }}">
                                            <td>{{ $invoice->invoice_number }}</td>
                                            <td>{{ $invoice->date }}</td>
                                            <td>{{ $invoice->status }}</td>
                                            <td class="text-right">{{ $drawdown_symbol }}{{ number_format((float) $invoice->total, 2) }}</td>
                                            <td class="text-right">{{ $drawdown_symbol }}{{ number_format((float) $invoice->balance, 2) }}</td>
                                            <td class="text-right">{{ $drawdown_symbol }}{{ number_format($applying, 2) }}</td>
                                            <td class="text-right" style="{{ $left < 0 ? 'color: red' : '' }}">{{ $drawdown_symbol }}{{ number_format($left, 2) }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="text-center">No unpaid invoices for this customer and currency.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <strong>Total to be applied: {{ $drawdown_symbol }}{{ number_format($to_apply, 2) }}</strong>
                        @if ($over_allocated)
                        <br><small style="color: red">The lines add up to more than a payment has available or an invoice has outstanding - see the figures in red.</small>
                        @elseif ($incomplete_line)
                        <br><small style="color: red">Every line needs a payment, an invoice and an amount.</small>
                        @endif
                    @endif

                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        @if ($to_apply > 0 && !$over_allocated && !$incomplete_line)
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

