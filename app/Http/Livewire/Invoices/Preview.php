<?php

namespace App\Http\Livewire\Invoices;

use App\Models\Invoice;
use App\Models\FiscalDocument;
use App\Http\Livewire\Concerns\HasFiscalDocument;
use App\Services\FiscalisationService;
use Livewire\Component;
use App\Mail\SendingInvoiceMail;
use Illuminate\Support\Facades\Mail;

class Preview extends Component
{
    use HasFiscalDocument;

    public $invoice;
    public $invoice_items;
    public $company;
    public $email;

    public function mount($invoice, $invoice_items, $company)
    {
        $this->invoice       = $invoice;
        $this->invoice_items = $invoice_items;
        $this->company       = $company;
        $this->loadFiscalState();
    }

    // ─────────────────────────────────────────────────────────────
    // FISCALIZE INVOICE  (status poll / PDF download: HasFiscalDocument)
    // ─────────────────────────────────────────────────────────────

    protected function fiscalDocument(): ?FiscalDocument
    {
        return FiscalDocument::forInvoice($this->invoice->id)->latest()->first();
    }

    public function fiscalizeInvoice($id)
    {
        $this->fiscalLoading = true;
        $this->fiscalMessage = '';

        try {
            $result = app(FiscalisationService::class)->fiscaliseInvoice(Invoice::findOrFail($id));
            $this->invoice = Invoice::find($this->invoice->id);
            $this->applyFiscalResult($result);
        } catch (\Exception $e) {
            $this->setFiscalMessage('Error: ' . $e->getMessage(), 'error');
        } finally {
            $this->fiscalLoading = false;
        }
    }

    // ─────────────────────────────────────────────────────────────
    // EXISTING METHODS (unchanged)
    // ─────────────────────────────────────────────────────────────

    public function sendEmail($id)
    {
        $invoice       = Invoice::find($id);
        $invoice_items = $invoice->invoice_items;
        $company       = $invoice->company;
        $this->email   = $invoice->customer?->email ?? '';

        if ($this->email !== '') {
            try {
                Mail::to($this->email)->send(new SendingInvoiceMail($invoice, $invoice_items, $company));
            } catch (\Exception $e) {
                $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => 'Something went wrong while sending email!!']);
            }
        }

        return redirect()->back();
    }

    public function print($id)
    {
        return view('invoices.print')->with([
            'invoice'       => $this->invoice,
            'company'       => $this->company,
            'invoice_items' => $this->invoice_items,
        ]);
    }

    public function generatePdf($id)
    {
        return app(\App\Services\Invoices\InvoicePdf::class)->download($this->invoice);
    }

    public function render()
    {
        return view('livewire.invoices.preview');
    }
}
