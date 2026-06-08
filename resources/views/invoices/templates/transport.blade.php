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
                <strong>Receipt Counter:</strong> 7/412 &nbsp;&nbsp;
                <strong>Fiscal Day No:</strong> 205<br>
                Invoice No: 2089 &nbsp;&nbsp;
                Date: 12/06/2025 09:30<br>
                Device Serial: 02ZIM20005067 &nbsp;&nbsp;
                Fiscal Device Id: 30841<br>
                Verification Code: A1B2-CD34-EF56-7890<br>
                You can verify receipt manually at
                <a href="https://fdms.zimra.co.zw" target="_blank" style="color:#0057b7">https://fdms.zimra.co.zw</a>
            </div>
        </div>
    </div>

    {{-- HEADER --}}
    <div class="header-row">
        <div class="company-block">
            <div class="company-name">Gonyeti Transport Ltd</div>
            <p>123 Samora Machel Avenue,</p>
            <p>Workington,</p>
            <p>Harare</p>
            <p>Tel: 08644 100 200 / 0864 300 400</p>
            <p>Cell: 0771 234 567 / 0782 345 678</p>
            <p><a href="mailto:info@gonyetitransport.co.zw">info@gonyetitransport.co.zw</a></p>
        </div>

        <div class="invoice-meta">
            <div class="invoice-number">INVOICE No. 2089</div>
            <table>
                <tr><td>DATE:</td><td>12/06/2025</td></tr>
                <tr><td>INVOICE CURRENCY:</td><td>USD</td></tr>
                <tr><td>VAT No:</td><td>100123456</td></tr>
                <tr><td>TIN No:</td><td>3000987654</td></tr>
                <tr><td>PO No:</td><td>PO-2025-00781</td></tr>
            </table>
        </div>
    </div>

    {{-- CUSTOMER --}}
    <div class="customer-section">
        <table>
            <tr>
                <td>Customer Name:</td>
                <td class="customer-name-value">Harare Grain Millers (Pvt) Ltd</td>
            </tr>
            <tr>
                <td>Customer House No:</td>
                <td>Plot 45, Coventry Road,</td>
            </tr>
            <tr>
                <td>Customer Street:</td>
                <td>Graniteside,</td>
            </tr>
            <tr>
                <td>Customer Province:</td>
                <td>Harare</td>
            </tr>
            <tr>
                <td>Customer Email:</td>
                <td>accounts@hgm.co.zw</td>
            </tr>
            <tr>
                <td>Customer Phone:</td>
                <td>+263 077 456 7890</td>
            </tr>
            <tr>
                <td>Customer VAT:</td>
                <td>20045678</td>
            </tr>
            <tr>
                <td>Customer TIN:</td>
                <td>5000112233</td>
            </tr>
            <tr>
                <td>Customer Bp No:</td>
                <td>400078932</td>
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
            <tr>
                <td>87042290</td>
                <td>02/06/2025</td>
                <td>WB-50021</td>
                <td>LTI-8800112200</td>
                <td class="text-left">GRAIN MARKETING BOARD HARARE</td>
                <td class="text-left">MUTARE</td>
                <td>ABX 1234</td>
                <td>28.00</td>
                <td>27.5</td>
                <td>265</td>
                <td>0.12</td>
                <td>50.00</td>
                <td>924.00</td>
            </tr>
            <tr>
                <td>87042290</td>
                <td>05/06/2025</td>
                <td>WB-50034</td>
                <td>LTI-8800112244</td>
                <td class="text-left">GRAIN MARKETING BOARD HARARE</td>
                <td class="text-left">GWERU</td>
                <td>ACZ 5678</td>
                <td>30.00</td>
                <td>30.0</td>
                <td>280</td>
                <td>0.12</td>
                <td>50.00</td>
                <td>986.00</td>
            </tr>
        </tbody>
    </table>

    {{-- TOTALS --}}
    <div class="totals-section">
        <table class="totals-table">
            <tr>
                <td>Total VAT Exc</td>
                <td>1,910.00</td>
            </tr>
            <tr>
                <td>VAT 15.5%</td>
                <td>296.05</td>
            </tr>
            <tr class="total-final">
                <td>Total VAT Inc</td>
                <td>2,206.05</td>
            </tr>
        </table>
    </div>

    {{-- FOOTER --}}
    <div class="invoice-footer">
        <div class="footer-grid">
            <div>
                <div class="section-label">Prepared By</div>
                <p>Chiedza Moyo (0771234567)</p>
                <br>
                <div class="section-label">Company Stamp</div>
                <div class="stamp-box">Company Stamp (Date)</div>
            </div>
            <div>
                <div class="section-label">Banking Details</div>
                <p><strong>Account Name:</strong> GONYETI TRANSPORT LIMITED</p>
                <p><strong>Bank:</strong> CBZ BANK LIMITED</p>
                <p><strong>Branch:</strong> JASON MOYO AVENUE</p>
                <p><strong>Sort Code:</strong> 06000</p>
                <p><strong>Account No ZWL:</strong> 0122056789001</p>
                <p><strong>Account No USD:</strong> 0122056789002</p>
                <p><strong>Swift Code:</strong> COBZZWHAXXX</p>
            </div>
        </div>
    </div>

</div>
</div>