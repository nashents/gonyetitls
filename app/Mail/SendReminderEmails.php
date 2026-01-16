<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\Fitness;
use App\Models\ReminderItem;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendReminderEmails extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public $company;
    public $fitness;
    public $reminder_item;
    public $user;

    public function __construct(Fitness $fitness)
    {
        $this->company = $fitness->company;
        $this->fitness = $fitness;
        $this->reminder_item = ReminderItem::find($fitness->reminder_item_id);
        $this->user = $fitness->user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = ucfirst($this->reminder_item->name)." Reminder: expires on {$this->fitness->expires_at->format('d M Y')}";

        return $this->view('emails.reminders')
            ->from($this->company->noreply, $this->company->name ?? null)
            ->subject($subject)
            ->cc($this->company->email);
            }
}
