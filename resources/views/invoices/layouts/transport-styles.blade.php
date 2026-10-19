{{--
    TRANSPORT invoice styles — shared by the screen/print page and the PDF.
    dompdf-safe: tables instead of flex/grid.
--}}
<style>
    .invoice-wrap {
        font-family: 'Roboto', 'DejaVu Sans', sans-serif;
        font-size: 11px;
        color: #1a1a1a;
        background: #fff;
        width: 210mm;
        margin: 0 auto;
        padding: 14mm 14mm 10mm;
        box-sizing: border-box;
    }
    .invoice-wrap table { border-collapse: collapse; }

    /* ── FISCAL BANNER ── */
    .fiscal-banner { width: 100%; border: 1.5px solid #1a1a1a; margin-bottom: 14px; }
    .fiscal-banner td { padding: 6px 10px; vertical-align: top; }
    .fiscal-banner td.qr { width: 84px; padding-right: 0; }
    .fiscal-banner .fiscal-text { font-size: 9.5px; line-height: 1.6; }
    .fiscal-banner .fiscal-title { font-weight: 700; font-size: 11px; margin-bottom: 3px; letter-spacing: 0.5px; text-align: center; }
    .fiscal-banner .fiscal-url { word-break: break-all; color: #0057b7; }

    /* ── HEADER ── */
    .header-row { width: 100%; margin-bottom: 14px; }
    .header-row td { vertical-align: top; padding: 0; }
    .company-block .company-name {
        font-family: 'Roboto Condensed', 'DejaVu Sans Condensed', sans-serif;
        font-weight: 700;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }
    .company-block p { margin: 1px 0; line-height: 1.55; color: #333; }
    .company-block a { color: #0057b7; text-decoration: none; }
    .company-block img { max-height: 60px; max-width: 170px; margin-bottom: 6px; }

    .invoice-meta { text-align: right; width: 230px; }
    .invoice-meta .doc-title { font-weight: 700; font-size: 12px; letter-spacing: 1px; color: #1a1a1a; }
    .invoice-meta .invoice-number {
        font-family: 'Roboto Condensed', 'DejaVu Sans Condensed', sans-serif;
        font-size: 22px;
        font-weight: 700;
        color: #0057b7;
        letter-spacing: 1px;
    }
    .invoice-meta table { margin-left: auto; }
    .invoice-meta td { padding: 1px 4px; line-height: 1.55; text-align: right; }
    .invoice-meta td.k { font-weight: bold; color: #555; }
    .invoice-meta td.v { min-width: 110px; }

    /* ── CUSTOMER ── */
    .customer-section { margin-bottom: 14px; }
    .customer-section table { width: 60%; }
    .customer-section td { padding: 1.5px 6px 1.5px 0; line-height: 1.55; vertical-align: top; }
    .customer-section td.k { font-weight: bold; color: #555; white-space: nowrap; width: 120px; }
    .customer-section .customer-name-value { font-weight: 700; color: #c00; }

    /* ── ITEMS ── */
    .items-table { width: 100%; margin-bottom: 16px; font-size: 9.5px; }
    .items-table thead th {
        background: #1a1a1a;
        color: #fff;
        padding: 5px 5px;
        text-align: center;
        font-weight: bold;
        letter-spacing: 0.2px;
        border: 1px solid #333;
        line-height: 1.35;
    }
    .items-table tbody td { padding: 5px 5px; text-align: center; border: 1px solid #ddd; vertical-align: middle; }
    .items-table tbody tr.even td { background: #f7f7f7; }
    .items-table td.text-left { text-align: left; }
    .items-table td.num { text-align: right; white-space: nowrap; }

    /* ── TOTALS ── */
    .totals-section { width: 100%; margin-bottom: 20px; }
    .totals-section td.spacer { width: auto; }
    .totals-table { min-width: 220px; }
    .totals-table td { padding: 3px 8px; border: 1px solid #ccc; text-align: right; white-space: nowrap; }
    .totals-table td.k { font-weight: bold; background: #f0f0f0; }
    .totals-table tr.total-final td { background: #1a1a1a; color: #fff; font-weight: 700; font-size: 12px; }

    .notes { margin-bottom: 14px; font-size: 10px; color: #444; line-height: 1.6; }
    .notes .section-label { margin-bottom: 2px; }

    /* ── FOOTER ── */
    .invoice-footer { margin-top: 30px; border-top: 1px solid #ccc; padding-top: 10px; font-size: 10px; color: #444; }
    .footer-grid { width: 100%; }
    .footer-grid > tbody > tr > td { width: 50%; vertical-align: top; padding: 0 16px 0 0; }
    .section-label { font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; font-size: 9px; color: #999; margin-bottom: 4px; }
    .footer-grid p { margin: 1px 0; line-height: 1.55; }
    .stamp-box {
        border: 1px dashed #bbb;
        height: 60px;
        line-height: 60px;
        text-align: center;
        color: #bbb;
        font-size: 9px;
        letter-spacing: 1px;
        text-transform: uppercase;
        margin-top: 4px;
    }
    .doc-footer-text { text-align: center; font-size: 9px; color: #888; margin-top: 12px; }

    @media print {
        .invoice-wrap { width: 100%; padding: 10mm; margin: 0; }
    }
</style>
