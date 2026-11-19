<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendingInvoiceMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $invoice;
    public $invoice_items;
    public $company;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($invoice, $invoice_items, $company)
    {
        $this->invoice = $invoice;
        $this->invoice_items = $invoice_items;
        $this->company = $company;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // Cover message in the standard email layout + the invoice itself as a
        // PDF in the company's chosen layout (same as Export as PDF).
        $pdf = app(\App\Services\Invoices\InvoicePdf::class);

        return $this->view('emails.invoice')
                    ->from($this->company->noreply, $this->company->name)
                    ->subject('Invoice ' . $this->invoice->invoice_number . ' from ' . $this->company->name)
                    ->attachData($pdf->make($this->invoice)->output(), $pdf->filename($this->invoice), ['mime' => 'application/pdf']);
    }
}
