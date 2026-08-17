<div>
    <div id="print-area">
        <div id="invoice">
            <x-loading/>
        <div class="toolbar hidden-print">
    <div class="text-end">

        <button type="button" onclick="goBack()"
                class="btn btn-default border-primary btn-wide btn-rounded">
            <i class="fa fa-arrow-left" style="color:black"></i> Back
        </button>

        {{-- ── FISCALIZE / STATUS BUTTON ──────────────────────────── --}}
        @if($fiscalStatus === 'approved')
            <span class="btn btn-success btn-wide btn-rounded" style="cursor:default">
                <i class="fa fa-check-circle"></i> Fiscalised
            </span>

        @elseif($fiscalStatus === 'pending')
            {{-- Submitted but still waiting on Fiscal Harmony --}}
            <button type="button"
                    wire:click="pollFiscalStatus"
                    wire:loading.attr="disabled"
                    wire:target="pollFiscalStatus"
                    class="btn btn-warning btn-wide btn-rounded">
                <span wire:loading.remove wire:target="pollFiscalStatus">
                    <i class="fa fa-refresh"></i> Check Fiscal Status
                </span>
                <span wire:loading wire:target="pollFiscalStatus">
                    <span class="spinner-border spinner-border-sm"></span> Checking...
                </span>
            </button>

        @elseif($fiscalStatus === 'failed' && $fiscalActionable)
            {{-- Failed but can retry --}}
            <button type="button"
                    wire:click="fiscalizeInvoice({{ $invoice->id }})"
                    wire:loading.attr="disabled"
                    wire:target="fiscalizeInvoice"
                    class="btn btn-danger btn-wide btn-rounded">
                <span wire:loading.remove wire:target="fiscalizeInvoice">
                    <i class="fa fa-refresh"></i> Retry Fiscalize
                </span>
                <span wire:loading wire:target="fiscalizeInvoice">
                    <span class="spinner-border spinner-border-sm"></span> Retrying...
                </span>
            </button>

        @else
            {{-- Not fiscalised yet (or non-actionable failed = still show button) --}}
            <button type="button"
                    wire:click="fiscalizeInvoice({{ $invoice->id }})"
                    wire:loading.attr="disabled"
                    wire:target="fiscalizeInvoice"
                    class="btn btn-default border-primary btn-wide btn-rounded">
                <span wire:loading.remove wire:target="fiscalizeInvoice">
                    <i class="fa fa-receipt" style="color:red"></i> Fiscalize Invoice
                </span>
                <span wire:loading wire:target="fiscalizeInvoice">
                    <span class="spinner-border spinner-border-sm" role="status"></span>
                    Fiscalizing...
                </span>
            </button>
        @endif

        {{-- ── DOWNLOAD FISCAL PDF (only once approved) ───────────── --}}
        @if($fiscalStatus === 'approved' && $fiscalPdfFile)
            <button type="button"
                    wire:click="downloadFiscalPdf"
                    class="btn btn-success btn-wide btn-rounded">
                <i class="fa fa-file-pdf-o"></i> Download Fiscal PDF
            </button>
        @endif

        <a href="javascript:void(0)" onclick="printSection()"
           class="btn btn-default border-primary btn-wide btn-rounded">
            <i class="fa fa-print" style="color: black"></i> Print
        </a>

        <a href="{{ route('invoices.pdf', $invoice->id) }}"
           class="btn btn-default border-primary btn-wide btn-rounded">
            <i class="fa fa-file-pdf-o" style="color:red"></i> Export as PDF
        </a>

    </div>

    {{-- ── STATUS BANNER ───────────────────────────────────────────── --}}
    @if($fiscalMessage)
        <div class="alert mt-2 mb-0 py-2
                {{ $fiscalMessageType === 'success' ? 'alert-success' : ($fiscalMessageType === 'error' ? 'alert-danger' : 'alert-warning') }}"
             role="alert">
            @if($fiscalMessageType === 'success') <i class="fa fa-check-circle"></i>
            @elseif($fiscalMessageType === 'error') <i class="fa fa-times-circle"></i>
            @else <i class="fa fa-clock-o"></i>
            @endif
            {{ $fiscalMessage }}
        </div>
    @endif

    {{-- ── QR CODE PANEL (shown once approved) ────────────────────── --}}
    @if($fiscalStatus === 'approved' && $fiscalQrUrl)
        <div class="card mt-3 border-success hidden-print">
            <div class="card-body py-2">
                <div class="d-flex align-items-center gap-3">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ urlencode($fiscalQrUrl) }}"
                         alt="Fiscal QR Code" width="100" height="100"
                         style="border:1px solid #ccc; border-radius:4px">
                    <div style="font-size:13px; line-height:1.8">
                        <div>
                            <strong>Verification Code:</strong>
                            <span class="badge bg-dark text-white px-2 py-1"
                                  style="font-family:monospace; font-size:14px">
                                {{ $fiscalVerifyCode }}
                            </span>
                        </div>
                        @if($fiscalDay)
                            <div><strong>Fiscal Day:</strong> {{ $fiscalDay }}</div>
                        @endif
                        @if($fiscalRaInvoiceNo)
                            <div><strong>RA Invoice No:</strong> {{ $fiscalRaInvoiceNo }}</div>
                        @endif
                        <small class="text-muted">
                            Scan QR code or enter verification code at the Revenue Authority portal.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <hr>
