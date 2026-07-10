<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendRemittanceAdviceMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $filePath;
    public $payment;
    public $company;
    public $vendor;

    public function __construct($data, $filePath)
    {
        $this->filePath = $filePath;
        $this->vendor = $data['vendor'];
        $this->company = $data['company'];
        $this->payment = $data['payment'];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.remittance_advice')
                    ->from($this->company->noreply)
                    ->subject($this->company->name.' Remittance Advice '.$this->payment->payment_number.' for '.$this->vendor->name)
                    ->attach(Storage::path($this->filePath));
    }
}
