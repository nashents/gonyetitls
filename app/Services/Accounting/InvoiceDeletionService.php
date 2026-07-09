<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class InvoiceDeletionService
{
    public function __construct(
        protected JournalReversalService $journalReversal,
        protected BillDeletionService $billDeletion
    ) {
    }

    /**
     * Delete an invoice, reversing every account/wallet balance and journal entry
     * its payments posted, cascading the same treatment to any bills raised
     * against it, and recording who deleted it.
     */
    public function delete(Invoice $invoice, ?int $deletedById = null, ?string $reason = null): void
    {
        DB::transaction(function () use ($invoice, $deletedById, $reason) {

            $invoice = Invoice::with([
                'invoice_items',
                'invoice_payments',
                'bills',
                'payments.denominations',
                'payments.documents',
                'payments.receipt',
                'payments.cash_flow',
            ])->lockForUpdate()->findOrFail($invoice->id);

            $reasonText = $reason ?? "Invoice {$invoice->invoice_number} deleted";

            // -----------------------------
            // 1) Reverse allocations recorded against this invoice (direct + drawdown)
            // -----------------------------
            foreach ($invoice->invoice_payments as $invoice_payment) {

                if ($invoice_payment->source === 'drawdown' && $invoice_payment->payment_id) {
                    // Give the money back to the wallet it was drawn down from
                    $funding_payment = Payment::where('id', $invoice_payment->payment_id)->lockForUpdate()->first();
                    if ($funding_payment) {
                        $funding_payment->drawdown_balance = (float) ($funding_payment->drawdown_balance ?? 0) + (float) $invoice_payment->amount;
                        $funding_payment->save();
                    }
                }

                $invoice_payment->delete();
            }

            // -----------------------------
            // 2) Reverse and remove payments recorded directly against this invoice
            // -----------------------------
            foreach ($invoice->payments as $payment) {

                if ($payment->account_id) {
                    $account = Account::where('id', $payment->account_id)->lockForUpdate()->first();
                    if ($account) {
                        // Invoice payments credited the account (cash in); deleting must debit it back out
                        $account->balance = (float) $account->balance - (float) $payment->amount;
                        $account->save();
                    }
                }

                // Post a reversing journal entry for whatever this payment originally posted
                JournalEntry::where('payment_id', $payment->id)
                    ->where('status', '!=', 'reversed')
                    ->get()
                    ->each(fn ($entry) => $this->journalReversal->reverse($entry, $reasonText));

                $payment->denominations?->each->delete();
                $payment->documents?->each->delete();

                if ($payment->receipt) {
                    $payment->receipt->delete();
                }

                if ($payment->cash_flow) {
                    $payment->cash_flow->delete();
                }

                $payment->deleted_by_id = $deletedById;
                $payment->save();
                $payment->delete();
            }

            // -----------------------------
            // 3) Reverse the invoice's own journal entry
            // -----------------------------
            JournalEntry::where('invoice_id', $invoice->id)
                ->where('status', '!=', 'reversed')
                ->get()
                ->each(fn ($entry) => $this->journalReversal->reverse($entry, $reasonText));

            // -----------------------------
            // 4) Cascade the same reversal treatment to bills raised against this invoice
            // -----------------------------
            foreach ($invoice->bills as $bill) {
                $this->billDeletion->delete($bill, $deletedById, $reasonText);
            }

            // -----------------------------
            // 5) Remove line items and the invoice itself
            // -----------------------------
            foreach ($invoice->invoice_items as $invoice_item) {
                $invoice_item->delete();
            }

            $invoice->deleted_by_id = $deletedById;
            $invoice->save();
            $invoice->delete();
        });
    }
}
