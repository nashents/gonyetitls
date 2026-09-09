<?php

namespace App\Services\Accounting;

use App\Models\Bill;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class BillRestorationService
{
    public function __construct(
        protected JournalReversalService $journalReversal,
        protected PaymentRestorationService $paymentRestoration
    ) {
    }

    /**
     * Restore a soft-deleted bill, reapplying every account balance change
     * and journal entry that BillDeletionService reversed, and restoring
     * every child row it soft-deleted. This is the exact inverse of
     * BillDeletionService::delete().
     *
     * $skipInvoiceGuard is set by InvoiceRestorationService when it cascades
     * here for a bill raised against the invoice it's already restoring in
     * the same transaction - otherwise a bill whose parent invoice is still
     * trashed refuses to restore on its own (restore the invoice first, so
     * the "Invoice VAT" link stays consistent).
     */
    public function restore(Bill $bill, ?string $reason = null, bool $skipInvoiceGuard = false): void
    {
        DB::transaction(function () use ($bill, $reason, $skipInvoiceGuard) {

            $bill = Bill::withTrashed()->with([
                'bill_expenses' => fn ($q) => $q->withTrashed(),
                'bill_payments' => fn ($q) => $q->withTrashed(),
                'payments' => fn ($q) => $q->withTrashed(),
            ])->lockForUpdate()->findOrFail($bill->id);

            if (! $skipInvoiceGuard && $bill->invoice_id) {
                $invoice = Invoice::withTrashed()->find($bill->invoice_id);
                if ($invoice && $invoice->trashed()) {
                    throw new \RuntimeException("Restore Invoice {$invoice->invoice_number} first - this bill was deleted together with it.");
                }
            }

            $reasonText = $reason ?? "Bill {$bill->bill_number} restored";

            // -----------------------------
            // 1) Reinstate allocations recorded against this bill (drawdown)
            // -----------------------------
            foreach ($bill->bill_payments as $bill_payment) {

                if ($bill_payment->source === 'drawdown' && $bill_payment->payment_id) {
                    $funding_payment = Payment::where('id', $bill_payment->payment_id)->lockForUpdate()->first();
                    if ($funding_payment) {
                        $funding_payment->drawdown_balance = (float) ($funding_payment->drawdown_balance ?? 0) - (float) $bill_payment->amount;
                        $funding_payment->save();
                    }
                }

                $bill_payment->restore();
            }

            // -----------------------------
            // 2) Reinstate payments recorded directly against this bill
            // -----------------------------
            foreach ($bill->payments as $payment) {
                $this->paymentRestoration->restore($payment, adjustParentBalance: false, reason: $reasonText);
            }

            // -----------------------------
            // 3) Reinstate the bill's own journal entry
            // -----------------------------
            JournalEntry::where('bill_id', $bill->id)
                ->where('status', 'reversed')
                ->get()
                ->each(fn ($entry) => $this->journalReversal->unreverse($entry, $reasonText));

            // -----------------------------
            // 4) Restore line items and the bill itself
            // -----------------------------
            foreach ($bill->bill_expenses as $bill_expense) {
                $bill_expense->restore();
            }

            $bill->deleted_by_id = null;
            $bill->save();
            $bill->restore();
        });
    }
}
