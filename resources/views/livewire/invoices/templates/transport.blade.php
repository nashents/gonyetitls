<div>
    {{-- TOOLBAR --}}
    <div class="toolbar hidden-print">
        <div class="text-end">

            <button type="button" onclick="goBack()"
                    class="btn btn-default border-primary btn-wide btn-rounded">
                <i class="fa fa-arrow-left" style="color:black"></i> Back
            </button>

            @if($fiscalStatus === 'approved')
                <span class="btn btn-success btn-wide btn-rounded" style="cursor:default">
                    <i class="fa fa-check-circle"></i> Fiscalised
                </span>

            @elseif($fiscalStatus === 'pending')
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

    {{-- INVOICE --}}
    <div id="printable-invoice">
        <div class="invoice-wrap">

            {{-- FISCAL BANNER --}}
            @if($fiscalStatus === 'approved')
            <div class="fiscal-banner">
                <div class="qr-placeholder">
                    @if($fiscalQrUrl)
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=64x64&data={{ urlencode($fiscalQrUrl) }}"
                             width="64" height="64" alt="QR">
                    @else
                        QR
                    @endif
                </div>
                <div style="flex:1">
                    <div class="fiscal-title">FISCAL TAX INVOICE</div>
                    <div class="fiscal-text">
                        <strong>Receipt Counter:</strong> {{ $invoice->fiscal_document->receipt_counter }} &nbsp;&nbsp;
                        <strong>Fiscal Day No:</strong> {{ $invoice->fiscal_document->fiscal_day_no }}<br>
                        Invoice No: {{ $invoice->invoice_number }} &nbsp;&nbsp;
                        Date: {{ \Carbon\Carbon::parse($invoice->fiscal_document->fiscal_date)->format('d/m/Y H:i') }}<br>
                        Device Serial: {{ $invoice->fiscal_document->device_serial }} &nbsp;&nbsp;
                        Fiscal Device Id: {{ $invoice->fiscal_document->fiscal_device_id }}<br>
                        Verification Code: {{ $fiscalVerifyCode }}<br>
                        You can verify receipt manually at
                        <a href="https://fdms.zimra.co.zw" target="_blank" style="color:#0057b7">https://fdms.zimra.co.zw</a>
                    </div>
                </div>
            </div>
            @endif

            {{-- HEADER --}}
            <div class="header-row">
                <div class="company-block">
                    <div class="company-name">{{ $company->name }}</div>
                    <p>{{ $company->address_line_1 }}</p>
                    @if($company->address_line_2)<p>{{ $company->address_line_2 }}</p>@endif
                    <p>{{ $company->city }}</p>
                    @if($company->phone_1 || $company->phone_2)
                    <p>Tel: {{ $company->phone_1 }}{{ $company->phone_2 ? ' / ' . $company->phone_2 : '' }}</p>
                    @endif
                    @if($company->mobile_1 || $company->mobile_2)
                    <p>Cell: {{ $company->mobile_1 }}{{ $company->mobile_2 ? ' / ' . $company->mobile_2 : '' }}</p>
                    @endif
                    @if($company->email)<p><a href="mailto:{{ $company->email }}">{{ $company->email }}</a></p>@endif
                </div>

                <div class="invoice-meta">
                    <div class="invoice-number">INVOICE No. {{ $invoice->invoice_number }}</div>
                    <table>
                        <tr><td>Date:</td><td>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}</td></tr>
                        <tr><td>Currecy:</td><td>{{ $invoice->currency?->name }}</td></tr>
                        <tr><td>VAT#:</td><td>{{ $company->vat_number }}</td></tr>
                        <tr><td>TIN#:</td><td>{{ $company->tin_number }}</td></tr>
                        @if($invoice->po_number)
                        <tr><td>PO No:</td><td>{{ $invoice->po_number }}</td></tr>
                        @endif
                    </table>
                </div>
            </div>

            {{-- CUSTOMER --}}
            <div class="customer-section">
                <table>
                    <tr>
                        <td>Customer Name:</td>
                        <td class="customer-name-value">{{ $invoice->customer->name }}</td>
                    </tr>
                    @if($invoice->customer->street_address)
                    <tr><td>Customer Address:</td><td>{{ $invoice->customer->street_address }}</td></tr>
                    @endif
                    @if($invoice->customer->city)
                    <tr><td>Customer Street:</td><td>{{ $invoice->customer->city }}{{ $invoice->customer->country ? ", ".$invoice->customer->country : "" }}</td></tr>
                    @endif
                    @if($invoice->customer->email)
                        <tr><td>Customer Email:</td><td>{{ $invoice->customer?->email }}</td></tr>
                    @endif
                    @if($invoice->customer->phonenumber)
                        <tr><td>Customer Phone:</td><td>{{ $invoice->customer?->phonenumber }}</td></tr>
                    @endif
                    @if($invoice->customer->vat_number)
                        <tr><td>Customer VAT:</td><td>{{ $invoice->customer?->vat_number }}</td></tr>
                    @endif
                    @if($invoice->customer->tin_number)
                        <tr><td>Customer TIN:</td><td>{{ $invoice->customer?->tin_number }}</td></tr>
                    @endif
                   
                </table>
            </div>

            {{-- LINE ITEMS --}}
            <table class="items-table">
                <thead>
                    <tr>
                        <th>HS Code</th>
                        <th>Date</th>
                        <th>Waybill No</th>
                        <th>LTI No</th>
                        <th>Origin</th>
                        <th>Destination</th>
                        <th>Reg No.</th>
                        <th>Truck Capacity</th>
                        <th>Delivered Tonnage (MT)</th>
                        <th>Distance (km)</th>
                        <th>Rate /KM($)</th>
                        <th>Loading /Offloading Fee</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice_items as $item)
                    <tr>
                        <td>{{ $item->hs_code }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->trip?->start_date)->format('d/m/Y') }}</td>
                        <td>
                            @foreach ($item->trip?->trip_transport_orders ?? [] as $tto)
                                {{$tto->delivery_note?->document_number}}
                            @endforeach
                        </td>
                        <td>{{ $item->lti_number }}</td>
                        <td class="text-left">{{ $item->origin }}</td>
                        <td class="text-left">{{ $item->destination }}</td>
                        <td>{{ $item->registration_number }}</td>
                        <td>{{ number_format($item->truck_capacity, 2) }}</td>
                        <td>{{ number_format($item->delivered_tonnage, 2) }}</td>
                        <td>{{ number_format($item->distance, 0) }}</td>
                        <td>{{ number_format($item->rate_per_km, 2) }}</td>
                        <td>{{ number_format($item->loading_fee, 2) }}</td>
                        <td>{{ number_format($item->amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- TOTALS --}}
            <div class="totals-section">
                <table class="totals-table">
                    <tr>
                        <td>Total VAT Exc</td>
                        <td>{{ number_format($invoice->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td>VAT {{ $invoice->vat_rate }}%</td>
                        <td>{{ number_format($invoice->vat_amount, 2) }}</td>
                    </tr>
                    <tr class="total-final">
                        <td>Total VAT Inc</td>
                        <td>{{ number_format($invoice->total_amount, 2) }}</td>
                    </tr>
                </table>
            </div>

            {{-- FOOTER --}}
            <div class="invoice-footer">
                <div class="footer-grid">
                    <div>
                        <div class="section-label">Prepared By</div>
                        <p>{{ $invoice->prepared_by }} ({{ $invoice->prepared_by_phone }})</p>
                        <br>
                        <div class="section-label">Company Stamp</div>
                        <div class="stamp-box">Company Stamp (Date)</div>
                    </div>
                    <div>
                        <div class="section-label">Banking Details</div>
                        <p><strong>Account Name:</strong> {{ $company->bank_account_name }}</p>
                        <p><strong>Bank:</strong> {{ $company->bank_name }}</p>
                        <p><strong>Branch:</strong> {{ $company->bank_branch }}</p>
                        <p><strong>Sort Code:</strong> {{ $company->bank_sort_code }}</p>
                        @if($company->bank_account_zwl)
                        <p><strong>Account No ZWL:</strong> {{ $company->bank_account_zwl }}</p>
                        @endif
                        @if($company->bank_account_usd)
                        <p><strong>Account No USD:</strong> {{ $company->bank_account_usd }}</p>
                        @endif
                        @if($company->swift_code)
                        <p><strong>Swift Code:</strong> {{ $company->swift_code }}</p>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- PRINT SCRIPT --}}
    <script>
        function printSection() {
            const contents = document.getElementById('printable-invoice').innerHTML;
            const original = document.body.innerHTML;
            document.body.innerHTML = contents;
            window.print();
            document.body.innerHTML = original;
            window.location.reload(); // restore Livewire
        }

        function goBack() {
            window.history.back();
        }
    </script>
</div>