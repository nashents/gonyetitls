{{-- resources/views/livewire/invoice-wfp.blade.php --}}

<div>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@400;700&family=Roboto:wght@300;400;500;700&display=swap');

    .invoice-wrap {
        font-family: 'Roboto', sans-serif;
        font-size: 11px;
        color: #1a1a1a;
        background: #fff;
        width: 210mm;
        min-height: 297mm;
        margin: 0 auto;
        padding: 14mm 14mm 10mm;
        box-sizing: border-box;
        position: relative;
    }

    /* ── FISCAL BANNER ── */
    .fiscal-banner {
        border: 1.5px solid #1a1a1a;
        padding: 6px 10px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 14px;
    }
    .fiscal-banner .qr-placeholder {
        width: 64px;
        height: 64px;
        background: #eee;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 9px;
        color: #999;
        flex-shrink: 0;
        border: 1px solid #ccc;
    }
    .fiscal-banner .fiscal-text {
        font-size: 9.5px;
        line-height: 1.6;
    }
    .fiscal-banner .fiscal-title {
        font-weight: 700;
        font-size: 11px;
        margin-bottom: 3px;
        letter-spacing: 0.5px;
        text-align: center;
    }

    /* ── HEADER ROW ── */
    .header-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 14px;
    }
    .company-block .company-name {
        font-family: 'Roboto Condensed', sans-serif;
        font-weight: 700;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }
    .company-block p {
        margin: 1px 0;
        line-height: 1.55;
        color: #333;
    }
    .company-block a {
        color: #0057b7;
        text-decoration: none;
    }

    .invoice-meta {
        text-align: right;
        min-width: 200px;
    }
    .invoice-meta .invoice-number {
        font-family: 'Roboto Condensed', sans-serif;
        font-size: 22px;
        font-weight: 700;
        color: #0057b7;
        letter-spacing: 1px;
    }
    .invoice-meta table {
        margin-left: auto;
        border-collapse: collapse;
    }
    .invoice-meta td {
        padding: 1px 4px;
        line-height: 1.55;
    }
    .invoice-meta td:first-child {
        font-weight: 600;
        color: #555;
        text-align: right;
    }
    .invoice-meta td:last-child {
        text-align: right;
        min-width: 110px;
    }

    /* ── CUSTOMER BLOCK ── */
    .customer-section {
        margin-bottom: 14px;
    }
    .customer-section table {
        border-collapse: collapse;
        width: 60%;
    }
    .customer-section td {
        padding: 1.5px 6px 1.5px 0;
        line-height: 1.55;
        vertical-align: top;
    }
    .customer-section td:first-child {
        font-weight: 600;
        color: #555;
        white-space: nowrap;
        width: 120px;
    }
    .customer-section .customer-name-value {
        font-weight: 700;
        color: #c00;
    }

    /* ── LINE ITEMS TABLE ── */
    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 16px;
        font-size: 10px;
    }
    .items-table thead tr {
        background: #1a1a1a;
        color: #fff;
    }
    .items-table thead th {
        padding: 5px 6px;
        text-align: center;
        font-weight: 600;
        letter-spacing: 0.2px;
        border: 1px solid #333;
        line-height: 1.35;
    }
    .items-table tbody tr {
        border-bottom: 1px solid #ddd;
    }
    .items-table tbody tr:nth-child(even) {
        background: #f7f7f7;
    }
    .items-table tbody td {
        padding: 5px 6px;
        text-align: center;
        border: 1px solid #ddd;
        vertical-align: middle;
    }
    .items-table tbody td.text-left {
        text-align: left;
    }

    /* ── TOTALS ── */
    .totals-section {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 20px;
    }
    .totals-table {
        border-collapse: collapse;
        min-width: 220px;
    }
    .totals-table td {
        padding: 3px 8px;
        border: 1px solid #ccc;
        text-align: right;
    }
    .totals-table td:first-child {
        font-weight: 600;
        background: #f0f0f0;
    }
    .totals-table tr.total-final td {
        background: #1a1a1a;
        color: #fff;
        font-weight: 700;
        font-size: 12px;
    }

    /* ── FOOTER ── */
    .invoice-footer {
        margin-top: 30px;
        border-top: 1px solid #ccc;
        padding-top: 10px;
        font-size: 10px;
        color: #444;
    }
    .footer-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    .footer-grid .section-label {
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 9px;
        color: #999;
        margin-bottom: 4px;
    }
    .footer-grid p {
        margin: 1px 0;
        line-height: 1.55;
    }
    .stamp-box {
        border: 1px dashed #bbb;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #bbb;
        font-size: 9px;
        letter-spacing: 1px;
        text-transform: uppercase;
        margin-top: 4px;
    }

    @media print {
        .invoice-wrap {
            width: 100%;
            padding: 10mm;
            box-shadow: none;
        }
    }
