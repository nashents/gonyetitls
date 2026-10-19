{{--
    MODERN invoice document — the single source for screen, browser print and
    Export as PDF. Styles: invoices.layouts.modern-styles (table based for dompdf).
    Expects $invoice, $company, $invoice_items, optional $fiscalDocument and $pdf.
--}}
@php
    $symbol    = $invoice->currency?->symbol;
    $fiscal    = (! empty($fiscalDocument) && $fiscalDocument->success) ? $fiscalDocument : null;
    $fiscalQr  = $fiscal ? \App\Support\FiscalQr::dataUri($fiscal->qr_code_url) : null;
    $labelCols = 3 + (! $company->hide_quantity ? 1 : 0) + (! $company->hide_price ? 1 : 0)
                   - ($company->hide_description ? 1 : 0) - ($company->hide_amount ? 1 : 0);
@endphp
<div class="inv-wrap">

    {{-- ── DARK HEADER ── --}}
    <table class="inv-header">
        <tr>
            <td>
                <div class="inv-logo-wrap">
                    <img src="{{ asset('images/uploads/' . $company->logo) }}" alt="{{ $company->name }}">
                </div>
                <div class="inv-coname">{{ $company->name }}</div>
                <div class="inv-codetail">
                    {{ $company->street_address }} {{ $company->suburb }}, {{ $company->city }}, {{ $company->country }}<br>
                    {{ $company->phonenumber }}
                    @if ($company->second_phonenumber) &nbsp;|&nbsp; {{ $company->second_phonenumber }} @endif
                    @if ($company->third_phonenumber) &nbsp;|&nbsp; {{ $company->third_phonenumber }} @endif<br>
                    <a href="mailto:{{ $company->email }}">{{ $company->email }}</a>
                    @if ($company->second_email) &nbsp;|&nbsp; {{ $company->second_email }} @endif<br>
                    VAT No: {{ $company->vat_number }} &nbsp;|&nbsp; TIN: {{ $company->tin_number }}
                    @if ($company->vendor_number) &nbsp;|&nbsp; Vendor No: {{ $company->vendor_number }} @endif
                </div>
            </td>
            <td class="inv-header-right">
                <div class="inv-doc-type">{{ $invoice->showsFiscalHeading() ? 'FISCAL TAX INVOICE' : $company->invoice_title }}</div>
                <div class="inv-number">{{ $invoice->invoice_number }}</div>
                <div class="inv-pill-row"><span class="inv-pill"><span>Date:</span>{{ $invoice->date }}</span></div>
                @if ($invoice->expiry)
                    <div class="inv-pill-row"><span class="inv-pill"><span>Due:</span>{{ $invoice->expiry }}</span></div>
                @endif
                <div class="inv-pill-row"><span class="inv-pill"><span>Currency:</span>{{ $invoice->currency?->name }}</span></div>
                @if ($invoice->purchase_order_number)
                    <div class="inv-pill-row"><span class="inv-pill"><span>P.O:</span>{{ $invoice->purchase_order_number }}</span></div>
                @endif
                @if ($invoice->sales_order_number)
                    <div class="inv-pill-row"><span class="inv-pill"><span>S.O:</span>{{ $invoice->sales_order_number }}</span></div>
                @endif
                @if ($invoice->pat_number)
                    <div class="inv-pill-row"><span class="inv-pill"><span>PAT:</span>{{ $invoice->pat_number }}</span></div>
                @endif
            </td>
        </tr>
    </table>

    {{-- ── ACCENT STRIPE (company colour) ── --}}
    <div class="inv-accent">&nbsp;</div>

    <div class="inv-body">

        {{-- ── BILLING + FISCAL / INVOICE DETAILS ── --}}
        <table class="inv-billing-row">
            <tr>
                <td>
                    <div class="inv-billing-box">
                        <div class="inv-billing-box-hdr">Bill To</div>
                        <div class="inv-billing-box-body">
                            <div class="cname">{{ $invoice->customer?->name }}</div>
                            <table class="inv-kv">
                                @if ($invoice->customer?->street_address || $invoice->customer?->suburb)
                                    <tr><td class="lbl">Address:</td><td>{{ $invoice->customer->street_address }} {{ $invoice->customer->suburb }}, {{ $invoice->customer->city }} {{ $invoice->customer->country }}</td></tr>
                                @endif
                                @if ($invoice->customer?->email)
                                    <tr><td class="lbl">Email:</td><td>{{ $invoice->customer->email }}</td></tr>
                                @endif
                                @if ($invoice->customer?->phonenumber)
                                    <tr><td class="lbl">Phone:</td><td>{{ $invoice->customer->phonenumber }}</td></tr>
                                @endif
                                @if ($invoice->customer?->vat_number)
                                    <tr><td class="lbl">VAT No:</td><td>{{ $invoice->customer->vat_number }}</td></tr>
                                @endif
                                @if ($invoice->customer?->tin_number)
                                    <tr><td class="lbl">TIN No:</td><td>{{ $invoice->customer->tin_number }}</td></tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </td>
                <td class="inv-gap"></td>
                <td>
                    @if ($fiscal)
                        <div class="inv-billing-box">
                            <div class="inv-billing-box-hdr">Fiscal Details</div>
                            <div class="inv-billing-box-body">
                                <table class="inv-kv">
                                    <tr>
                                        <td style="padding:0">
                                            <table class="inv-kv">
                                                @if ($fiscal->verification_code)
                                                    <tr><td class="lbl">Verify Code:</td><td class="mono">{{ $fiscal->verification_code }}</td></tr>
                                                @endif
                                                @if ($fiscal->ra_invoice_number)
                                                    <tr><td class="lbl">Receipt No:</td><td>{{ $fiscal->ra_invoice_number }}</td></tr>
                                                @endif
                                                @if ($fiscal->fiscal_day)
                                                    <tr><td class="lbl">Fiscal Day:</td><td>{{ $fiscal->fiscal_day }}</td></tr>
                                                @endif
                                                @if ($fiscal->device_id)
                                                    <tr><td class="lbl">Device ID:</td><td>{{ $fiscal->device_id }}</td></tr>
                                                @endif
                                                @if ($fiscal->fiscalized_at)
                                                    <tr><td class="lbl">Fiscalised:</td><td>{{ $fiscal->fiscalized_at->format('d M Y H:i') }}</td></tr>
                                                @endif
                                            </table>
                                        </td>
                                        @if ($fiscalQr)
                                            <td style="width:76px; text-align:right; padding:0">
                                                <img src="{{ $fiscalQr }}" width="70" height="70" alt="Fiscal QR" class="inv-qr">
                                            </td>
                                        @endif
                                    </tr>
                                </table>
                                @if ($fiscal->qr_code_url)
                                    <div class="inv-qr-note">Verify at: {{ $fiscal->qr_code_url }}</div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="inv-billing-box">
                            <div class="inv-billing-box-hdr">Invoice Details</div>
                            <div class="inv-billing-box-body">
                                <table class="inv-kv">
                                    <tr><td class="lbl">Invoice No:</td><td>{{ $invoice->invoice_number }}</td></tr>
                                    <tr><td class="lbl">Date:</td><td>{{ $invoice->date }}</td></tr>
                                    @if ($invoice->expiry)
                                        <tr><td class="lbl">Due Date:</td><td>{{ $invoice->expiry }}</td></tr>
                                    @endif
                                    <tr><td class="lbl">Currency:</td><td>{{ $invoice->currency?->name }}</td></tr>
                                </table>
                            </div>
                        </div>
                    @endif
                </td>
            </tr>
        </table>

        {{-- ── LINE ITEMS ── --}}
        <table class="inv-items">
            <thead>
                <tr>
                    @if (! $company->hide_description)
                        <th class="left">{{ $company->items_column }}</th>
                    @endif
                    <th>HS Code</th>
                    @if (! $company->hide_quantity)
                        <th>{{ $company->units_column }}</th>
                    @endif
                    @if (! $company->hide_price)
                        <th>{{ $company->price_column }}</th>
                    @endif
                    @if (! $company->hide_amount)
                        <th>{{ $company->total_column }} <small>(Excl)</small></th>
                    @endif
                    <th>VAT Amount</th>
                    <th>Total <small>(Incl)</small></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice_items as $invoice_item)
                    @php $tax = $invoice_item->tax; @endphp
                    <tr class="{{ $loop->even ? 'even' : '' }}">
                        @if (! $company->hide_description)
                            <td class="left">
                                @if ($invoice_item->product)
                                    {{ $invoice_item->product->name }}<br>
                                @endif
                                {{ $invoice_item->description }}
                            </td>
                        @endif
                        <td>{{ $invoice_item->hs_code ?: $tax?->hs_code }}</td>
                        @if (! $company->hide_quantity)
                            <td>{{ $invoice_item->qty }}</td>
                        @endif
                        @if (! $company->hide_price)
                            <td>
                                @if ($invoice_item->amount)
                                    {{ $symbol }}{{ number_format($invoice_item->amount, 2) }}
                                @endif
                            </td>
                        @endif
                        @if (! $company->hide_amount)
                            <td>
                                @if ($invoice_item->subtotal)
                                    {{ $symbol }}{{ number_format($invoice_item->subtotal, 2) }}
                                @endif
                            </td>
                        @endif
                        <td>{{ $symbol }}{{ number_format($invoice->tax_amount > 0 ? $invoice_item->tax_amount : 0, 2) }}</td>
                        <td>
                            @if (isset($invoice_item->subtotal_incl))
                                {{ $symbol }}{{ number_format($invoice_item->subtotal_incl, 2) }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="subtotal">
                    <td colspan="{{ $labelCols }}"></td>
                    <td>Sub-total {{ $invoice->currency?->name }} <small>(Excl)</small></td>
                    <td>{{ $symbol }}{{ number_format($invoice->invoice_items->whereNotNull('subtotal')->where('subtotal', '!=', '')->sum('subtotal'), 2) }}</td>
                </tr>
                <tr>
                    <td colspan="{{ $labelCols }}"></td>
                    <td>VAT Total</td>
                    <td>{{ $symbol }}{{ number_format($invoice->tax_amount > 0 ? $invoice->tax_amount : 0, 2) }}</td>
                </tr>
                @if ($invoice->discount)
                    <tr>
                        <td colspan="{{ $labelCols }}"></td>
                        <td>Discount {{ $invoice->discount->description }}</td>
                        <td>
                            @if ($invoice->discount->unit === 'currency')
                                {{ $symbol }}{{ number_format($invoice->discount->amount ?? 0, 2) }}
                            @else
                                {{ number_format($invoice->discount->amount ?? 0, 2) }} %
                            @endif
                        </td>
                    </tr>
                @endif
                <tr class="grand">
                    <td colspan="{{ $labelCols }}"></td>
                    <td>Invoice Total {{ $invoice->currency?->name }}</td>
                    <td>
                        @if ($invoice->total)
                            {{ $symbol }}{{ number_format($invoice->total, 2) }}
                        @endif
                    </td>
                </tr>
            </tfoot>
        </table>

        {{-- ── NOTES + BANKING ── --}}
        <table class="inv-bottom">
            <tr>
                <td>
                    @if ($invoice->memo)
                        <div class="inv-section-label">Notes / Terms &amp; Conditions</div>
                        <div class="inv-notes">{{ $invoice->memo }}</div>
                    @endif
                </td>
                <td>
                    @if ($invoice->bank_accounts->count() > 0)
                        <div class="inv-section-label">Banking Details</div>
                        @foreach ($invoice->bank_accounts as $bank_account)
                            <div class="inv-bank">
                                @if ($bank_account->name) <div><strong>Bank:</strong> {{ $bank_account->name }}</div> @endif
                                @if ($bank_account->branch) <div><strong>Branch:</strong> {{ $bank_account->branch }}</div> @endif
                                @if ($bank_account->branch_code) <div><strong>Branch Code:</strong> {{ $bank_account->branch_code }}</div> @endif
                                @if ($bank_account->swift_code) <div><strong>Swift:</strong> {{ $bank_account->swift_code }}</div> @endif
                                @if ($bank_account->account_name) <div><strong>Acc Name:</strong> {{ $bank_account->account_name }}</div> @endif
                                @if ($bank_account->account_number) <div><strong>Acc No:</strong> {{ $bank_account->account_number }}</div> @endif
                            </div>
                        @endforeach
                    @endif
                </td>
            </tr>
        </table>

        {{-- ── FOOTER ── --}}
        <table class="inv-doc-footer">
            <tr>
                <td>{{ $invoice->footer }}</td>
                @if ($company->show_branding ?? true)
                    <td class="inv-footer-brand">
                        <span class="dot"></span>
                        <span>Powered by</span>
                        <img src="{{ asset('images/logo.png') }}" alt="Gonyeti" style="height:14px; vertical-align:middle">
                    </td>
                @endif
            </tr>
        </table>

    </div>
</div>
