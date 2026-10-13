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
     * $vendor is the statement's counterparty - a Vendor or a Transporter.
     * The period lives in $periodFrom / $periodTo because $from / $to are
     * Mailable's own sender/recipient arrays.
     *
     * @return void
     */
    public $filePath;
    public $type;
    public $periodFrom;
    public $periodTo;
    public $company;
    public $vendor;

    public function __construct($data, $filePath)
    {
        $this->filePath = $filePath;
        $this->vendor = $data['vendor'];
        $this->company = $data['company'];
        $this->periodFrom = $data['from'] ?? null;
        $this->periodTo = $data['to'] ?? null;
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
                    ->from($this->company->noreply ?: config('mail.from.address'), $this->company->name)
                    ->subject($this->company->name.' '.date('M Y').' Statement for '. $this->vendor->name)
                    ->attach(Storage::path($this->filePath), [
                        'as' => 'Statement - '.$this->vendor->name.'.pdf',
                        'mime' => 'application/pdf',
                    ]);
    }
}
