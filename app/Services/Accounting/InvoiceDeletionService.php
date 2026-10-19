<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\Invoice;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\DB;

class InvoiceDeletionService
{
    public function __construct(
        protected JournalReversalService $journalReversal,
        protected BillDeletionService $billDeletion,
        protected CustomerDepositService $customerDeposits
    ) {
    }

    /**
     * Delete an invoice, reversing every account/wallet balance and journal entry
     * its payments posted, cascading the same treatment to any bills raised
     * against it and credit notes issued on it, and recording who deleted it.
     *
     * Refused for an invoice raised from a sale (delete the sale - that
     * removes the invoice) and for a fiscalised invoice (already reported to
     * the tax authority - issue a credit note instead). Source flows pass
     * $fromSource = true.
     */
    public function delete(Invoice $invoice, ?int $deletedById = null, ?string $reason = null, bool $fromSource = false): void
    {
        if ($blocked = $this->blockReason($invoice, $fromSource)) {
            throw new \RuntimeException($blocked);
        }

        DB::transaction(function () use ($invoice, $deletedById, $reason) {
            $invoice = Invoice::lockForUpdate()->findOrFail($invoice->id);
            $reasonText = $reason ?? "Invoice {$invoice->invoice_number} deleted";

            $this->cascade($invoice, $reasonText, $deletedById);

            $invoice->deleted_by_id = $deletedById;
            $invoice->save();
            $invoice->delete();
        });
    }

    /** Why this invoice can't be deleted, or null. */
    public function blockReason(Invoice $invoice, bool $fromSource = false): ?string
    {
        if ($invoice->isFiscalised()) {
            return "Invoice {$invoice->invoice_number} has been fiscalised with the tax authority and can't be deleted - issue a credit note against it instead.";
        }

        if (! $fromSource && $invoice->sale_id && ($sale = $invoice->sale) && ! $sale->trashed()) {
            return "Invoice {$invoice->invoice_number} was raised from a sale. Delete the sale instead - that removes this invoice and reverses everything under it.";
        }

        return null;
    }

    /**
     * Everything under an invoice: allocations (deposit drawdowns go back to
     * the wallet, trip line splits come off the trips), payments made
     * against it, credit notes issued on it, bills raised against it, its own
     * posting(s) incl. an advance release, and its line items. Idempotent,
     * and also run by InvoiceObserver::deleted() so an invoice removed by any
     * path leaves nothing behind.
     */
    public function cascade(Invoice $invoice, string $reasonText, ?int $deletedById = null): void
    {
        DB::transaction(function () use ($invoice, $reasonText, $deletedById) {

            // 1) Allocations recorded against this invoice (direct + drawdown)
            $allocations = $invoice->invoice_payments()->get();
            $hadDrawdowns = $allocations->contains(fn ($invoice_payment) => $invoice_payment->source === 'drawdown' && $invoice_payment->payment_id);

            foreach ($allocations as $invoice_payment) {
                // per-line split comes back off the trips it was paid against
                app(InvoicePaymentLineService::class)->reverse($invoice_payment);
                // InvoicePaymentObserver reverses a drawdown's own posting
                $invoice_payment->delete();
            }

            // Give the money back to the wallet it was drawn down from - the
            // allocations are gone, so each funding deposit is available
            // again; the running wallet total just needs recomputing.
            if ($hadDrawdowns && $invoice->customer_id && $invoice->currency_id) {
                $this->customerDeposits->syncWalletBalance((int) $invoice->customer_id, (int) $invoice->currency_id);
            }

            // 2) Payments recorded directly against this invoice - PaymentDeletionService
            //    takes the cash back off the account and reverses its posting.
            foreach ($invoice->payments()->get() as $payment) {
                app(PaymentDeletionService::class)->delete($payment, $deletedById, $reasonText);
            }

            // 3) Credit notes issued on this invoice - left behind they kept
            //    crediting AR / Sales for an invoice that no longer exists.
            //    CreditNoteObserver::deleted reverses each one's posting.
            foreach ($invoice->credit_notes()->get() as $creditNote) {
                $creditNote->delete();
            }

            // 4) The invoice's own posting(s), incl. an advance invoice's release
            $this->journalReversal->reverseLiveEffects(JournalEntry::where('invoice_id', $invoice->id), $reasonText);

            // 5) Bills raised against this invoice - removed because their source is
            foreach ($invoice->bills()->get() as $bill) {
                $this->billDeletion->delete($bill, $deletedById, $reasonText, fromSource: true);
            }

            // 6) Line items
            foreach ($invoice->invoice_items()->get() as $invoice_item) {
                $invoice_item->delete();
            }
        });
    }
}
