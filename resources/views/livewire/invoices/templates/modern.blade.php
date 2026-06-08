{{-- resources/views/livewire/invoice-modern.blade.php --}}

<div>
    <div class="inv-wrap">

    {{-- ── DARK HEADER ── --}}
    <div class="inv-header">
        <div class="inv-header-left">
            <div class="inv-logo-wrap">
                <img src="{{ asset('images/uploads/' . $company->logo) }}" alt="{{ $company->name }}">
            </div>
            <div class="inv-coname">{{ $company->name }}</div>
            <div class="inv-codetail">
                {{ $company->street_address }} {{ $company->suburb }}, {{ $company->city }}, {{ $company->country }}<br>
                {{ $company->phonenumber }}
                @if($company->second_phonenumber) &nbsp;|&nbsp; {{ $company->second_phonenumber }} @endif
                @if($company->third_phonenumber) &nbsp;|&nbsp; {{ $company->third_phonenumber }} @endif<br>
                <a href="mailto:{{ $company->email }}">{{ $company->email }}</a>
                @if($company->second_email) &nbsp;|&nbsp; {{ $company->second_email }} @endif<br>
                VAT No: {{ $company->vat_number }} &nbsp;|&nbsp; TIN: {{ $company->tin_number }}
                @if($company->vendor_number) &nbsp;|&nbsp; Vendor No: {{ $company->vendor_number }} @endif
            </div>
        </div>
        <div class="inv-header-right">
            <div class="inv-doc-type">{{ $invoice->fiscalize ? 'FISCAL TAX INVOICE' : $company->invoice_title }}</div>
            <div class="inv-number">{{ $invoice->invoice_number }}</div>
            <div class="inv-meta-pills">
                <div class="inv-pill"><span>Date:</span>{{ $invoice->date }}</div>
                @if($invoice->expiry)
                    <div class="inv-pill"><span>Due:</span>{{ $invoice->expiry }}</div>
                @endif
                <div class="inv-pill"><span>Currency:</span>{{ $invoice->currency?->name }}</div>
                @if($invoice->purchase_order_number)
                    <div class="inv-pill"><span>P.O:</span>{{ $invoice->purchase_order_number }}</div>
                @endif
                @if($invoice->sales_order_number)
                    <div class="inv-pill"><span>S.O:</span>{{ $invoice->sales_order_number }}</div>
                @endif
                @if($invoice->pat_number)
                    <div class="inv-pill"><span>PAT:</span>{{ $invoice->pat_number }}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- ── ACCENT STRIPE (uses company colour) ── --}}
    <div class="inv-accent"></div>

    <div class="inv-body">

        {{-- ── BILLING + FISCAL DETAILS ── --}}
        <div class="inv-billing-row">
            <div class="inv-billing-box">
                <div class="inv-billing-box-hdr">Bill To</div>
                <div class="inv-billing-box-body">
                    <div class="cname">{{ $invoice->customer?->name }}</div>
                    @if($invoice->customer?->street_address || $invoice->customer?->suburb)
                        <div class="row"><span class="lbl">Address:</span><span>{{ $invoice->customer->street_address }} {{ $invoice->customer->suburb }}, {{ $invoice->customer->city }} {{ $invoice->customer->country }}</span></div>
                    @endif
                    @if($invoice->customer?->email)
                        <div class="row"><span class="lbl">Email:</span><span>{{ $invoice->customer->email }}</span></div>
                    @endif
                    @if($invoice->customer?->phonenumber)
                        <div class="row"><span class="lbl">Phone:</span><span>{{ $invoice->customer->phonenumber }}</span></div>
                    @endif
                    @if($invoice->customer?->vat_number)
                        <div class="row"><span class="lbl">VAT No:</span><span>{{ $invoice->customer->vat_number }}</span></div>
                    @endif
                    @if($invoice->customer?->tin_number)
                        <div class="row"><span class="lbl">TIN No:</span><span>{{ $invoice->customer->tin_number }}</span></div>
                    @endif
                </div>
            </div>

            @if($invoice->fiscalize)
            <div class="inv-billing-box">
                <div class="inv-billing-box-hdr">Fiscal Details</div>
                <div class="inv-billing-box-body">
                    @if($fiscalDay)
                        <div class="row"><span class="lbl">Fiscal Day:</span><span>{{ $fiscalDay }}</span></div>
                    @endif
                    @if($fiscalRaInvoiceNo)
                        <div class="row"><span class="lbl">RA Invoice:</span><span>{{ $fiscalRaInvoiceNo }}</span></div>
                    @endif
                    @if($fiscalVerifyCode)
                        <div class="row"><span class="lbl">Verify Code:</span><span style="font-family:monospace;font-weight:700">{{ $fiscalVerifyCode }}</span></div>
                    @endif
                    @if($fiscalQrUrl)
                        <div class="row" style="margin-top:4px">
                            <span class="lbl"></span>
                            <span>
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=60x60&data={{ urlencode($fiscalQrUrl) }}"
                                     width="60" height="60" alt="Fiscal QR"
                                     style="border:1px solid #ddd;border-radius:3px">
                            </span>
                        </div>
                        <div class="row" style="font-size:8.5px;color:#888">
                            <span class="lbl"></span><span>Verify at fdms.zimra.co.zw</span>
                        </div>
                    @endif
                </div>
            </div>
            @else
            <div class="inv-billing-box">
                <div class="inv-billing-box-hdr">Invoice Details</div>
                <div class="inv-billing-box-body">
                    <div class="row"><span class="lbl">Invoice No:</span><span>{{ $invoice->invoice_number }}</span></div>
                    <div class="row"><span class="lbl">Date:</span><span>{{ $invoice->date }}</span></div>
                    @if($invoice->expiry)
                        <div class="row"><span class="lbl">Due Date:</span><span>{{ $invoice->expiry }}</span></div>
                    @endif
                    <div class="row"><span class="lbl">Currency:</span><span>{{ $invoice->currency?->name }}</span></div>
                </div>
            </div>
            @endif
        </div>

        {{-- ── LINE ITEMS ── --}}
        <table class="inv-items">
            <thead>
                <tr>
                    @if(!$company->hide_description)
                        <th style="text-align:left">{{ $company->items_column }}</th>
                    @endif
                    <th>HS Code</th>
                    @if(!$company->hide_quantity)
                        <th>{{ $company->units_column }}</th>
                    @endif
                    @if(!$company->hide_price)
                        <th>{{ $company->price_column }}</th>
                    @endif
                    @if(!$company->hide_amount)
                        <th>{{ $company->total_column }} <small>(Excl)</small></th>
                    @endif
                    <th>VAT Amount</th>
                    <th>Total <small>(Incl)</small></th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice_items as $invoice_item)
                    @php $tax = $invoice_item->tax; @endphp
                    <tr>
                        @if(!$company->hide_description)
                            <td style="text-align:left">
                                @if($invoice_item->product)
                                    {{ $invoice_item->product->name }}<br>
                                @endif
                                {{ $invoice_item->description }}
                            </td>
                        @endif
                        <td>{{ $tax?->hs_code }}</td>
                        @if(!$company->hide_quantity)
                            <td>{{ $invoice_item->qty }}</td>
                        @endif
                        @if(!$company->hide_price)
                            <td>
                                @if($invoice_item->amount)
                                    {{ $invoice->currency?->symbol }}{{ number_format($invoice_item->amount, 2) }}
                                @endif
                            </td>
                        @endif
                        @if(!$company->hide_amount)
                            <td>
                                @if($invoice_item->subtotal)
                                    {{ $invoice->currency?->symbol }}{{ number_format($invoice_item->subtotal, 2) }}
                                @endif
                            </td>
                        @endif
                        <td>
                            {{ $invoice->currency?->symbol }}{{ number_format($invoice->tax_amount > 0 ? $invoice_item->tax_amount : 0, 2) }}
                        </td>
                        <td>
                            @if(isset($invoice_item->subtotal_incl))
                                {{ $invoice->currency?->symbol }}{{ number_format($invoice_item->subtotal_incl, 2) }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="subtotal">
                    <td colspan="{{ 3 + (!$company->hide_quantity ? 1 : 0) + (!$company->hide_price ? 1 : 0) }}"></td>
                    <td>Sub-total {{ $invoice->currency?->name }} <small>(Excl)</small></td>
                    <td>{{ $invoice->currency?->symbol }}{{ number_format($invoice->invoice_items->whereNotNull('subtotal')->where('subtotal','!=','')->sum('subtotal'), 2) }}</td>
                </tr>
                <tr>
                    <td colspan="{{ 3 + (!$company->hide_quantity ? 1 : 0) + (!$company->hide_price ? 1 : 0) }}"></td>
                    <td>VAT Total</td>
                    <td>{{ $invoice->currency?->symbol }}{{ number_format($invoice->tax_amount > 0 ? $invoice->tax_amount : 0, 2) }}</td>
                </tr>
                @if($invoice->discount)
                    <tr>
                        <td colspan="{{ 3 + (!$company->hide_quantity ? 1 : 0) + (!$company->hide_price ? 1 : 0) }}"></td>
                        <td>Discount {{ $invoice->discount->description }}</td>
                        <td>
                            @if($invoice->discount->unit === 'currency')
                                {{ $invoice->currency?->symbol }}{{ number_format($invoice->discount->amount ?? 0, 2) }}
                            @else
                                {{ number_format($invoice->discount->amount ?? 0, 2) }} %
                            @endif
                        </td>
                    </tr>
                @endif
                <tr class="grand">
                    <td colspan="{{ 3 + (!$company->hide_quantity ? 1 : 0) + (!$company->hide_price ? 1 : 0) }}"></td>
                    <td>Invoice Total {{ $invoice->currency?->name }}</td>
                    <td>
                        @if($invoice->total)
                            {{ $invoice->currency?->symbol }}{{ number_format($invoice->total, 2) }}
                        @endif
                    </td>
                </tr>
            </tfoot>
        </table>

        {{-- ── NOTES + BANKING ── --}}
        <div class="inv-bottom">
            <div>
                @if($invoice->memo)
                    <div class="inv-section-label">Notes / Terms &amp; Conditions</div>
                    <div class="inv-notes">{{ $invoice->memo }}</div>
                @endif
            </div>
            <div>
                @if($invoice->bank_accounts->count() > 0)
                    <div class="inv-section-label">Banking Details</div>
                    @foreach($invoice->bank_accounts as $bank_account)
                        <div class="inv-bank">
                            @if($bank_account->name) <div><strong>Bank:</strong> {{ $bank_account->name }}</div> @endif
                            @if($bank_account->branch) <div><strong>Branch:</strong> {{ $bank_account->branch }}</div> @endif
                            @if($bank_account->branch_code) <div><strong>Branch Code:</strong> {{ $bank_account->branch_code }}</div> @endif
                            @if($bank_account->swift_code) <div><strong>Swift:</strong> {{ $bank_account->swift_code }}</div> @endif
                            @if($bank_account->account_name) <div><strong>Acc Name:</strong> {{ $bank_account->account_name }}</div> @endif
                            @if($bank_account->account_number) <div><strong>Acc No:</strong> {{ $bank_account->account_number }}</div> @endif
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        {{-- ── FOOTER ── --}}
        <div class="inv-doc-footer">
            <div>{{ $invoice->footer }}</div>
            <div class="inv-footer-brand">
                <div class="dot"></div>
                <span>Powered by</span>
                <img src="{{ asset('images/basilmark-logo.png') }}" alt="Basilmark" style="height:14px">
            </div>
        </div>

    </div>
</div>
</div>
