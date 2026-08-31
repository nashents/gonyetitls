<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomerPortalCredentialsMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $customer;
    public $company;
    public $pin;
    public $loginUrl;

    public function __construct($customer, $company, $pin, $loginUrl)
    {
        $this->customer = $customer;
        $this->company = $company;
        $this->pin = $pin;
        $this->loginUrl = $loginUrl;
    }

    public function build()
    {
        return $this->view('emails.customer-portal-credentials')
                    ->from($this->company?->noreply)
                    ->subject('Your Freight Portal Login');
    }
}