</style>

<div class="invoice-wrap">

    {{-- FISCAL BANNER --}}
    <div class="fiscal-banner">
        <div class="qr-placeholder">QR</div>
        <div style="flex:1">
            <div class="fiscal-title">FISCAL TAX INVOICE</div>
            <div class="fiscal-text">
                <strong>Receipt Counter:</strong> {{ $invoice['receipt_counter'] ?? '4/935' }} &nbsp;&nbsp;
                <strong>Fiscal Day No:</strong> {{ $invoice['fiscal_day_no'] ?? '318' }}<br>
                Invoice No: {{ $invoice['number'] }} &nbsp;&nbsp;
                Date: {{ \Carbon\Carbon::parse($invoice['date'])->format('d/m/Y') }} {{ $invoice['time'] ?? '14:05' }}<br>
                Device Serial: {{ $invoice['device_serial'] ?? '02ZIM10001014' }} &nbsp;&nbsp;
                Fiscal Device Id: {{ $invoice['fiscal_device_id'] ?? '10276' }}<br>
                Verification Code: {{ $invoice['verification_code'] ?? '3843-341A-6238-88D0' }}<br>
                You can verify receipt manually at
                <a href="https://fdms.zimra.co.zw" target="_blank" style="color:#0057b7">https://fdms.zimra.co.zw</a>
            </div>
        </div>
    </div>

    {{-- HEADER --}}
    <div class="header-row">
        <div class="company-block">
            <div class="company-name">{{ $company['name'] ?? 'Trinitas Distributors' }}</div>
            <p>{{ $company['address_line1'] ?? '1817 Usselby Road,' }}</p>
            <p>{{ $company['address_line2'] ?? 'Waterfalls' }}</p>
            <p>{{ $company['city'] ?? 'Harare' }}</p>
            <p>Tel: {{ $company['tel'] ?? '08644 068 714 / 0864 202 362' }}</p>
            <p>Cell: {{ $company['cell'] ?? '0785 677 691 / 0788 829 894' }}</p>
            <p><a href="mailto:{{ $company['email'] ?? 'sales@trinitaszw.com' }}">{{ $company['email'] ?? 'sales@trinitaszw.com' }}</a></p>
        </div>

        <div class="invoice-meta">
            <div class="invoice-number">INVOICE No. {{ $invoice['number'] ?? '4106' }}</div>
            <table>
                <tr><td>DATE:</td><td>{{ \Carbon\Carbon::parse($invoice['date'])->format('d/m/Y') }}</td></tr>
                <tr><td>INVOICE CURRENCY:</td><td>{{ $invoice['currency'] ?? 'USD' }}</td></tr>
                <tr><td>VAT No:</td><td>{{ $company['vat_no'] ?? '220149113' }}</td></tr>
                <tr><td>TIN No:</td><td>{{ $company['tin_no'] ?? '2000619633' }}</td></tr>
                <tr><td>PO No:</td><td>{{ $invoice['po_no'] ?? '4700797253' }}</td></tr>
            </table>
        </div>
    </div>

    {{-- CUSTOMER --}}
    <div class="customer-section">
        <table>
            <tr>
                <td>Customer Name:</td>
                <td class="customer-name-value">{{ $customer['name'] ?? 'World Food Programme, Country Office' }}</td>
            </tr>
            <tr>
                <td>Customer House No:</td>
                <td>{{ $customer['house_no'] ?? 'Block 1, Arundel Office Park, Norfolk Rd,' }}</td>
            </tr>
            <tr>
                <td>Customer Street:</td>
                <td>{{ $customer['street'] ?? 'Mt Pleasant,' }}</td>
            </tr>
            <tr>
                <td>Customer Province:</td>
                <td>{{ $customer['province'] ?? 'Harare' }}</td>
            </tr>
            <tr>
                <td>Customer Email:</td>
                <td>{{ $customer['email'] ?? 'harare.supplychaininvoicing@wfp.org' }}</td>
            </tr>
            <tr>
                <td>Customer Phone:</td>
                <td>{{ $customer['phone'] ?? '+263 08677000805' }}</td>
            </tr>
            <tr>
                <td>Customer VAT:</td>
                <td>{{ $customer['vat'] ?? '10091435' }}</td>
            </tr>
            <tr>
                <td>Customer TIN:</td>
                <td>{{ $customer['tin'] ?? '6000000416' }}</td>
            </tr>
            <tr>
                <td>Customer Bp No:</td>
                <td>{{ $customer['bp_no'] ?? '300012191' }}</td>
            </tr>
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
            @forelse($items ?? [] as $item)
            <tr>
                <td>{{ $item['hs_code'] }}</td>
                <td>{{ \Carbon\Carbon::parse($item['date'])->format('d/m/Y') }}</td>
                <td>{{ $item['waybill_no'] }}</td>
                <td>{{ $item['lti_no'] }}</td>
                <td class="text-left">{{ $item['origin'] }}</td>
                <td class="text-left">{{ $item['destination'] }}</td>
                <td>{{ $item['reg_no'] }}</td>
                <td>{{ number_format($item['truck_capacity'], 2) }}</td>
                <td>{{ number_format($item['delivered_tonnage'], 1) }}</td>
                <td>{{ number_format($item['distance']) }}</td>
                <td>{{ number_format($item['rate_per_km'], 2) }}</td>
                <td>{{ $item['loading_fee'] ?? '' }}</td>
                <td>{{ number_format($item['amount'], 2) }}</td>
            </tr>
            @empty
            {{-- Sample row --}}
            <tr>
                <td>87042290</td>
                <td>14/3/2026</td>
                <td>14111</td>
                <td>7700783200</td>
                <td class="text-left">DORMERVALE MARONDERA</td>
                <td class="text-left">MASVINGO</td>
                <td>AGJ 2320</td>
                <td>30.00</td>
                <td>30.0</td>
                <td>399</td>
                <td>0.11</td>
                <td></td>
                <td>1,316.70</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- TOTALS --}}
    <div class="totals-section">
        <table class="totals-table">
            <tr>
                <td>Total VAT Exc</td>
                <td>{{ number_format($totals['vat_exclusive'] ?? 1316.70, 2) }}</td>
            </tr>
            <tr>
                <td>VAT 15.5%</td>
                <td>{{ number_format($totals['vat_amount'] ?? 204.09, 2) }}</td>
            </tr>
            <tr class="total-final">
                <td>Total VAT Inc</td>
                <td>{{ number_format($totals['vat_inclusive'] ?? 1520.79, 2) }}</td>
            </tr>
        </table>
    </div>

    {{-- FOOTER --}}
    <div class="invoice-footer">
        <div class="footer-grid">
            <div>
                <div class="section-label">Prepared By</div>
                <p>{{ $invoice['prepared_by'] ?? 'Tawanda Dafana (0788829894)' }}</p>
                <br>
                <div class="section-label">Company Stamp</div>
                <div class="stamp-box">Company Stamp (Date)</div>
            </div>
            <div>
                <div class="section-label">Banking Details</div>
                <p><strong>Account Name:</strong> {{ $bank['account_name'] ?? 'TRINITAS INVESTMENTS PRIVATE LIMITED' }}</p>
                <p><strong>Bank:</strong> {{ $bank['bank_name'] ?? 'ECOBANK ZIMBABWE LIMITED' }}</p>
                <p><strong>Branch:</strong> {{ $bank['branch'] ?? 'JOINA CITY' }}</p>
                <p><strong>Sort Code:</strong> {{ $bank['sort_code'] ?? '26000' }}</p>
                <p><strong>Account No ZWL:</strong> {{ $bank['account_zwl'] ?? '5722100001146' }}</p>
                <p><strong>Account No USD:</strong> {{ $bank['account_usd'] ?? '5783600018539' }}</p>
                <p><strong>Swift Code:</strong> {{ $bank['swift'] ?? 'ECOCZWHX' }}</p>
            </div>
        </div>
    </div>

</div>
</div>