<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendingQuotationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $quotation;
    public $quotation_products;
    public $company;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($quotation, $quotation_products, $company)
    {
        $this->quotation = $quotation;
        $this->quotation_products = $quotation_products;
        $this->company = $company;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // Cover message in the standard email layout + the quotation PDF
        // (same document as QuotationController::generatePDF).
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('quotations.quotation', [
            'quotation' => $this->quotation,
            'quotation_items' => $this->quotation->quotation_items,
            'company' => $this->company,
            'document' => 'quotation',
        ]);
        $filename = preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) ($this->quotation->quotation_number ?: 'quotation-' . $this->quotation->id)) . '.pdf';

        return $this->view('emails.quotation')
                    ->from($this->company->noreply, $this->company->name)
                    ->subject('Quotation ' . $this->quotation->quotation_number . ' from ' . $this->company->name)
                    ->attachData($pdf->output(), $filename, ['mime' => 'application/pdf']);
    }
}
