<?php

namespace App\Services\Invoices;

use App\Models\Company;
use App\Models\FiscalDocument;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Export as PDF for invoices, in the company's chosen layout (Business
 * Settings → invoice template). Uses the same document partials as the
 * on-screen preview and browser print (resources/views/invoices/layouts), so
 * all three outputs match and carry the fiscal data once fiscalised.
 */
class InvoicePdf
{
    public const LAYOUTS = ['classic', 'modern', 'transport'];

    public static function layoutFor(?Company $company): string
    {
        return in_array($company?->invoice_template, self::LAYOUTS, true)
            ? $company->invoice_template
            : 'classic';
    }

    /** @return \Barryvdh\DomPDF\PDF */
    public function make(Invoice $invoice, ?string $layout = null)
    {
        $company = $invoice->company;
        $layout  = in_array($layout, self::LAYOUTS, true) ? $layout : static::layoutFor($company);

        return Pdf::loadView('invoices.pdf', [
            'layout'         => $layout,
            'invoice'        => $invoice,
            'company'        => $company,
            'invoice_items'  => $invoice->invoice_items,
            'fiscalDocument' => FiscalDocument::forInvoice($invoice->id)->latest()->first(),
        ])->setPaper('a4');
    }

    public function filename(Invoice $invoice): string
    {
        return preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) ($invoice->invoice_number ?: 'invoice-' . $invoice->id)) . '.pdf';
    }

    public function download(Invoice $invoice, ?string $layout = null)
    {
        return $this->make($invoice, $layout)->download($this->filename($invoice));
    }
}
