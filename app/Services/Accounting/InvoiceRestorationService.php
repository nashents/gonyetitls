<?php

namespace App\Services\Accounting;

use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class InvoiceRestorationService
{
    public function __construct(
        protected JournalReversalService $journalReversal,
        protected PaymentRestorationService $paymentRestoration,
        protected BillRestorationService $billRestoration
    ) {
    }

    /**
     * Restore a soft-deleted invoice, reapplying every account/wallet
     * balance change and journal entry that InvoiceDeletionService reversed,
     * cascading the same treatment to any bills raised against it. This is
     * the exact inverse of InvoiceDeletionService::delete().
     */
    public function restore(Invoice $invoice, ?string $reason = null): void
    {
        DB::transaction(function () use ($invoice, $reason) {

            $invoice = Invoice::withTrashed()->with([
                'invoice_items' => fn ($q) => $q->withTrashed(),
                'invoice_payments' => fn ($q) => $q->withTrashed(),
                'bills' => fn ($q) => $q->withTrashed(),
                'payments' => fn ($q) => $q->withTrashed(),
            ])->lockForUpdate()->findOrFail($invoice->id);

            $reasonText = $reason ?? "Invoice {$invoice->invoice_number} restored";

            // -----------------------------
            // 1) Reinstate allocations recorded against this invoice (drawdown)
            // -----------------------------
            foreach ($invoice->invoice_payments as $invoice_payment) {

                if ($invoice_payment->source === 'drawdown' && $invoice_payment->payment_id) {
                    $funding_payment = Payment::where('id', $invoice_payment->payment_id)->lockForUpdate()->first();
                    if ($funding_payment) {
                        $funding_payment->drawdown_balance = (float) ($funding_payment->drawdown_balance ?? 0) - (float) $invoice_payment->amount;
                        $funding_payment->save();
                    }
                }

                $invoice_payment->restore();
            }

            // -----------------------------
            // 2) Reinstate payments recorded directly against this invoice
            // -----------------------------
            foreach ($invoice->payments as $payment) {
                $this->paymentRestoration->restore($payment, adjustParentBalance: false, reason: $reasonText);
            }

            // -----------------------------
            // 3) Reinstate the invoice's own journal entry
            // -----------------------------
            JournalEntry::where('invoice_id', $invoice->id)
                ->where('status', 'reversed')
                ->get()
                ->each(fn ($entry) => $this->journalReversal->unreverse($entry, $reasonText));

            // -----------------------------
            // 4) Cascade the same restoration to bills raised against this invoice
            // -----------------------------
            foreach ($invoice->bills as $bill) {
                $this->billRestoration->restore($bill, $reasonText, skipInvoiceGuard: true);
            }

            // -----------------------------
            // 5) Restore line items and the invoice itself
            // -----------------------------
            foreach ($invoice->invoice_items as $invoice_item) {
                $invoice_item->restore();
            }

            $invoice->deleted_by_id = null;
            $invoice->save();
            $invoice->restore();
        });
    }
}
