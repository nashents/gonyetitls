<div>
    <form wire:submit.prevent="update()">
            <div class="form-group">
                <label for="color">Company Color</label>
                <input type="color" class="form-control"  wire:model.debounce.300ms="color" class="form-control">
                @error('color') <span class="error" style="color:red">{{ $message }}</span> @enderror
            </div>
               <div class="row">
                <div class="col-md-6">
                    <div class="mb-10 mt-10">
                        <input type="checkbox" wire:model.debounce.300ms="invoice_serialize_by_customer"   class="line-style" />
                        <label for="one" class="radio-label">Serialize Invoices By Customer</label>
                        @error('invoice_serialize_by_customer') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-10 mt-10">
                        <input type="checkbox" wire:model.debounce.300ms="quotation_serialize_by_customer"   class="line-style" />
                        <label for="one" class="radio-label">Serialize Quotations By Customer</label>
                        @error('quotation_serialize_by_customer') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>
                </div>
               </div>
               
               <h5 class="underline mt-5">Invoices</h5>

               {{-- ── INVOICE TEMPLATE SELECTOR (3 templates) ──────────────────────────── --}}
                <div class="form-group mt-3 mb-4">
                    <label class="d-block mb-1"><strong>Invoice Template</strong></label>
                    <small class="text-muted d-block mb-3">Select the invoice layout used when previewing and printing invoices.</small>

                    <style>
                        .tpl-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
                        .tpl-card { border: 2px solid #dee2e6; border-radius: 8px; overflow: hidden; cursor: pointer; transition: border-color 0.15s; }
                        .tpl-card.tpl-selected { border-color: #0d6efd; }
                        .tpl-card-header { padding: 7px 12px; display: flex; align-items: center; justify-content: space-between; background: #f8f9fa; border-bottom: 1px solid #dee2e6; }
                        .tpl-card-header .tpl-name { font-size: 12px; font-weight: 600; }
                        .tpl-active-badge { display: none; font-size: 10px; padding: 1px 8px; border-radius: 999px; background: #cfe2ff; color: #084298; }
                        .tpl-card.tpl-selected .tpl-active-badge { display: inline-block; }
                        .tpl-preview-area { background: #fff; padding: 8px; overflow: hidden; }
                        .tpl-footer { padding: 7px 12px; display: flex; align-items: flex-start; gap: 7px; border-top: 1px solid #dee2e6; background: #f8f9fa; }
                        .tpl-footer label { font-size: 11px; font-weight: 600; cursor: pointer; margin-bottom: 1px; display: block; }
                        .tpl-footer small { font-size: 10px; color: #6c757d; line-height: 1.4; display: block; }

                        /* shared preview base */
                        .inv-p { font-family: Arial, sans-serif; font-size: 7px; color: #1a1a1a; line-height: 1.4; }
                        .inv-p-table { width: 100%; border-collapse: collapse; font-size: 6px; margin-bottom: 5px; }
                        .inv-p-table thead tr { background: #1a1a1a; color: #fff; }
                        .inv-p-table th { padding: 2px 3px; text-align: right; }
                        .inv-p-table th:first-child { text-align: left; }
                        .inv-p-table td { padding: 2px 3px; text-align: right; border-bottom: .5px solid #ececec; }
                        .inv-p-table td:first-child { text-align: left; }
                        .inv-p-table tfoot .total-row td { background: #1a1a1a; color: #fff; font-weight: 700; }

                        /* Classic preview */
                        .p-cls-hdr { display: flex; justify-content: space-between; margin-bottom: 5px; }
                        .p-cls-coname { font-size: 8px; font-weight: 700; color: #185FA5; margin-bottom: 1px; }
                        .p-cls-codetail { font-size: 6px; color: #444; line-height: 1.5; }
                        .p-cls-title { text-align: center; font-size: 9px; font-weight: 700; margin: 4px 0 1px; }
                        .p-cls-subtitle { text-align: center; font-size: 6px; color: #666; margin-bottom: 5px; }
                        .p-cls-billing { display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 6px; }

                        /* Transport preview */
                        .p-trp-qr { border: 1px solid #1a1a1a; padding: 4px 5px; display: flex; gap: 5px; margin-bottom: 5px; }
                        .p-trp-qrbox { width: 22px; height: 22px; background: #eee; border: 1px solid #ccc; display: flex; align-items: center; justify-content: center; font-size: 5px; color: #999; flex-shrink: 0; }

                        /* Modern preview */
                        .p-mod-hdr { background: #1a1a1a; color: #fff; padding: 8px 9px; display: flex; justify-content: space-between; align-items: center; margin: -8px -8px 0; }
                        .p-mod-accent { height: 3px; background: #185FA5; margin: 0 -8px 6px; }
                        .p-mod-coname { font-size: 8px; font-weight: 700; color: #fff; margin-bottom: 2px; }
                        .p-mod-codetail { font-size: 5.5px; color: rgba(255,255,255,0.65); line-height: 1.5; }
                        .p-mod-doctype { font-size: 11px; font-weight: 700; color: #fff; margin-bottom: 2px; }
                        .p-mod-invno { font-size: 6.5px; color: rgba(255,255,255,0.75); margin-bottom: 4px; }
                        .p-mod-pills { display: flex; flex-direction: column; gap: 2px; align-items: flex-end; }
                        .p-mod-pill { background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.2); border-radius: 999px; padding: 1px 6px; font-size: 5.5px; color: #fff; white-space: nowrap; }
                        .p-mod-pill span { color: rgba(255,255,255,0.5); margin-right: 3px; }
                        .p-mod-billing { display: grid; grid-template-columns: 1fr 1fr; gap: 5px; margin: 6px 0; }
                        .p-mod-box { border: .5px solid #ddd; border-radius: 3px; overflow: hidden; }
                        .p-mod-boxhdr { background: #f4f4f4; padding: 2px 5px; font-size: 5.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .3px; color: #555; border-bottom: .5px solid #ddd; }
                        .p-mod-boxbody { padding: 4px 5px; font-size: 6px; line-height: 1.7; color: #333; }
                        .p-mod-boxbody .cname { font-weight: 700; color: #1a1a1a; font-size: 7px; }
                        .p-mod-bottom { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; margin-top: 4px; font-size: 6px; color: #333; line-height: 1.7; }
                        .p-mod-slabel { font-size: 5.5px; font-weight: 700; text-transform: uppercase; color: #999; letter-spacing: .3px; margin-bottom: 2px; }
                    </style>

                    <div class="tpl-grid">

                        {{-- ── TEMPLATE 1: CLASSIC ──────────────────── --}}
                        <div class="tpl-card {{ $invoice_template === 'classic' ? 'tpl-selected' : '' }}"
                            onclick="selectTemplate(this, 'classic')">
                            <div class="tpl-card-header">
                                <span class="tpl-name"><i class="fa fa-file-text-o mr-1"></i> Classic</span>
                                <span class="tpl-active-badge">Active</span>
                            </div>
                            <div class="tpl-preview-area">
                                <div class="inv-p">
                                    <div class="p-cls-hdr">
                                        <img src="{{ asset('images/uploads/' . $company->logo) }}" style="max-height:20px;max-width:50px;object-fit:contain" alt="logo">
                                        <div style="text-align:right">
                                            <div class="p-cls-coname">{{ $company->name }}</div>
                                            <div class="p-cls-codetail">{{ $company->street_address }} {{ $company->suburb }}, {{ $company->city }}<br>{{ $company->phonenumber }} | {{ $company->email }}<br>VAT: {{ $company->vat_number }} | TIN: {{ $company->tin_number }}</div>
                                        </div>
                                    </div>
                                    <div class="p-cls-title">FISCAL TAX INVOICE</div>
                                    <div class="p-cls-subtitle">Thank you for your business</div>
                                    <div class="p-cls-billing">
                                        <div>
                                            <div style="font-size:5.5px;color:#999;margin-bottom:1px">BILL TO</div>
                                            <div style="font-weight:700">Harare Freight Distributors</div>
                                            <div>45 Coventry Rd, Harare</div>
                                            <div>VAT: 2233445566 | TIN: 7788990011</div>
                                        </div>
                                        <div style="text-align:right">
                                            <div style="font-weight:700">INV-2025-00142</div>
                                            <div>Date: 15 May 2025</div>
                                            <div>Due: 14 Jun 2025</div>
                                            <div>Currency: USD</div>
                                        </div>
                                    </div>
                                    <table class="inv-p-table">
                                        <thead><tr><th style="text-align:left">Description</th><th>HS Code</th><th>Qty</th><th>Price</th><th>Excl</th><th>VAT</th><th>Incl</th></tr></thead>
                                        <tbody>
                                            <tr><td style="text-align:left">Long Haul Freight — Harare–Beit Bridge</td><td>8704.10</td><td>1</td><td>$1,200</td><td>$1,200</td><td>$180</td><td>$1,380</td></tr>
                                            <tr><td style="text-align:left">Fuel Surcharge — Beit Bridge</td><td>2710.19</td><td>1</td><td>$150</td><td>$150</td><td>$22.50</td><td>$172.50</td></tr>
                                        </tbody>
                                        <tfoot>
                                            <tr><td colspan="5"></td><td>Sub-total</td><td>$1,350</td></tr>
                                            <tr><td colspan="5"></td><td>VAT</td><td>$202.50</td></tr>
                                            <tr class="total-row"><td colspan="5"></td><td>Total USD</td><td>$1,552.50</td></tr>
                                        </tfoot>
                                    </table>
                                    <div style="font-size:5.5px;color:#333;border-top:.5px solid #ddd;padding-top:3px;margin-top:2px">
                                        <strong style="color:#999;text-transform:uppercase;letter-spacing:.3px">Banking Details</strong> &nbsp;
                                        Bank: CBZ Bank | Acc: 01220567890001 | Swift: COBZZWHAXXX
                                    </div>
                                    <div style="text-align:center;font-size:5.5px;color:#aaa;margin-top:4px;border-top:.5px solid #eee;padding-top:3px">Powered by Basilmark Software Solutions</div>
                                </div>
                            </div>
                            <div class="tpl-footer">
                                <input type="radio" wire:model="invoice_template" value="classic" id="tpl_classic"
                                    {{ $invoice_template === 'classic' ? 'checked' : '' }} style="margin-top:2px;flex-shrink:0">
                                <div>
                                    <label for="tpl_classic">Classic layout</label>
                                    <small>Standard layout — fiscal QR panel, full billing section, bank details table.</small>
                                </div>
                            </div>
                        </div>

                        {{-- ── TEMPLATE 2: TRANSPORT ────────────────── --}}
                        <div class="tpl-card {{ $invoice_template === 'transport' ? 'tpl-selected' : '' }}"
                            onclick="selectTemplate(this, 'transport')">
                            <div class="tpl-card-header">
                                <span class="tpl-name"><i class="fa fa-truck mr-1"></i> Transport</span>
                                <span class="tpl-active-badge">Active</span>
                            </div>
                            <div class="tpl-preview-area">
                                <div class="inv-p">
                                    <div class="p-trp-qr">
                                        <div class="p-trp-qrbox">QR</div>
                                        <div style="font-size:6px;line-height:1.5;color:#333">
                                            <strong style="font-size:7px">FISCAL TAX INVOICE</strong><br>
                                            Receipt Counter: 7/412 &nbsp; Fiscal Day No: 205<br>
                                            Invoice No: 2089 &nbsp; Date: 12/06/2025<br>
                                            Verification Code: A1B2-CD34-EF56-7890
                                        </div>
                                    </div>
                                    <div style="display:flex;justify-content:space-between;margin-bottom:4px">
                                        <div>
                                            <img src="{{ asset('images/uploads/' . $company->logo) }}" style="max-height:18px;max-width:45px;object-fit:contain;display:block;margin-bottom:3px" alt="logo"><div style="font-size:8px;font-weight:700;text-transform:uppercase;letter-spacing:.4px">{{ $company->name }}</div>
                                            <div style="font-size:5.5px;color:#444;line-height:1.5">{{ $company->street_address }}, {{ $company->city }}<br>{{ $company->email }}</div>
                                        </div>
                                        <div style="text-align:right">
                                            <div style="font-size:10px;font-weight:700;color:#0057b7">INVOICE No. 2089</div>
                                            <div style="font-size:5.5px;color:#444">DATE: 12/06/2025 | USD</div>
                                        </div>
                                    </div>
                                    <div style="margin-bottom:4px;font-size:6px">
                                        <span style="color:#555;font-weight:700">Customer:</span> <span style="font-weight:700;color:#c00">Harare Grain Millers (Pvt) Ltd</span>
                                        &nbsp; <span style="color:#555;font-weight:700">VAT:</span> 20045678
                                    </div>
                                    <table class="inv-p-table" style="font-size:5.5px">
                                        <thead><tr>
                                            <th>HS</th><th>Date</th><th>Waybill</th><th style="text-align:left">Origin</th><th style="text-align:left">Dest</th><th>Reg</th><th>MT</th><th>KM</th><th>Rate</th><th>Amount</th>
                                        </tr></thead>
                                        <tbody>
                                            <tr><td>87042290</td><td>02/06</td><td>WB-50021</td><td style="text-align:left">GMB HRE</td><td style="text-align:left">MUTARE</td><td>ABX 1234</td><td>27.5</td><td>265</td><td>0.12</td><td>924.00</td></tr>
                                            <tr><td>87042290</td><td>05/06</td><td>WB-50034</td><td style="text-align:left">GMB HRE</td><td style="text-align:left">GWERU</td><td>ACZ 5678</td><td>30.0</td><td>280</td><td>0.12</td><td>986.00</td></tr>
                                        </tbody>
                                        <tfoot>
                                            <tr><td colspan="8"></td><td>Excl</td><td>1,910.00</td></tr>
                                            <tr><td colspan="8"></td><td>VAT</td><td>296.05</td></tr>
                                            <tr class="total-row"><td colspan="8"></td><td>Total</td><td>2,206.05</td></tr>
                                        </tfoot>
                                    </table>
                                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:5px;margin-top:3px;font-size:5.5px;color:#333">
                                        <div><strong style="font-size:5px;text-transform:uppercase;color:#999">Prepared By</strong><br>Chiedza Moyo (0771234567)<br><div style="border:1px dashed #ccc;height:14px;display:flex;align-items:center;justify-content:center;font-size:5px;color:#bbb;margin-top:2px">Company Stamp</div></div>
                                        <div><strong style="font-size:5px;text-transform:uppercase;color:#999">Banking Details</strong><br>CBZ Bank | JASON MOYO<br>USD: 0122056789002<br>Swift: COBZZWHAXXX</div>
                                    </div>
                                </div>
                            </div>
                            <div class="tpl-footer">
                                <input type="radio" wire:model="invoice_template" value="transport" id="tpl_transport"
                                    {{ $invoice_template === 'transport' ? 'checked' : '' }} style="margin-top:2px;flex-shrink:0">
                                <div>
                                    <label for="tpl_transport">Transport layout</label>
                                    <small>Compact fiscal banner with transport columns: waybill, origin/destination, tonnage, km rate.</small>
                                </div>
                            </div>
                        </div>

                        {{-- ── TEMPLATE 3: MODERN ───────────────────── --}}
                        <div class="tpl-card {{ $invoice_template === 'modern' ? 'tpl-selected' : '' }}"
                            onclick="selectTemplate(this, 'modern')">
                            <div class="tpl-card-header">
                                <span class="tpl-name"><i class="fa fa-star-o mr-1"></i> Modern</span>
                                <span class="tpl-active-badge">Active</span>
                            </div>
                            <div class="tpl-preview-area">
                                <div class="inv-p">
                                    <div class="p-mod-hdr">
                                        <div>
                                            <img src="{{ asset('images/uploads/' . $company->logo) }}" style="max-height:16px;max-width:40px;object-fit:contain;filter:brightness(0) invert(1);margin-bottom:3px;display:block" alt="logo">
                                            <div class="p-mod-coname">{{ $company->name }}</div>
                                            <div class="p-mod-codetail">{{ $company->street_address }}, {{ $company->city }}<br>{{ $company->phonenumber }} | VAT: {{ $company->vat_number }}</div>
                                        </div>
                                        <div style="text-align:right">
                                            <div class="p-mod-doctype">TAX INVOICE</div>
                                            <div class="p-mod-invno">INV-2025-00142</div>
                                            <div class="p-mod-pills">
                                                <div class="p-mod-pill"><span>Date:</span>15 May 2025</div>
                                                <div class="p-mod-pill"><span>Due:</span>14 Jun 2025</div>
                                                <div class="p-mod-pill"><span>CCY:</span>USD</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-mod-accent"></div>
                                    <div class="p-mod-billing">
                                        <div class="p-mod-box">
                                            <div class="p-mod-boxhdr">Bill To</div>
                                            <div class="p-mod-boxbody">
                                                <div class="cname">Harare Freight Distributors</div>
                                                <div>45 Coventry Rd, Harare</div>
                                                <div>accounts@hfd.co.zw</div>
                                                <div>VAT: 2233445566</div>
                                            </div>
                                        </div>
                                        <div class="p-mod-box">
                                            <div class="p-mod-boxhdr">Fiscal Details</div>
                                            <div class="p-mod-boxbody">
                                                <div>Fiscal Day: 205</div>
                                                <div>RA Invoice: RA-INV-00789</div>
                                                <div style="font-family:monospace;font-weight:700">A1B2-CD34-EF56</div>
                                                <div style="color:#185FA5">fdms.zimra.co.zw</div>
                                            </div>
                                        </div>
                                    </div>
                                    <table class="inv-p-table">
                                        <thead><tr><th style="text-align:left">Description</th><th>HS</th><th>Qty</th><th>Price</th><th>Excl</th><th>VAT</th><th>Incl</th></tr></thead>
                                        <tbody>
                                            <tr><td style="text-align:left">Long Haul Freight — Harare–Beit Bridge</td><td>8704.10</td><td>1</td><td>$1,200</td><td>$1,200</td><td>$180</td><td>$1,380</td></tr>
                                            <tr><td style="text-align:left">Fuel Surcharge — Beit Bridge</td><td>2710.19</td><td>1</td><td>$150</td><td>$150</td><td>$22.50</td><td>$172.50</td></tr>
                                        </tbody>
                                        <tfoot>
                                            <tr style="border-top:.5px solid #ddd"><td colspan="5"></td><td>Sub-total</td><td>$1,350</td></tr>
                                            <tr><td colspan="5"></td><td>VAT</td><td>$202.50</td></tr>
                                            <tr class="total-row"><td colspan="5"></td><td>Total USD</td><td>$1,552.50</td></tr>
                                        </tfoot>
                                    </table>
                                    <div class="p-mod-bottom">
                                        <div>
                                            <div class="p-mod-slabel">Notes / Terms</div>
                                            Payment due within 30 days. All rates in USD. Late payments attract a 2% monthly surcharge.
                                        </div>
                                        <div>
                                            <div class="p-mod-slabel">Banking Details</div>
                                            <div>Bank: CBZ Bank Limited</div>
                                            <div>Branch: Jason Moyo Avenue</div>
                                            <div>USD Acc: 01220567890002</div>
                                            <div>Swift: COBZZWHAXXX</div>
                                        </div>
                                    </div>
                                    <div style="display:flex;justify-content:space-between;align-items:center;font-size:5.5px;color:#aaa;margin-top:5px;border-top:.5px solid #ddd;padding-top:3px">
                                        <span>Electronically generated. E&amp;OE.</span>
                                        <span style="display:flex;align-items:center;gap:3px"><span style="width:6px;height:6px;background:#185FA5;border-radius:50%;display:inline-block"></span> Powered by Basilmark</span>
                                    </div>
                                </div>
                            </div>
                            <div class="tpl-footer">
                                <input type="radio" wire:model="invoice_template" value="modern" id="tpl_modern"
                                    {{ $invoice_template === 'modern' ? 'checked' : '' }} style="margin-top:2px;flex-shrink:0">
                                <div>
                                    <label for="tpl_modern">Modern layout</label>
                                    <small>Full-width dark header, accent stripe, boxed billing + fiscal details, clean table body.</small>
                                </div>
                            </div>
                        </div>

                    </div>
                    @error('invoice_template') <span class="error" style="color:red">{{ $message }}</span> @enderror
                </div>

                <script>
                function selectTemplate(card, value) {
                    document.querySelectorAll('.tpl-card').forEach(c => c.classList.remove('tpl-selected'));
                    card.classList.add('tpl-selected');
                    document.getElementById('tpl_' + value).click();
                }
                </script>
                {{-- ── END TEMPLATE SELECTOR ──────────────────────────────────────────────── --}}

                <div class="mb-10 mt-10">
                    <input type="checkbox" wire:model.debounce.300ms="show_branding"   class="line-style" />
                    <label for="one" class="radio-label">Show Gonyeti branding on printable documents</label>
                    @error('show_branding') <span class="text-danger error">{{ $message }}</span>@enderror
                    <br>
                    <small style="color: green">Uncheck to hide the "Powered By" Gonyeti logo/name on invoices, quotations, purchase orders, requisitions, receipts, credit notes and debit notes.</small>
                </div>

               <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Default Payment Terms</label>
                            <select  wire:model.debounce.300ms="invoice_due_when" class="form-control">
                                <option value="">Select Option</option>
                                <option value="0">Due upon receipt</option>
                                <option value="15">Due within 15 Days</option>
                                <option value="30">Due within 30 Days</option>
                                <option value="45">Due within 45 Days</option>
                                <option value="60">Due within 60 Days</option>
                                <option value="90">Due within 90 Days</option>
                            </select>
                            <small style="color: green">The default title for all invoices. You can change this on each invoice.</small>
                            @error('invoice_due_when') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Default Title</label>
                            <input type="text" wire:model.debounce.300ms="invoice_title" class="form-control" placeholder="Default Invoice Title">
                            <small style="color: green">The default title for all invoices. You can change this on each invoice.</small>
                            @error('invoice_title') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Default Subheading</label>
                            <input type="text" wire:model.debounce.300ms="invoice_subheading" class="form-control" placeholder="Default Invoice Subheading">
                            <small style="color: green">This will be displayed below the title of each invoice. Useful for things like Vendor numbers.</small>
                            @error('invoice_subheading') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
               </div>
               <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="exampleInputEmail1">Default notes / terms</label>
                       <textarea wire:model.debounce.300ms="invoice_memo" cols="30" rows="2" class="form-control" placeholder="Invoice notes / terms"></textarea>
                       @error('invoice_memo') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="invoice_footer">Default Footer</label>
                       <textarea wire:model.debounce.300ms="invoice_footer" cols="30" rows="2" class="form-control" placeholder="Invoice footer"></textarea>
                       @error('invoice_footer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
             <h5 class="underline mt-5">Invoice Column Settings</h5>
            <div class="row">
                <div class="col-md-6">
                    <label for="one" class="radio-label">Items</label>
                    <div class="mb-10">
                        <input type="radio" wire:model.debounce.300ms="items_column" value="Items"  class="line-style"  />
                        <label for="one" class="radio-label">Items<small><i>(Default)</i></small></label>
                        <input type="radio" wire:model.debounce.300ms="items_column" value="Description"  class="line-style"  />
                        <label for="one" class="radio-label">Description</label>
                        <input type="radio" wire:model.debounce.300ms="items_column" value="Products"  class="line-style"  />
                        <label for="one" class="radio-label">Products</label>
                        <input type="radio" wire:model.debounce.300ms="items_column" value="Services"  class="line-style"  />
                        <label for="one" class="radio-label">Services</label>
                        <input type="radio" wire:model.debounce.300ms="items_column" value="Other"  class="line-style"  />
                        <label for="one" class="radio-label">Other</label>
                        @error('items_column') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>   
                </div>
                <div class="col-md-6">
                    <label for="one" class="radio-label">Units</label>
                    <div class="mb-10">
                        <input type="radio" wire:model.debounce.300ms="units_column" value="Qty"  class="line-style"  />
                        <label for="one" class="radio-label">Quantity<small><i>(Default)</i></small></label>
                        <input type="radio" wire:model.debounce.300ms="units_column" value="Hours"  class="line-style"  />
                        <label for="one" class="radio-label">Hours</label>
                        <input type="radio" wire:model.debounce.300ms="units_column" value="Days"  class="line-style"  />
                        <label for="one" class="radio-label">Days</label>
                        <input type="radio" wire:model.debounce.300ms="units_column" value="Other"  class="line-style"  />
                        <label for="one" class="radio-label">Other</label>
                        @error('units_column') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>   
                </div>
                
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label for="one" class="radio-label">Price</label>
                    <div class="mb-10">
                        <input type="radio" wire:model.debounce.300ms="price_column" value="Price"  class="line-style"  />
                        <label for="one" class="radio-label">Price<small><i>(Default)</i></small></label>
                        <input type="radio" wire:model.debounce.300ms="price_column" value="Rate"  class="line-style"  />
                        <label for="one" class="radio-label">Rate</label>
                        <input type="radio" wire:model.debounce.300ms="price_column" value="Other"  class="line-style"  />
                        <label for="one" class="radio-label">Other</label>
                        @error('price_column') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>   
                </div>
                <div class="col-md-6">
                     <label for="one" class="radio-label">Amount</label>
                    <div class="mb-10">
                        <input type="radio" wire:model.debounce.300ms="total_column" value="Total"  class="line-style"  />
                        <label for="one" class="radio-label">Total<small><i>(Default)</i></small></label>
                        <input type="radio" wire:model.debounce.300ms="total_column" value="Amount"  class="line-style"  />
                        <label for="one" class="radio-label">Amount</label>
                        <input type="radio" wire:model.debounce.300ms="total_column" value="Other"  class="line-style"  />
                        <label for="one" class="radio-label">Other</label>
                        @error('total_column') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>   
                </div>
            </div>
            <label for="one" class="radio-label">Choose which columns on your invoices to hide:</label>
            <div class="row">
                 <div class="col-md-6">
                    <div class="mb-10">
                        <input type="checkbox" wire:model.debounce.300ms="hide_items"   class="line-style" />
                        <label for="one" class="radio-label">Hide Items</label>
                        @error('hide_items') <span class="text-danger error">{{ $message }}</span>@enderror
                        <input type="checkbox" wire:model.debounce.300ms="hide_description"   class="line-style" />
                        <label for="one" class="radio-label">Hide Description</label>
                        @error('hide_description') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>
                </div>
                 <div class="col-md-6">
                    <div class="mb-10">
                        <input type="checkbox" wire:model.debounce.300ms="hide_price"   class="line-style" />
                        <label for="one" class="radio-label">Hide Price</label>
                        @error('hide_price') <span class="text-danger error">{{ $message }}</span>@enderror
                        <input type="checkbox" wire:model.debounce.300ms="hide_quantity"   class="line-style" />
                        <label for="one" class="radio-label">Hide Quantity</label>
                        @error('hide_quantity') <span class="text-danger error">{{ $message }}</span>@enderror
                        <input type="checkbox" wire:model.debounce.300ms="hide_amount"   class="line-style" />
                        <label for="one" class="radio-label">Hide Amount</label> <br>
                        <small style="color: green">Your invoice must show at least one of the above.</small>
                        @error('hide_amount') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>
            <h5 class="underline mt-5">Quotations</h5>
               <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Default valid until</label>
                            <select  wire:model.debounce.300ms="quote_valid_until" class="form-control">
                                <option value="">Select Option</option>
                                <option value="1">1 Day</option>
                                <option value="7">7 Days</option>
                                <option value="15">15 Days</option>
                                <option value="30">30 Days</option>
                                <option value="45">45 Days</option>
                                <option value="60">60 Days</option>
                            </select>
                            <small style="color: green">The default validity period for all quotations. You can change this on each quotation</small>
                            @error('quote_valid_until') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Default Title</label>
                            <input type="text" wire:model.debounce.300ms="quotation_title" class="form-control" placeholder="Default Quotation Title">
                            <small style="color: green">The default title for all quotations. You can change this on each quotation.</small>
                            @error('quotation_title') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Default Subheading</label>
                            <input type="text" wire:model.debounce.300ms="quotation_subheading" class="form-control" placeholder="Default Quotation Subheading">
                            <small style="color: green">This will be displayed below the title of each quotation. Useful for things like Vendor numbers.</small>
                            @error('quotation_subheading') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
               </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="quotation_memo">Default Notes / Terms</label>
                       <textarea wire:model.debounce.300ms="quotation_memo" cols="30" rows="2" class="form-control" placeholder="Quotation notes / terms" ></textarea>
                       @error('quotation_memo') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="quotation_footer">Default Footer</label>
                       <textarea wire:model.debounce.300ms="quotation_footer" cols="30" rows="2" class="form-control" placeholder="Quotation footer"></textarea>
                       @error('quotation_footer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
            <h5 class="underline mt-5">Receipts</h5>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="exampleInputEmail1">Default notes / terms</label>
                       <textarea wire:model.debounce.300ms="receipt_memo" cols="30" rows="2" class="form-control" placeholder="Receipt notes / terms"></textarea>
                       @error('receipt_memo') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="invoice_footer">Default footer</label>
                       <textarea wire:model.debounce.300ms="receipt_footer" cols="30" rows="2" class="form-control" placeholder="Receipt footer"></textarea>
                       @error('receipt_footer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div> 

            <div class="btn-group" role="group" style="float: right;">
                <button type="submit" class="btn btn-success btn-wide btn-rounded" ><i class="fa fa-refresh"></i>Update</button>
            </div>
            <br>
            <hr>
    </form>
</div>
