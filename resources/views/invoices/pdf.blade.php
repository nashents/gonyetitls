{{--
    Export as PDF (dompdf) for every invoice layout. Renders the SAME document
    partial as the on-screen preview / browser print (invoices.layouts.{layout}),
    so the PDF keeps the company's chosen design plus the fiscal data.
    Built by App\Services\Invoices\InvoicePdf. Expects $layout, $invoice,
    $company, $invoice_items, $fiscalDocument.
--}}
<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice {{ $invoice->invoice_number }}</title>
    @if ($layout === 'classic')
        @include('includes.pdf_css')
    @else
        @include("invoices.layouts.{$layout}-styles")
        <style>
            /* dompdf: no web fonts, page-wide document, no CSS filters */
            body { margin: 0; background: #fff; font-family: "DejaVu Sans", sans-serif; }
            .inv-wrap, .invoice-wrap { width: 100%; font-family: "DejaVu Sans", sans-serif; }
            .company-block .company-name, .invoice-meta .invoice-number { font-family: "DejaVu Sans Condensed", "DejaVu Sans", sans-serif; }
            @if ($layout === 'modern')
                @page { margin: 0 0 30px 0; }
                .inv-wrap { padding-bottom: 0; }
                /* The screen view inverts the logo to white; dompdf can't, so give it a white tile. */
                .inv-logo-wrap img { filter: none; background: #fff; padding: 4px; border-radius: 4px; }
            @else
                /* dompdf adds padding on top of width:100%, so the page margin does the spacing. */
                @page { margin: 12mm 10mm 10mm 10mm; }
                .invoice-wrap { width: auto; padding: 0; }
                .items-table { font-size: 8px; }
                .items-table thead th, .items-table tbody td { padding: 4px 3px; }
            @endif
        </style>
    @endif
</head>
<body>
    @if ($layout === 'classic')
        <div class="container">
            <div class="card">
                <div class="card-body">
                    <div id="invoice">
                        @include('invoices.layouts.classic', ['pdf' => true])
                    </div>
                </div>
            </div>
        </div>
    @else
        @include("invoices.layouts.{$layout}", ['pdf' => true])
    @endif
</body>
</html>
