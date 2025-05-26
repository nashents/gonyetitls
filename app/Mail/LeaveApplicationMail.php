<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class LeaveApplicationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $leave;
    public $company;
    public $hod;
    public $manager;
    public function __construct($company, $leave)
    {
        $this->manager = User::find($leave->management_id);
        $this->hod = User::find($leave->hod_id);
        $this->company = $company;
        $this->leave = $leave;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
         return $this->view('emails.leave_copy')
                    ->from($this->company->noreply)
                    ->subject($this->leave->employee->name." ".$this->leave->employee->surname . " Leave Application Copy");
    }
}
