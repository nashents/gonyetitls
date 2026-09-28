{{--
    Stylesheet for dompdf-rendered documents (Export to PDF).

    dompdf does not load Bootstrap and has no flexbox support, so the web
    layout's .row/.col grid and helper classes (text-center, text-right, ...)
    silently did nothing and templates compensated with negative margins.
    Everything here uses table layout so PDFs match the browser print view.
--}}
@php
    $pdfColor = (isset($company) && $company && $company->color)
        ? $company->color
        : (optional(optional(optional(Auth::user())->employee)->company)->color
            ?? optional(optional(Auth::user())->company)->color
            ?? '#000000');
@endphp
<style>
    @page { margin: 30px 35px 70px 35px; }

    body {
        margin: 0;
        background-color: #fff;
        font-family: "DejaVu Sans", sans-serif;
        font-size: 11px;
        line-height: 1.45;
        color: #212529;
    }

    /* Neutralise the web wrappers */
    .container, .card, .card-body { margin: 0; padding: 0; border: 0; box-shadow: none; background: #fff; }
    #invoice { padding: 0; font-size: 11px !important; }
    .invoice { position: relative; background-color: #fff; padding: 0; }

    h1, h2, h3, h4, h5, h6 { margin: 0 0 4px 0; font-weight: bold; line-height: 1.25; }
    h2 { font-size: 20px; font-weight: normal; letter-spacing: .5px; }
    h3 { font-size: 15px; }
    h4 { font-size: 15px; }
    h6 { font-size: 11px; }
    center { display: block; text-align: center; }

    /* Bootstrap grid replacement: rows become tables, cols become cells */
    .row { display: table; width: 100%; table-layout: fixed; margin: 0; }
    .row > .col, .row > [class^="col-"], .row > [class*=" col-"] {
        display: table-cell;
        vertical-align: top;
        padding: 0;
    }

    /* Bootstrap helpers used by the templates */
    .text-left, .text-start { text-align: left !important; }
    .text-center { text-align: center !important; }
    .text-right, .text-end { text-align: right !important; }
    .text-gray-light, .text-muted { color: #6c757d; }
    .font-weight-bold, .fw-bold { font-weight: bold; }
    .overflow-auto { overflow: visible; }
    .d-none { display: none; }

    /* Header */
    .invoice header {
        padding: 0 0 10px 0;
        margin: 0 0 15px 0;
        border-bottom: 1px solid {{ $pdfColor }};
    }
    .invoice header img { max-width: 170px; max-height: 110px; }
    .invoice .company-details { text-align: right; }
    .invoice .company-details .name { margin: 0 0 2px 0; font-size: 15px; color: {{ $pdfColor }}; }

    /* Bill-to / document details */
    .invoice .contacts { margin-bottom: 18px; }
    .invoice .invoice-to { text-align: left; }
    .invoice .invoice-to .to { margin: 0; font-size: 11px; }
    .invoice .invoice-details { text-align: right; }
    .invoice .invoice-details .invoice-id { margin-top: 0; color: {{ $pdfColor }}; }

    .invoice main { padding-bottom: 20px; }

    .invoice main .notices {
        border-left: 5px solid {{ $pdfColor }};
        background: #e7f2ff;
        padding: 8px 10px;
        margin-bottom: 15px;
    }
    .invoice main .notices .notice { font-size: 11px; }

    /* Tables */
    .invoice table {
        width: 100%;
        border-collapse: collapse;
        border-spacing: 0;
        margin-bottom: 18px;
    }
    .invoice table td,
    .invoice table th {
        padding: 7px 8px;
        background: #fff;
        vertical-align: top;
        border: 0;
    }
    .invoice table thead th {
        font-weight: bold;
        font-size: 10.5px;
        white-space: nowrap;
        border-bottom: 1px solid #dee2e6;
    }
    .invoice table tbody td { border-bottom: 1px solid #f0f0f0; }
    .invoice table tbody tr:last-child td { border-bottom: 0; }
    .invoice table td h3 { margin: 0; font-weight: normal; font-size: 12px; color: {{ $pdfColor }}; }

    .invoice table .qty,
    .invoice table .unit,
    .invoice table .total { font-size: 11px; background: #fff; }
    .invoice table .no { color: #fff; background: {{ $pdfColor }}; }
    .invoice table .total { color: #212529; }

    .invoice table tfoot td {
        background: #fff;
        white-space: nowrap;
        text-align: right;
        font-weight: bold;
        font-size: 12px;
        padding: 8px;
        border-top: 1px solid #212529;
    }
    .invoice table tfoot tr:first-child td { border-top: 0; }
    .invoice table tfoot tr:last-child td {
        color: {{ $pdfColor }};
        font-size: 14px;
        border-bottom: 1px solid #212529;
    }

    .invoice footer {
        width: 100%;
        text-align: center;
        color: #777;
        border-top: 1px solid #aaa;
        padding: 6px 0;
        font-size: 10px;
    }
</style>
