{{-- Legacy view name — the classic PDF now lives in invoices.pdf (shared layout partials). --}}
@include('invoices.pdf', [
    'layout'         => 'classic',
    'fiscalDocument' => $fiscalDocument ?? \App\Models\FiscalDocument::forInvoice($invoice->id)->latest()->first(),
])
