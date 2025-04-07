<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\Fitness;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $company;
    public $fitness;
    public $user;

    public function __construct(Fitness $fitness,Company $company)
    {
        $this->company = $company;
        $this->fitness = $fitness;
        $this->user = $fitness->user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from($this->company->noreply)
                    ->cc($this->user->email)
                    ->view('emails.reminder')
                    ->with([
                        'company' => $this->company,
                        'fitness' => $this->fitness,
                    ]);
    }
}
