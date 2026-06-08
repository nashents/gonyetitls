{{-- resources/views/livewire/invoice-modern.blade.php --}}

<div>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap');

    .inv-wrap {
        font-family: 'Roboto', sans-serif;
        font-size: 11px;
        color: #1a1a1a;
        background: #fff;
        width: 210mm;
        min-height: 297mm;
        margin: 0 auto;
        padding: 0 0 14mm;
        box-sizing: border-box;
        position: relative;
    }

    /* ── DARK HEADER ── */
    .inv-header {
        background: #1a1a1a;
        color: #fff;
        padding: 22px 28px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .inv-header-left {}
    .inv-logo-wrap {
        margin-bottom: 10px;
    }
    .inv-logo-wrap img {
        max-height: 48px;
        max-width: 160px;
        filter: brightness(0) invert(1);
    }
    .inv-coname {
        font-size: 16px;
        font-weight: 700;
        letter-spacing: 0.5px;
        color: #fff;
        margin-bottom: 5px;
    }
    .inv-codetail {
        font-size: 9.5px;
        color: rgba(255,255,255,0.72);
        line-height: 1.75;
    }
    .inv-codetail a {
        color: rgba(255,255,255,0.8);
        text-decoration: none;
    }
    .inv-header-right {
        text-align: right;
    }
    .inv-doc-type {
        font-size: 24px;
        font-weight: 700;
        letter-spacing: 1.5px;
        color: #fff;
        margin-bottom: 4px;
    }
    .inv-number {
        font-size: 13px;
        color: rgba(255,255,255,0.8);
        margin-bottom: 12px;
        letter-spacing: 0.3px;
    }
    .inv-meta-pills {
        display: flex;
        flex-direction: column;
        gap: 4px;
        align-items: flex-end;
    }
    .inv-pill {
        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 999px;
        padding: 2px 12px;
        font-size: 9.5px;
        color: #fff;
        white-space: nowrap;
    }
    .inv-pill span {
        color: rgba(255,255,255,0.55);
        margin-right: 5px;
    }

    /* ── ACCENT STRIPE ── */
    .inv-accent {
        height: 4px;
        background: {{ $company->color ?: '#185FA5' }};
    }

    /* ── BODY ── */
    .inv-body {
        padding: 20px 28px 14px;
    }

    .inv-billing-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 18px;
    }
    .inv-billing-box {
        border: 0.5px solid #ddd;
        border-radius: 6px;
        overflow: hidden;
    }
    .inv-billing-box-hdr {
        background: #f4f4f4;
        padding: 5px 10px;
        font-size: 8.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #555;
        border-bottom: 0.5px solid #ddd;
    }
    .inv-billing-box-body {
        padding: 9px 10px;
        font-size: 9.5px;
        line-height: 1.8;
        color: #333;
    }
    .inv-billing-box-body .cname {
        font-size: 11px;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 3px;
    }
    .inv-billing-box-body .row {
        display: flex;
        gap: 5px;
    }
    .inv-billing-box-body .lbl {
        color: #888;
        min-width: 65px;
        flex-shrink: 0;
    }

    /* ── ITEMS TABLE ── */
    .inv-items {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 16px;
        font-size: 10px;
    }
    .inv-items thead tr {
        background: #1a1a1a;
        color: #fff;
    }
    .inv-items th {
        padding: 7px 8px;
        text-align: right;
        font-weight: 600;
        letter-spacing: 0.2px;
    }
    .inv-items th:first-child { text-align: left; }
    .inv-items tbody tr:nth-child(even) { background: #f9f9f9; }
    .inv-items td {
        padding: 7px 8px;
        text-align: right;
        border-bottom: 0.5px solid #ececec;
        vertical-align: middle;
    }
    .inv-items td:first-child { text-align: left; }
    .inv-items tfoot td {
        padding: 5px 8px;
        text-align: right;
        border: none;
    }
    .inv-items tfoot tr.subtotal td { border-top: 0.5px solid #ddd; }
    .inv-items tfoot tr.grand td {
        background: #1a1a1a;
        color: #fff;
        font-weight: 700;
        font-size: 11px;
    }

    /* ── BOTTOM GRID ── */
    .inv-bottom {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-top: 10px;
        font-size: 10px;
    }
    .inv-section-label {
        font-size: 8.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #999;
        margin-bottom: 5px;
    }
    .inv-notes {
        color: #444;
        line-height: 1.7;
    }
    .inv-bank {
        color: #333;
        line-height: 1.85;
    }

    /* ── FOOTER ── */
    .inv-doc-footer {
        margin-top: 20px;
        padding-top: 10px;
        border-top: 0.5px solid #ddd;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 9px;
        color: #aaa;
    }
    .inv-footer-brand {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .inv-footer-brand .dot {
        width: 8px;
        height: 8px;
        background: {{ $company->color ?: '#185FA5' }};
        border-radius: 50%;
        flex-shrink: 0;
    }

    @media print {
        .inv-wrap { width: 100%; padding: 0; }
        .inv-body { padding: 16px 20px 10px; }
        .inv-header { padding: 18px 20px; }
    }
</style>

    @livewire('invoices.templates.modern',[
                    'invoice' => $invoice,
                    'company' => $company,
                    'invoice_items' => $invoice_items,])
</div>
