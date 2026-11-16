<?php

namespace App\Services\Billing;

use App\Models\Company;
use App\Models\SubscriptionInvoice;
use Illuminate\Support\HtmlString;

/**
 * Presents a Gonyeti license invoice with the fields the shared CLASSIC
 * invoice layout (resources/views/invoices/layouts/classic.blade.php) reads
 * from a sales Invoice, so license invoices use that one layout instead of a
 * copy of it. The client company stands in as the "customer".
 */
class ClassicInvoiceDocument
{
    public $invoice_number;
    public $date;
    public $expiry;
    public $currency;
    public $customer;
    public $invoice_items;
    public $bank_accounts;
    public $total;
    public $tax_amount = 0;
    public $discount = null;
    public $memo;
    public $footer;
    public $sales_order_number = null;
    public $purchase_order_number = null;
    public $custom_ref = null;

    public function __construct(SubscriptionInvoice $invoice, ?Company $issuer)
    {
        $this->invoice_number = $invoice->invoice_number;
        $this->date = $invoice->issue_date->format('Y-m-d');
        $this->expiry = $invoice->due_date->format('Y-m-d');
        $this->currency = $invoice->currency;
        $this->customer = $invoice->company;
        $this->total = $invoice->amount;
        $this->footer = $issuer?->invoice_footer;
        $this->bank_accounts = collect();

        $this->invoice_items = collect([(object) [
            'product' => null,
            'tax' => null,
            'hs_code' => null,
            'description' => $invoice->description,
            'qty' => 1,
            'amount' => $invoice->amount,
            'subtotal' => $invoice->amount,
            'tax_amount' => 0,
            'subtotal_incl' => $invoice->amount,
        ]]);

        $this->memo = $this->notes($invoice, $issuer);
    }

    /** License invoices are never fiscal documents. */
    public function showsFiscalHeading(): bool
    {
        return false;
    }

    /**
     * The layout's "Notes / Terms & Conditions" block: what has been paid,
     * what is still due and how to pay. Escaped here, so safe as HTML.
     */
    private function notes(SubscriptionInvoice $invoice, ?Company $issuer): ?HtmlString
    {
        $lines = [];

        if ($invoice->status === SubscriptionInvoice::STATUS_CANCELLED) {
            $lines[] = 'This invoice has been CANCELLED.';
        } elseif ($invoice->balance <= 0) {
            $lines[] = 'PAID IN FULL' . ($invoice->paid_at ? ' on ' . $invoice->paid_at->format('d M Y') : '') . '. Thank you.';
        } else {
            if ($invoice->amount_paid > 0) {
                $lines[] = 'Payments received: ' . $invoice->money($invoice->amount_paid) . '. Balance due: ' . $invoice->money($invoice->balance) . '.';
            }
            if ($issuer?->billing_payment_details) {
                $lines[] = $issuer->billing_payment_details;
            }
            $lines[] = 'Please use ' . $invoice->invoice_number . ' as your payment reference.';
        }

        return $lines ? new HtmlString(nl2br(e(implode("\n", $lines)))) : null;
    }
}
