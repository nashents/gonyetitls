<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\SubscriptionInvoice;
use Illuminate\Mail\Mailable;

/**
 * A Gonyeti license invoice, or a payment reminder for one, with the invoice
 * PDF attached. Sent synchronously (the PDF bytes must not be serialized onto
 * a queue).
 */
class SubscriptionInvoiceMail extends Mailable
{
    public SubscriptionInvoice $invoice;
    public string $type;            // invoice | reminder
    public ?string $recipientName;
    public ?Company $issuer;

    private string $pdf;
    private string $filename;

    public function __construct(SubscriptionInvoice $invoice, string $type, ?string $recipientName, string $pdf, string $filename, ?Company $issuer)
    {
        $this->invoice = $invoice;
        $this->type = $type;
        $this->recipientName = $recipientName;
        $this->pdf = $pdf;
        $this->filename = $filename;
        $this->issuer = $issuer;
    }

    public function build()
    {
        $invoice = $this->invoice;

        $subject = $this->type === 'reminder'
            ? ($invoice->isOverdue() ? 'Overdue: ' : 'Payment reminder: ') . "Gonyeti TLS invoice {$invoice->invoice_number}"
            : "Gonyeti TLS invoice {$invoice->invoice_number} - " . $invoice->issue_date->format('F Y');

        $mail = $this->subject($subject)
            ->view('emails.subscription_invoice')
            ->attachData($this->pdf, $this->filename, ['mime' => 'application/pdf']);

        if ($this->issuer && $this->issuer->noreply) {
            $mail->from($this->issuer->noreply, $this->issuer->name);
        }

        return $mail;
    }
}
