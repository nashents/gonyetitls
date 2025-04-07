<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendEmails extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $subject;
    public $body;
    public $company;
    public $attachment;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data, $attachment)
    {
        $this->subject = $data['subject'];
        $this->body = $data['body'];
        $this->company = Auth::user()->employee->company;
        $this->attachment = $attachment;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.send_emails')
                ->from(Auth::user()->employee->email)
                ->subject($this->subject)
                ->attach(public_path('/myfiles/attachments/'.$this->attachment->filename));
    }
}
