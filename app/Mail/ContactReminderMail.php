<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\Fitness;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Customer-facing expiry reminder sent to a customer's / vendor's contacts
 * (Fitness::notify_contacts). Unlike SendReminderEmails it has no link into
 * the system and never carries the internal copy (CC) list. Replies go to
 * the staff member who set the reminder.
 *
 * Not queued: sent from the reminder:send command, so it doesn't depend on
 * a queue worker. Never name properties $from / $to.
 */
class ContactReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $company;
    public $fitness;
    public $contactName;
    public $partyName;

    public function __construct(Fitness $fitness, string $contactName)
    {
        $this->fitness = $fitness;
        $this->contactName = $contactName;
        $this->company = $fitness->company
            ?? optional(optional($fitness->user)->employee)->company
            ?? Company::first();
        $this->partyName = optional($fitness->customer)->name ?? optional($fitness->vendor)->name ?? '';
    }

    public function build()
    {
        $item = optional($this->fitness->reminder_item)->name ?: 'Document';
        $mail = $this->view('emails.contact_reminder')
            ->subject("{$item} expiring on " . $this->fitness->expires_at->format('d M Y') . ($this->partyName ? " - {$this->partyName}" : ''));

        if ($this->company && $this->company->noreply) {
            $mail->from($this->company->noreply, $this->company->name);
        }

        $replyTo = optional($this->fitness->user)->email;
        if ($replyTo && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            $mail->replyTo($replyTo, trim($this->fitness->user->name . ' ' . $this->fitness->user->surname));
        }

        return $mail;
    }
}
