<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AuthorizationNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    public $user;
    public $company;
    public $model;
    public $notification;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($company, $notification , $user, $model)
    {
        $this->user = $user;
        $this->company = $company;
        $this->notification = $notification;
        $this->model = $model;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.authorization_notifications')
        ->from($this->company->noreply)
        ->subject($this->notification .' Email');
    }
}
