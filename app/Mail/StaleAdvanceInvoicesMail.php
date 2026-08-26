<?php

namespace App\Mail;

use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;

class StaleAdvanceInvoicesMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Collection $invoices;
    public ?Company $company;
    public int $agingDays;

    public function __construct(Collection $invoices, ?Company $company, int $agingDays)
    {
        $this->invoices = $invoices;
        $this->company = $company;
        $this->agingDays = $agingDays;
    }

    public function build()
    {
        $mail = $this->subject('Unrecognized Advance Invoices — ' . $this->invoices->count() . ' outstanding')
            ->view('emails.stale_advance_invoices');

        if ($this->company && $this->company->noreply) {
            $mail->from($this->company->noreply);
        }

        return $mail;
    }
}
