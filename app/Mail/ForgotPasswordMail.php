<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ForgotPasswordMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $link;
    public $company;

   public function __construct($user, $company, $link)
   {
           $this->link = $link;
           $this->user = $user;
           $this->company = $company;
   }

   /**
    * Build the message.
    *
    * @return $this
    */
   public function build()
   {
       return $this->view('emails.forgot-password')
                   ->from($this->company->noreply)
                   ->subject('Password Reset Email');
   }
}