</div>
            <div class="invoice overflow-auto">
                <div style="min-width: 600px">
                    <header>
                        <div class="row">
                            <div class="col">
                                <a href="javascript:;">
                                    <img src="{{asset('images/uploads/'.$company->logo)}}" width="200" alt="">
                                </a>
                            </div>
                            <div class="col company-details">
                            
                                <h4 class="name" style="color:  {{$company->color ? $company->color : "#000000" }}" >
                                    {{$company->name}}
                                </h4>
                                <div>{{$company->street_address}} {{$company->suburb}} <br>
                                    {{$company->city}}, {{$company->country}}</div>
                                <div>
                                    {{$company->phonenumber}}
                                    @if ($company->second_phonenumber)
                                    | {{$company->second_phonenumber}}
                                    @endif
                                    @if ($company->third_phonenumber)
                                    | {{$company->third_phonenumber}}
                                    @endif
                                </div>
                            
                                
                                <div>{{$company->email}}</div>
                                @if ($company->second_email)
                                <div>{{$company->second_email}}</div>
                                @endif
                                @if ($company->third_email)
                                <div>{{$company->third_email}}</div>
                                <br>
                                @endif
                                <div>
                                
                                        VAT No.: {{$company->vat_number}}
                                
                                </div>
                                <div>
                                
                                        TIN.: {{$company->tin_number}}
                                
                                </div>
                                @if ($company->vendor_number)
                                    <div>
                                
                                        Vendor No.: {{$company->vendor_number}}
                                
                                    </div>
                                @endif
                               
                            </div>
                        </div>
                    
                        <div style="padding-top: 25px; padding-bottom:15px">
                            <center>
                                <h2>{{ $invoice->fiscalize == TRUE ? " FISCAL TAX INVOICE" : $company->invoice_title }} </h2>
                                <p>{{$company->invoice_subheading}}</p>
                            </center>
                        </div>
                
                    </header>
                    <main>
                        <div class="row contacts">
                            <div class="col invoice-to" >
                                <div class="text-gray-light">BILL TO:</div>
                                <h6 class="to"> <strong>Customer Name: </strong> {{$invoice->customer ? $invoice->customer->name : ""}}</h6>
                            
                                <div class="address" >
                                    <strong>Customer Address: </strong>
                                    @if (isset($invoice->customer->street_address) || isset($invoice->customer->suburb))
                                    {{$invoice->customer ? $invoice->customer->street_address : ""}} {{$invoice->customer ? $invoice->customer->suburb : ""}}, <br>  
                                    @endif
                                    {{$invoice->customer ? $invoice->customer->city : ""}} {{$invoice->customer ? $invoice->customer->country : ""}}
                                </div>
                            
                                @if (isset($invoice->customer->email))
                                <div class="email">
                                    <strong>Customer Email: </strong> {{$invoice->customer->email}}
                                </div>
                                @endif
                                @if (isset($invoice->customer->email))
                                <div class="email">
                                    <strong>Customer Phonenumber: </strong>{{$invoice->customer->phonenumber}}
                                </div>
                                @endif
                                <div class="email">
                                
                                    <strong>Customer VAT No:</strong> {{$invoice->customer ? $invoice->customer->vat_number : ""}}
                                
                                </div>
                                <div class="email">
                                
                                    <strong>Customer TIN No:</strong> {{$invoice->customer ? $invoice->customer->tin_number : ""}}
                                
                                </div>
                            </div>
                            <div class="col invoice-details">
                                @if ($invoice->fiscalize == TRUE)
                                <div class="date" style="padding-bottom: 3px"> <strong>Document No.:</strong> {{$invoice->invoice_number}}</div>
                                @else   
                                <div class="date" style="padding-bottom: 3px"> <strong>Invoice No.:</strong> {{$invoice->invoice_number}}</div>
                                @endif
                                @if ($invoice->sales_order_number)
                                <div class="date" style="padding-bottom: 3px"> <strong>S.O No.:</strong> {{$invoice->sales_order_number}}</div>
                                @endif
                                @if ($invoice->purchase_order_number)
                                <div class="date" style="padding-bottom: 3px"> <strong>P.O No.:</strong> {{$invoice->purchase_order_number}}</div>
                                @endif
                                @if ($invoice->pat_number)
                                <div class="date" style="padding-bottom: 3px"> <strong>PAT No.:</strong> {{$invoice->pat_number}}</div>
                                @endif
                                <div class="date" style="padding-bottom: 3px"><strong>Invoice Date:</strong> {{$invoice->date}}</div>
                                @if ($invoice->expiry)
                                <div class="date" style="padding-bottom: 3px"><strong>Payment Due:</strong> {{$invoice->expiry}}</div>
                                @endif
                                <div class="date" style="padding-bottom: 3px"><strong>Currency:</strong> {{$invoice->currency ? $invoice->currency->name : ""}}</div>
                                
                            </div>
                        </div>

                        <table >
                            <thead>
                                <tr>
                                   
                                    @if ($company->hide_description == False)
                                         <th class="text-center"> <strong>{{$company->items_column}}</strong></th>
                                    @endif
                                    <th class="text-center"> <strong>HS Code</strong></th>
                                    @if ($company->hide_quantity == False)
                                        <th class="text-right"><strong>{{$company->units_column}}</strong></th>
                                    @endif
                                    @if ($company->hide_price == False)
                                        <th class="text-right"><strong>{{$company->price_column}}</strong></th>
                                    @endif
                                    @if ($company->hide_amount == False)
                                         <th class="text-right"><strong>{{$company->total_column}}</strong><small>(Excl)</small></th>
                                    @endif
                                    <th class="text-right"><strong>VAT Amount</strong></th>
                                    <th class="text-right"><strong>Total</strong><small>(Incl)</small></th>
                                </tr>
                            </thead>
                            <tbody>
                            
                                @foreach ($invoice_items as $invoice_item)
                                    <tr>
                                        @php
                                            $tax = $invoice_item->tax;
                                        @endphp
                                       
                                        @if ($company->hide_description == False)
                                            <td class="text-center">
                                                @if ($invoice_item->product)
                                                {{$invoice_item->product ? $invoice_item->product->name : ""}}<br>
                                                {{-- @elseif ($invoice_item->trip)
                                                    {{$invoice_item->trip ? $invoice_item->trip->trip_number : ""}}<br> --}}
                                                {{-- @elseif ($invoice_item->rental)
                                                    {{$invoice_item->rental ? $invoice_item->rental->car_rental_number : ""}}<br> --}}
                                                @endif
                                                {{$invoice_item->description}}
                                            </td>
                                        @endif
                                        <td class="unit text-center"> 
                                            @if ($tax && $tax->hs_code)
                                                {{$tax->hs_code}}
                                            @endif
                                        </td>
                                        @if ($company->hide_quantity == False)
                                            <td class="unit text-right"> {{$invoice_item->qty}}</td>
                                        @endif
                                        @if ($company->hide_price == False)
                                            <td class="unit text-right">
                                                @if ($invoice_item->amount)
                                                {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($invoice_item->amount,2)}}        
                                                @endif
                                            </td>
                                        @endif
                                        @if ($company->hide_amount == False)
                                            <td class="unit text-right">
                                                @if ($invoice_item->subtotal)
                                                    {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($invoice_item->subtotal,2)}}
                                                @endif
                                            </td>
                                        @endif

                                        <td class="unit text-right">
                                            @if (isset($invoice->tax_amount) && $invoice->tax_amount > 0)
                                                {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($invoice_item->tax_amount,2)}}
                                            @else
                                                {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format(0,2)}}
                                            @endif
                                        </td>
                                    
                                        <td class="unit text-right">
                                            @if (isset($invoice_item->subtotal_incl))
                                                {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($invoice_item->subtotal_incl,2)}}
                                            @endif
                                        </td>
                                    </tr>

                                @endforeach
                            
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4"></td>
                                    <td colspan="2">SUB-TOTAL {{ $invoice->currency ? $invoice->currency->name : "" }} <small>(Excl)</small></td>
                                    <td>  
                                        @if (isset($invoice->invoice_items))
                                            {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($invoice->invoice_items->where('subtotal','!=',Null)->where('subtotal','!=','')->sum('subtotal'),2)}}  
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="4"></td>
                                    <td colspan="2">VAT TOTAL</td>
                                    <td>
                                        @if (isset($invoice->tax_amount) && $invoice->tax_amount > 0) 
                                        {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($invoice->tax_amount,2)}}
                                        @else
                                        {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format(0,2)}}
                                        @endif
                                    </td>
                                </tr>
                                @if ($invoice->discount)
                                    <tr>
                                        <td colspan="4"></td>
                                        <td colspan="2">DISCOUNT {{$invoice->discount->description}}</td>
                                        
                                        <td>
                                            @if ($invoice->discount->unit == "currency")
                                                {{$invoice->currency ? $invoice->currency->symbol : ""}}{{number_format($invoice->discount->amount ? $invoice->discount->amount : 0,2)}}
                                                @elseif($invoice->discount->unit == "percentage")
                                                {{number_format($invoice->discount->amount ? $invoice->discount->amount : 0,2)}} %
                                                @endif 
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <td colspan="4"></td>
                                    <td colspan="2">INVOICE TOTAL {{ $invoice->currency ? $invoice->currency->name : "" }} </td>
                                    <td>
                                        @if ($invoice->total)
                                            {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($invoice->total,2)}}
                                        @endif
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                
                    
                        @if ($invoice->memo)
                        <div class="notices">
                            <div><strong>Notes / Terms & Conditions</strong></div>
                            <div class="notice">{{$invoice->memo}}</div>
                        </div>
                        @endif
                    
                        <br>
                        @if ($invoice->bank_accounts->count()>0)
                                <table>
                                    <thead>
                                        <tr>
                                            <th  style="background-color: white;"><strong>Banking Details</strong></th>      
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            @foreach ($invoice->bank_accounts as $bank_account)
                                            <td class="text-left "  scope="col" >
                                                @if ($bank_account->name )
                                                <strong>Bank: </strong>{{ $bank_account->name }} <br> 
                                                @endif
                                                @if ($bank_account->branch)
                                                <strong>Branch: </strong>{{ $bank_account->branch }} <br>
                                                @endif
                                                @if ($bank_account->branch_code)
                                                <strong>Branch Code: </strong> {{ $bank_account->branch_code }} <br>
                                                @endif
                                                @if ($bank_account->swift_code)
                                                <strong>Swift Code: </strong> {{ $bank_account->swift_code }} <br>
                                                @endif
                                                @if ($bank_account->type)
                                                <strong>Account Type: </strong> {{ $bank_account->type }} <br> 
                                                @endif
                                                @if ($bank_account->account_name)
                                                <strong>Account Name: </strong> {{ $bank_account->account_name }} <br>
                                                @endif
                                            @if ($bank_account->account_number)
                                            <strong>Account Number: </strong> {{ $bank_account->account_number }} <br>
                                            @endif
                                            
                                            </td>
                                            @endforeach
                                        </tr>
                                    </tbody>
                                </table>
                                @endif
                    </main>
                
                    <center> 
                        <footer style=" text-align: center; bottom: 0px; left: 0px; right: 0px; ">
                            {{$invoice->footer}}
                            @if ($company->show_branding ?? true)
                                <br>
                                <strong style="font-size: 18px;">Powered By</strong> <img src="{{asset('images/logo.png')}}" alt="" style="width: 20%; height:20%">
                            @endif
                        </footer>
                    </center>  
                </div>
                <!--DO NOT DELETE THIS div. IT is responsible for showing footer always at the bottom-->
                <div></div>
            </div>
        </div>
    </div>
</div>
