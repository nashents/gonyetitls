<?php

namespace App\Mail;

use App\Models\FreightJobReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Courtesy version of a freight job follow-up for the client's contacts and
 * any extra addresses: no internal link or notes, replies go to the person
 * who set the reminder. Not queued. Never name properties $from / $to.
 */
class FreightJobClientReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $company;
    public $reminder;
    public $contactName;

    public function __construct($company, FreightJobReminder $reminder, ?string $contactName = null)
    {
        $this->company = $company;
        $this->reminder = $reminder;
        $this->contactName = $contactName;
    }

    public function build()
    {
        $job = $this->reminder->freight_job;
        $ref = $job->customer_reference ? " (your ref {$job->customer_reference})" : '';

        $mail = $this->view('emails.freight_job_client_reminder', [
                'company' => $this->company,
                'reminder' => $this->reminder,
                'job' => $job,
                'contactName' => $this->contactName,
                'daysLeft' => $this->reminder->daysLeft(),
            ])
            ->subject("Reminder: {$this->reminder->title} - {$job->job_number}{$ref}");

        if ($this->company->noreply) {
            $mail->from($this->company->noreply, $this->company->name);
        }

        $owner = $this->reminder->user;
        if ($owner && filter_var($owner->email, FILTER_VALIDATE_EMAIL)) {
            $mail->replyTo($owner->email, trim($owner->name . ' ' . $owner->surname));
        }

        return $mail;
    }
}
