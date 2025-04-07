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
        return $this->view('emails.invoice')
                    ->from($this->company->noreply)
                    ->subject('Invoice Notification Email');
    }
}
