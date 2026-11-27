<?php

namespace App\Mail;

use App\Models\FreightJobReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Internal (staff) email for a freight job follow-up (FreightJobReminderService).
 * Not queued - it already runs in a background process. Never name properties
 * $from / $to (Mailable uses them).
 */
class FreightJobReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $company;
    public $reminder;

    public function __construct($company, FreightJobReminder $reminder)
    {
        $this->company = $company;
        $this->reminder = $reminder;
    }

    public function build()
    {
        $job = $this->reminder->freight_job;
        $days = $this->reminder->daysLeft();
        $when = $days < 0 ? 'OVERDUE' : ($days === 0 ? 'due today' : 'due ' . $this->reminder->due_at->format('d M Y'));

        $mail = $this->view('emails.freight_job_reminder', [
                'company' => $this->company,
                'reminder' => $this->reminder,
                'job' => $job,
                'daysLeft' => $days,
            ])
            ->subject("Follow-up {$when}: {$this->reminder->title} - {$job->job_number}");

        if ($this->company->noreply) {
            $mail->from($this->company->noreply, $this->company->name);
        }

        return $mail;
    }
}
