<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendCustomerStatementMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * Note: $from / $to are Mailable's own sender/recipient arrays, so the
     * statement period lives in $periodFrom / $periodTo. Assigning a date
     * string to $to caused "[] operator not supported for strings".
     *
     * @return void
     */
    public $filePath;
    public $type;
    public $periodFrom;
    public $periodTo;
    public $company;
    public $customer;

    public function __construct($data, $filePath)
    {
        $this->filePath = $filePath;
        $this->customer = $data['customer'];
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
       return $this->view('emails.customer_statement')
                    ->from($this->company->noreply ?: config('mail.from.address'), $this->company->name)
                    ->subject($this->company->name.' '.date('M Y').' Statement for '. $this->customer->name)
                    ->attach(Storage::path($this->filePath), [
                        'as' => 'Statement - '.$this->customer->name.'.pdf',
                        'mime' => 'application/pdf',
                    ]);
    }
}
