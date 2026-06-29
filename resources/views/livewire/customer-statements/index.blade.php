<div>
  
        <section class="section">
            <x-loading/>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel">
                            
                            <div>
                                @include('includes.messages')
                            </div>
                         
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="panel">
                                        <div class="panel-heading">
                                            <div class="panel-title">
                                                <h5>Customer Statements</h5>
                                            </div>
                                        </div>
                                        <div class="panel-body">
                                            <div class="row">
                                                <form wire:submit.prevent="generateStatement()">
                                                    <div class="col-lg-3">
                                                        <div class="input-group">
                                                            <span class="input-group-addon">Customers</span>
                                                            <select wire:model.debounce.300ms="selectedCustomer" class="form-control" aria-label="...">
                                                                <option value="">Select Customer</option>
                                                                @foreach ($customers as $customer)
                                                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-3">
                                                        <div class="input-group">
                                                            <span class="input-group-addon">Type</span>
                                                            <select wire:model.debounce.300ms="selectedType" class="form-control" aria-label="...">
                                                                <option value="">Select Type</option>
                                                                <option value="Outstanding Invoices">Outstanding Invoices</option>
                                                                <option value="Account Activity">Account Activity</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    @if (!is_null($selectedType) && $selectedType == "Account Activity")
                                                    <div class="col-lg-2">
                                                        <div class="input-group">
                                                            <span class="input-group-addon">From</span>
                                                            <input type="date" wire:model.debounce.300ms="from" class="form-control" aria-label="...">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-2">
                                                        <div class="input-group" style="margin-left:15px;">
                                                            <span class="input-group-addon">To</span>
                                                            <input type="date" wire:model.debounce.300ms="to" class="form-control" aria-label="...">
                                                        </div>
                                                    </div>
                                                    @endif
                                                </form>
                                            </div>
                                            <!-- /.row -->
                                            <br><br>

                                            @if (isset($invoices) && $invoices && $invoices->count() > 0)
                                            <div class="row">
                                                <div class="col-md-4"></div>
                                                <div class="col-md-6">
                                                    <div class="col-lg-2">
                                                        <div class="btn-group">
                                                            @if (isset($selectedCustomer) && isset($selectedType) && $selectedType == "Outstanding Invoices")
                                                            <a href="{{ route('customer_statements.preview.outstanding', ['selectedCustomer' => $selectedCustomer, 'selectedType' => $selectedType]) }}"
                                                               class="btn btn-default border-primary btn-wide btn-rounded">
                                                                <i class="fas fa-file-invoice"></i> Preview
                                                            </a>
                                                            @elseif(isset($selectedCustomer) && isset($selectedType) && $selectedType == "Account Activity" && isset($from) && isset($to))
                                                            <a href="{{ route('customer_statements.preview.account', ['selectedCustomer' => $selectedCustomer, 'selectedType' => $selectedType, 'from' => $from, 'to' => $to]) }}"
                                                               class="btn btn-default border-primary btn-wide btn-rounded">
                                                                <i class="fas fa-file-invoice"></i> Preview
                                                            </a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-2">
                                                        <div class="btn-group">
                                                            @if (isset($selectedCustomer) && isset($selectedType) && $selectedType == "Outstanding Invoices")
                                                            <button wire:click="exportCustomerStatementExcel()"
                                                                    class="btn btn-default border-primary btn-wide btn-rounded"
                                                                    type="button">
                                                                <i class="fas fa-download"></i> Excel
                                                            </button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-2"></div>
                                            </div>
                                            @endif

                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- ===================== TABLE AREA ===================== --}}
                            <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">

                                {{-- =========== OUTSTANDING INVOICES =========== --}}
                                @if ($selectedType == "Outstanding Invoices")
                                <table id="invoicesTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                    <caption>Outstanding Invoices for {{ App\Models\Customer::find($selectedCustomer)?->name }}</caption>
                                    <thead>
                                        <tr>
                                            <th class="th-sm">Invoice#</th>
                                            <th class="th-sm">Customer</th>
                                            <th class="th-sm">Invoice Date</th>
                                            <th class="th-sm">Due Date</th>
                                            <th class="th-sm">Currency</th>
                                            <th class="th-sm">Invoice Total</th>
                                            <th class="th-sm">Amount Paid</th>
                                            <th class="th-sm">Amount Due</th>
                                            <th class="th-sm">Status</th>
                                        </tr>
                                    </thead>

                                    @if ($invoices && $invoices->count() > 0)
                                    <tbody>
                                        @foreach ($invoices as $invoice)
                                        <tr>
                                            <td>
                                                <a href="{{ route('invoices.show', $invoice->id) }}" style="color: blue">
                                                    {{ $invoice->invoice_number }}
                                                </a>
                                            </td>
                                            <td>{{ $invoice->customer ? $invoice->customer->name : '' }}</td>
                                            <td>{{ $invoice->date }}</td>
                                            <td>{{ $invoice->expiry }}</td>
                                            <td>{{ $invoice->currency ? $invoice->currency->name : '' }}</td>
                                            <td>{{ $invoice->currency ? $invoice->currency->symbol : '' }}{{ number_format($invoice->total, 2) }}</td>
                                            <td>
                                                {{ $invoice->currency ? $invoice->currency->symbol : '' }}{{ number_format($invoice->payments->sum('amount'), 2) }}
                                            </td>
                                            <td>
                                                @if ($invoice->balance)
                                                    {{ $invoice->currency ? $invoice->currency->symbol : '' }}{{ number_format($invoice->balance, 2) }}
                                                @endif
                                            </td>
                                            <td>
                                                <span class="label label-{{ ($invoice->status == 'Paid') ? 'success' : (($invoice->status == 'Partial') ? 'warning' : 'danger') }}">
                                                    {{ $invoice->status }}
                                                </span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    @elseif ($invoices && $invoices->count() == 0)
                                    <tbody>
                                        <tr>
                                            <td colspan="9" class="text-center">
                                                <img style="width:25%; height:25%;" src="{{ asset('images/nodata.png') }}" alt="">
                                            </td>
                                        </tr>
                                    </tbody>
                                    @else
                                    <tbody>
                                        <tr>
                                            <td colspan="9">
                                                <div class="row">
                                                    <center>
                                                        <img style="width:25%; height:25%;" src="{{ asset('images/invoice.png') }}" alt="">
                                                        <h3>Keep customers informed</h3>
                                                        <p>Remind your customers about outstanding invoices or send details of their account activity.</p>
                                                        <p>Create a statement by selecting a customer and statement type from the form above.</p>
                                                    </center>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                    @endif
                                </table>

                                {{-- =========== ACCOUNT ACTIVITY =========== --}}
                                @elseif ($selectedType == "Account Activity")

                                    @php
                                        $currencies         = App\Models\Currency::all();
                                        $invoiced_currencies = App\Models\Invoice::where('customer_id', $selectedCustomer)
                                                                ->where('authorization', 'approved')
                                                                ->pluck('currency_id')
                                                                ->toArray();
                                    @endphp

                                    @foreach ($currencies as $currency)
                                        @if (isset($invoiced_currencies) && in_array($currency->id, $invoiced_currencies))

                                        <table id="invoicesTable_{{ $currency->id }}" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                            <caption>Account Activity for {{ App\Models\Customer::find($selectedCustomer)?->name }} in {{ $currency->fullname }} {{ $currency->name }}</caption>
                                            <thead>
                                                <tr>
                                                    <th class="th-sm">Date</th>
                                                    <th class="th-sm">Item</th>
                                                    <th class="th-sm">Currency</th>
                                                    <th class="th-sm">Amount</th>
                                                    <th class="th-sm">Accrual Balance</th>
                                                </tr>
                                            </thead>

                                            @if ($results && $results->count() > 0)
                                            <tbody>

                                                {{-- Opening Balance row --}}
                                                @php
                                                    $opening_balance = $openingBalances[$currency->id] ?? 0.00;
                                                    $closing_balance = $closingBalances[$currency->id] ?? 0.00;
                                                @endphp
                                                <tr class="table-active">
                                                    <td colspan="2"><strong>{{ date('F j, Y', strtotime($from)) }}</strong></td>
                                                    <td colspan="2"><strong>Opening Balance {{ $currency->name }}</strong></td>
                                                    <td><strong>{{ $currency->symbol }}{{ number_format($opening_balance, 2) }}</strong></td>
                                                </tr>

                                                {{-- Transaction rows for this currency --}}
                                                @foreach ($results->where('currency_id', $currency->id) as $result)
                                                    @php
                                                        $row_currency = App\Models\Currency::find($result->currency_id);
                                                        $invoice      = App\Models\Invoice::where('invoice_number', $result->number)->where('authorization', 'approved')->first();
                                                        $payment      = App\Models\Payment::where('payment_number', $result->number)->first();
                                                        $credit_note  = $result->transaction_type === 'credit_note'
                                                            ? App\Models\CreditNote::where('credit_note_number', $result->number)->where('authorization', 'approved')->first()
                                                            : null;
                                                    @endphp
                                                    <tr>
                                                        <td>{{ date('F j, Y', strtotime($result->transaction_date)) }}</td>
                                                        <td>
                                                            @if ($credit_note)
                                                                Credit Note# {{ $result->number }}
                                                                @if ($credit_note->invoice)
                                                                    for <a href="{{ route('invoices.show', $credit_note->invoice->id) }}" target="_blank" rel="noopener noreferrer" style="color: blue">Invoice# {{ $credit_note->invoice->invoice_number }}</a>
                                                                @endif
                                                            @elseif ($invoice)
                                                                <a href="{{ route('invoices.show', $invoice->id) }}" target="_blank" rel="noopener noreferrer" style="color: blue">
                                                                    Invoice# {{ $result->number }}
                                                                </a><br>
                                                                Due {{ $invoice->expiry }}
                                                            @elseif ($payment)
                                                                <a href="{{ route('payments.show', $payment->id) }}" target="_blank" rel="noopener noreferrer" style="color: blue">
                                                                    {{ $result->number }}
                                                                </a> Payment made for
                                                                @if (isset($payment->invoice))
                                                                    <a href="{{ route('invoices.show', $payment->invoice->id) }}" target="_blank" rel="noopener noreferrer" style="color: blue">
                                                                        Invoice# {{ $payment->invoice->invoice_number }}
                                                                    </a>
                                                                @elseif (isset($payment->invoice_payment))
                                                                    <a href="{{ route('invoices.show', $payment->invoice_payment->invoice->id) }}" target="_blank" rel="noopener noreferrer" style="color: blue">
                                                                        Invoice# {{ $payment->invoice_payment->invoice ? $payment->invoice_payment->invoice->invoice_number : '' }}
                                                                    </a>
                                                                @endif
                                                            @endif
                                                        </td>
                                                        <td>{{ $row_currency ? $row_currency->name : '' }}</td>
                                                        <td>{{ $row_currency ? $row_currency->symbol : '' }}{{ number_format($result->amount, 2) }}</td>
                                                        <td>{{ $row_currency ? $row_currency->symbol : '' }}{{ number_format($result->accrual_balance ?? 0, 2) }}</td>
                                                    </tr>
                                                @endforeach

                                                {{-- Closing Balance row --}}
                                                <tr class="table-active">
                                                    <td colspan="2"><strong>{{ date('F j, Y', strtotime($to)) }}</strong></td>
                                                    <td colspan="2"><strong>Closing Balance {{ $currency->name }}</strong></td>
                                                    <td><strong>{{ $currency->symbol }}{{ number_format($closing_balance, 2) }}</strong></td>
                                                </tr>

                                            </tbody>
                                            @elseif ($results && $results->count() == 0)
                                            <tbody>
                                                <tr>
                                                    <td colspan="5" class="text-center">
                                                        <img style="width:25%; height:25%;" src="{{ asset('images/nodata.png') }}" alt="">
                                                    </td>
                                                </tr>
                                            </tbody>
                                            @else
                                            <tbody>
                                                <tr>
                                                    <td colspan="5">
                                                        <div class="row">
                                                            <center>
                                                                <img style="width:25%; height:25%;" src="{{ asset('images/invoice.png') }}" alt="">
                                                                <h3>Keep customers informed</h3>
                                                                <p>Remind your customers about outstanding invoices or send details of their account activity.</p>
                                                                <p>Create a statement by selecting a customer and statement type from the form above.</p>
                                                            </center>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                            @endif
                                        </table>

                                        @endif
                                    @endforeach

                                {{-- =========== NOTHING SELECTED =========== --}}
                                @else
                                <div class="row">
                                    <center>
                                        <img style="width:25%; height:25%;" src="{{ asset('images/invoice.png') }}" alt="">
                                        <h3>Keep customers informed</h3>
                                        <p>Remind your customers about outstanding invoices or send details of their account activity.</p>
                                        <p>Create a statement by selecting a customer and statement type from the form above.</p>
                                    </center>
                                </div>
                                @endif

                            </div>
                            {{-- /.panel-body --}}

                        </div>
                    </div>
                </div>
                <!-- /.row -->
            </div>
            <!-- /.container-fluid -->
        </section>

    </div>
