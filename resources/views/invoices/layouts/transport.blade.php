{{--
    TRANSPORT invoice document — the single source for screen, browser print and
    Export as PDF. Styles: invoices.layouts.transport-styles (table based for dompdf).
    Trip lines show the trip's waybills, route, truck, tonnage and distance;
    other lines show their description. Expects $invoice, $company,
    $invoice_items, optional $fiscalDocument and $pdf.
--}}
@php
    $fiscal   = (! empty($fiscalDocument) && $fiscalDocument->success) ? $fiscalDocument : null;
    $fiscalQr = $fiscal ? \App\Support\FiscalQr::dataUri($fiscal->qr_code_url) : null;
    $symbol   = $invoice->currency?->symbol;
    $customer = $invoice->customer;

    $place = function ($point, $destinationId) {
        if ($point?->name) {
            return $point->name;
        }
        $destination = $destinationId ? \App\Models\Destination::find($destinationId) : null;
        return $destination?->city;
    };
@endphp
<div class="invoice-wrap">

    {{-- ── FISCAL BANNER (once fiscalised) ── --}}
    @if ($fiscal)
        <table class="fiscal-banner">
            <tr>
                @if ($fiscalQr)
                    <td class="qr"><img src="{{ $fiscalQr }}" width="76" height="76" alt="Fiscal QR"></td>
                @endif
                <td>
                    <div class="fiscal-title">FISCAL TAX INVOICE</div>
                    <div class="fiscal-text">
                        @if ($fiscal->ra_invoice_number) <strong>Fiscal Receipt No:</strong> {{ $fiscal->ra_invoice_number }} &nbsp;&nbsp; @endif
                        @if ($fiscal->fiscal_day) <strong>Fiscal Day:</strong> {{ $fiscal->fiscal_day }} &nbsp;&nbsp; @endif
                        @if ($fiscal->device_id) <strong>Fiscal Device ID:</strong> {{ $fiscal->device_id }} @endif
                        <br>
                        Invoice No: {{ $invoice->invoice_number }}
                        @if ($fiscal->fiscalized_at) &nbsp;&nbsp; Fiscalised: {{ $fiscal->fiscalized_at->format('d/m/Y H:i') }} @endif
                        <br>
                        @if ($fiscal->verification_code) <strong>Verification Code:</strong> {{ $fiscal->verification_code }}<br> @endif
                        @if ($fiscal->qr_code_url)
                            Verify at: <span class="fiscal-url">{{ $fiscal->qr_code_url }}</span>
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    @endif

    {{-- ── HEADER ── --}}
    <table class="header-row">
        <tr>
            <td class="company-block">
                @if ($company->logo)
                    <img src="{{ asset('images/uploads/' . $company->logo) }}" alt="{{ $company->name }}"><br>
                @endif
                <div class="company-name">{{ $company->name }}</div>
                @if ($company->street_address || $company->suburb)
                    <p>{{ $company->street_address }} {{ $company->suburb }}</p>
                @endif
                <p>{{ $company->city }}{{ $company->country ? ', ' . $company->country : '' }}</p>
                @if ($company->phonenumber || $company->second_phonenumber || $company->third_phonenumber)
                    <p>Tel: {{ collect([$company->phonenumber, $company->second_phonenumber, $company->third_phonenumber])->filter()->implode(' / ') }}</p>
                @endif
                @if ($company->email)<p><a href="mailto:{{ $company->email }}">{{ $company->email }}</a></p>@endif
            </td>
            <td class="invoice-meta">
                <div class="doc-title">{{ $invoice->showsFiscalHeading() ? 'FISCAL TAX INVOICE' : ($company->invoice_title ?: 'INVOICE') }}</div>
                <div class="invoice-number">No. {{ $invoice->invoice_number }}</div>
                <table>
                    <tr><td class="k">Date:</td><td class="v">{{ $invoice->date ? \Carbon\Carbon::parse($invoice->date)->format('d/m/Y') : '' }}</td></tr>
                    @if ($invoice->expiry)
                        <tr><td class="k">Due:</td><td class="v">{{ \Carbon\Carbon::parse($invoice->expiry)->format('d/m/Y') }}</td></tr>
                    @endif
                    <tr><td class="k">Currency:</td><td class="v">{{ $invoice->currency?->name }}</td></tr>
                    <tr><td class="k">VAT#:</td><td class="v">{{ $company->vat_number }}</td></tr>
                    <tr><td class="k">TIN#:</td><td class="v">{{ $company->tin_number }}</td></tr>
                    @if ($invoice->purchase_order_number)
                        <tr><td class="k">PO No:</td><td class="v">{{ $invoice->purchase_order_number }}</td></tr>
                    @endif
                    @if ($invoice->sales_order_number)
                        <tr><td class="k">SO No:</td><td class="v">{{ $invoice->sales_order_number }}</td></tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    {{-- ── CUSTOMER ── --}}
    <div class="customer-section">
        <table>
            <tr><td class="k">Customer Name:</td><td class="customer-name-value">{{ $customer?->name }}</td></tr>
            @if ($customer?->street_address || $customer?->suburb)
                <tr><td class="k">Customer Address:</td><td>{{ $customer->street_address }} {{ $customer->suburb }}</td></tr>
            @endif
            @if ($customer?->city)
                <tr><td class="k">City:</td><td>{{ $customer->city }}{{ $customer->country ? ', ' . $customer->country : '' }}</td></tr>
            @endif
            @if ($customer?->email)
                <tr><td class="k">Customer Email:</td><td>{{ $customer->email }}</td></tr>
            @endif
            @if ($customer?->phonenumber)
                <tr><td class="k">Customer Phone:</td><td>{{ $customer->phonenumber }}</td></tr>
            @endif
            @if ($customer?->vat_number)
                <tr><td class="k">Customer VAT:</td><td>{{ $customer->vat_number }}</td></tr>
            @endif
            @if ($customer?->tin_number)
                <tr><td class="k">Customer TIN:</td><td>{{ $customer->tin_number }}</td></tr>
            @endif
        </table>
    </div>

    {{-- ── LINE ITEMS ── --}}
    <table class="items-table">
        <thead>
            <tr>
                <th>HS Code</th>
                <th>Date</th>
                <th>Description</th>
                <th>Waybill No</th>
                <th>Origin</th>
                <th>Destination</th>
                <th>Reg No.</th>
                <th>Tonnage / Qty</th>
                <th>Distance (km)</th>
                <th>Rate</th>
                <th>Amount (Excl)</th>
                <th>VAT</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice_items as $item)
                @php $trip = $item->trip; @endphp
                <tr class="{{ $loop->even ? 'even' : '' }}">
                    <td>{{ $item->hs_code ?: $item->tax?->hs_code }}</td>
                    <td>{{ $trip?->start_date ? \Carbon\Carbon::parse($trip->start_date)->format('d/m/Y') : '' }}</td>
                    <td class="text-left">
                        @if ($item->product) {{ $item->product->name }}<br> @endif
                        {{ $item->description }}
                    </td>
                    <td>
                        @foreach ($trip?->trip_transport_orders ?? [] as $tto)
                            {{ $tto->delivery_note?->document_number }}
                        @endforeach
                    </td>
                    <td class="text-left">{{ $trip ? $place($trip->loading_point, $trip->from) : '' }}</td>
                    <td class="text-left">{{ $trip ? $place($trip->offloading_point, $trip->to) : '' }}</td>
                    <td>{{ $trip?->horse?->registration_number }}</td>
                    <td class="num">
                        @if ($trip && ($trip->offloaded_quantity || $trip->weight))
                            {{ number_format((float) ($trip->offloaded_quantity ?: $trip->weight), 2) }}
                        @else
                            {{ $item->qty }}
                        @endif
                    </td>
                    <td class="num">{{ $trip?->distance ? number_format((float) $trip->distance, 0) : '' }}</td>
                    <td class="num">{{ $item->amount ? $symbol . number_format($item->amount, 2) : '' }}</td>
                    <td class="num">{{ $item->subtotal ? $symbol . number_format($item->subtotal, 2) : '' }}</td>
                    <td class="num">{{ $symbol }}{{ number_format($invoice->tax_amount > 0 ? (float) $item->tax_amount : 0, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ── TOTALS ── --}}
    <table class="totals-section">
        <tr>
            <td class="spacer"></td>
            <td style="width:240px; padding:0">
                <table class="totals-table" style="width:100%">
                    <tr>
                        <td class="k">Total VAT Exc</td>
                        <td>{{ $symbol }}{{ number_format($invoice->invoice_items->whereNotNull('subtotal')->where('subtotal', '!=', '')->sum('subtotal'), 2) }}</td>
                    </tr>
                    <tr>
                        <td class="k">VAT</td>
                        <td>{{ $symbol }}{{ number_format($invoice->tax_amount > 0 ? $invoice->tax_amount : 0, 2) }}</td>
                    </tr>
                    @if ($invoice->discount)
                        <tr>
                            <td class="k">Discount {{ $invoice->discount->description }}</td>
                            <td>
                                @if ($invoice->discount->unit === 'currency')
                                    {{ $symbol }}{{ number_format($invoice->discount->amount ?? 0, 2) }}
                                @else
                                    {{ number_format($invoice->discount->amount ?? 0, 2) }} %
                                @endif
                            </td>
                        </tr>
                    @endif
                    <tr class="total-final">
                        <td>Total VAT Inc {{ $invoice->currency?->name }}</td>
                        <td>{{ $symbol }}{{ number_format((float) $invoice->total, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    @if ($invoice->memo)
        <div class="notes">
            <div class="section-label">Notes / Terms &amp; Conditions</div>
            {{ $invoice->memo }}
        </div>
    @endif

    {{-- ── FOOTER ── --}}
    <div class="invoice-footer">
        <table class="footer-grid">
            <tr>
                <td>
                    <div class="section-label">Prepared By</div>
                    <p>{{ $invoice->user?->name }} {{ $invoice->user?->surname }}</p>
                    <br>
                    <div class="section-label">Company Stamp</div>
                    <div class="stamp-box">Company Stamp (Date)</div>
                </td>
                <td>
                    @if ($invoice->bank_accounts->count() > 0)
                        <div class="section-label">Banking Details</div>
                        @foreach ($invoice->bank_accounts as $bank_account)
                            <p style="margin-bottom:6px">
                                @if ($bank_account->account_name) <strong>Account Name:</strong> {{ $bank_account->account_name }}<br> @endif
                                @if ($bank_account->name) <strong>Bank:</strong> {{ $bank_account->name }}<br> @endif
                                @if ($bank_account->branch) <strong>Branch:</strong> {{ $bank_account->branch }}<br> @endif
                                @if ($bank_account->branch_code) <strong>Branch Code:</strong> {{ $bank_account->branch_code }}<br> @endif
                                @if ($bank_account->account_number) <strong>Account No:</strong> {{ $bank_account->account_number }}<br> @endif
                                @if ($bank_account->swift_code) <strong>Swift Code:</strong> {{ $bank_account->swift_code }} @endif
                            </p>
                        @endforeach
                    @endif
                </td>
            </tr>
        </table>
        @if ($invoice->footer || ($company->show_branding ?? true))
            <div class="doc-footer-text">
                {{ $invoice->footer }}
                @if ($company->show_branding ?? true)
                    <br>Powered by <img src="{{ asset('images/logo.png') }}" alt="Gonyeti" style="height:12px; vertical-align:middle">
                @endif
            </div>
        @endif
    </div>

</div>
