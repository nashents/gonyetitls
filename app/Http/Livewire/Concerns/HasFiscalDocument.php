<?php

namespace App\Http\Livewire\Concerns;

use App\Models\FiscalDocument;
use App\Services\FiscalisationService;

/**
 * Fiscalisation UI state for a document preview (invoice templates, credit
 * note preview), whichever fiscal provider is configured. State is read from
 * fiscal_documents, never stored on the source document. The using component
 * supplies fiscalDocument().
 */
trait HasFiscalDocument
{
    public bool    $fiscalLoading     = false;
    public string  $fiscalMessage     = '';
    public string  $fiscalMessageType = '';      // success | error | warning
    public string  $fiscalStatus      = 'not_fiscalised';
    public ?string $fiscalRequestId   = null;
    public ?string $fiscalPdfFile     = null;
    public ?string $fiscalQrUrl       = null;
    public ?string $fiscalVerifyCode  = null;
    public ?string $fiscalDay         = null;
    public ?string $fiscalRaInvoiceNo = null;
    public ?string $fiscalError       = null;

    abstract protected function fiscalDocument(): ?FiscalDocument;

    protected function loadFiscalState(): void
    {
        $doc = $this->fiscalDocument();

        if (! $doc) {
            $this->fiscalStatus = 'not_fiscalised';
            return;
        }

        $this->fiscalStatus      = $doc->statusLabel();
        $this->fiscalRequestId   = $doc->request_id;
        $this->fiscalPdfFile     = $doc->fiscal_invoice_pdf;
        $this->fiscalQrUrl       = $doc->qr_code_url;
        $this->fiscalVerifyCode  = $doc->verification_code;
        $this->fiscalDay         = $doc->fiscal_day;
        $this->fiscalRaInvoiceNo = $doc->ra_invoice_number;
        $this->fiscalError       = $doc->error_message;
    }

    /** wire:click="pollFiscalStatus" */
    public function pollFiscalStatus()
    {
        $doc = $this->fiscalDocument();

        if (! $doc) {
            $this->setFiscalMessage('This document has not been fiscalised yet.', 'error');
            return;
        }

        $this->applyFiscalResult(app(FiscalisationService::class)->refresh($doc));
    }

    public function downloadFiscalPdf()
    {
        if (! $this->fiscalPdfFile) {
            $this->setFiscalMessage('No fiscal PDF available yet.', 'error');
            return;
        }

        $doc   = $this->fiscalDocument();
        $bytes = $doc ? rescue(fn () => app(FiscalisationService::class)->downloadPdf($doc), null) : null;

        if (! $bytes) {
            $this->setFiscalMessage('PDF download failed.', 'error');
            return;
        }

        return response()->streamDownload(
            fn () => print($bytes),
            $this->fiscalPdfFile,
            ['Content-Type' => 'application/pdf']
        );
    }

    protected function applyFiscalResult(array $result): void
    {
        $this->setFiscalMessage($result['message'], $result['type']);
        $this->loadFiscalState();
    }

    protected function setFiscalMessage(string $msg, string $type): void
    {
        $this->fiscalMessage     = $msg;
        $this->fiscalMessageType = $type;
    }
}
