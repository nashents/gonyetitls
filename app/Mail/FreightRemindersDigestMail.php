<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Daily freight operations digest (FreightReminderService::sendDailyDigests).
 * Deliberately not queued: it is already sent from a background process, and
 * this way it doesn't depend on a queue worker running.
 */
class FreightRemindersDigestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $company;
    public $reminders;

    public function __construct($company, array $reminders)
    {
        $this->company = $company;
        $this->reminders = $reminders;
    }

    public function build()
    {
        $overdue = collect($this->reminders)->where('severity', 'overdue')->count();

        return $this->view('emails.freight_reminders_digest')
            ->from($this->company->noreply)
            ->subject('Freight Reminders: ' . count($this->reminders) . ' item(s) need attention' . ($overdue ? " ({$overdue} overdue)" : ''));
    }
}
