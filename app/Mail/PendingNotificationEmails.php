<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PendingNotificationEmails extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    public $notification;
    public $company;
    public $model;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($company, $notification, $model)
    {
        $this->notification = $notification;
        $this->company = $company;
        $this->model = $model;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.pending_notifications')
        ->from($this->company->noreply)
        ->subject($this->notification->category ?? 'Pending Notification' . ' Email');
    }
}
