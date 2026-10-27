<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class AccountCreationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

     public $user;
     public $pin;
     public $company;
     public $shared_by_name;

    public function __construct($user, $company, $pin)
    {
            $this->pin = $pin;
            $this->user = $user;
            $this->company = $company;
            // Resolved now (in the request) because the mail is queued
            $sharer = Auth::user();
            $this->shared_by_name = $sharer ? trim(ucfirst($sharer->name).' '.ucfirst($sharer->surname)) : null;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.credentials')
                    ->from($this->company?->noreply)
                    ->subject('Account Creation Email');
    }
}
