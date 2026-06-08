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
        .hidden-print {
            display: none !important;
        }

        body {
            margin: 0;
            padding: 0;
            background: #fff;
        }

        .invoice-wrap {
            width: 100%;
            padding: 10mm;
            box-shadow: none;
            margin: 0;
        }
    }
</style>
    @livewire('invoices.templates.transport', [
                    'invoice' => $invoice,
                    'company' => $company,
                    'invoice_items' => $invoice_items,])
</div>