<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Document hand-over notifications (DocumentCustodyNotifier). Not queued -
 * there is no queue worker; the notifier sends after the HTTP response.
 * Never name properties $from / $to (Laravel's recipient arrays).
 */
class DocumentCustodyMail extends Mailable
{
    use Queueable, SerializesModels;

    public $company;
    public $movement;
    public $event;
    public $headline;
    public $actionUrl;
    public $actionLabel;
    public $recipientName;

    public function __construct($company, $movement, string $event, string $headline, string $actionUrl, string $actionLabel, string $recipientName)
    {
        $this->company = $company;
        $this->movement = $movement;
        $this->event = $event;
        $this->headline = $headline;
        $this->actionUrl = $actionUrl;
        $this->actionLabel = $actionLabel;
        $this->recipientName = $recipientName;
    }

    public function build()
    {
        $mail = $this->view('emails.document_custody')
            ->subject($this->headline . ': ' . $this->movement->document->title);

        if ($this->company && $this->company->noreply) {
            $mail->from($this->company->noreply, $this->company->name);
        }

        return $mail;
    }
}
