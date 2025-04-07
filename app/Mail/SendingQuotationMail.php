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
        return $this->view('emails.quotation')
                    ->from($this->company->noreply)
                    ->subject('Quotation Notification Email');
    }
}
