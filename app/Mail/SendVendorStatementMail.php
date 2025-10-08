<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendVendorStatementMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $filePath;
    public $type;
    public $from;
    public $to;
    public $company;
    public $vendor;

    public function __construct($data, $filePath)
    {
        $this->filePath = $filePath;
        $this->vendor = $data['vendor'];
        $this->company = $data['company'];
        $this->from = $data['from'];
        $this->to = $data['to'];
        $this->type = $data['selectedType'];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
       return $this->view('emails.vendor_statement')
                    ->from($this->company->noreply)
                    ->subject($this->company->name.' '.date('M Y').' Statement for '. $this->vendor->name)
                    ->attach(Storage::path($this->filePath));
    }
}
