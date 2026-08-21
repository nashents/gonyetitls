<?php

namespace App\Services\Accounting;

use App\Models\Bill;
use App\Models\CreditNote;
use App\Models\DebitNote;
use App\Models\Invoice;
use App\Models\JournalEntry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Fixes a document whose figures (exchange_rate, total, currency, etc.)
 * were corrected AFTER it was already posted to the ledger. None of
 * Invoice/Bill/CreditNote/DebitNote-editing re-posts the ledger - the
 * *JournalService classes are "post once, never again" by design, and the
 * Edit screens never touch journal_entry_lines at all - so a document
 * edited post-approval otherwise leaves its original (now wrong) journal
 * entry sitting in the Trial Balance forever, no matter how many more times
 * it gets corrected.
 *
 * Reverses every non-reversed journal entry already posted for the
 * document, then posts a fresh one from its current field values. Safe to
 * call on a document that was never posted (or already correctly posted) -
 * reversing zero entries is a no-op, and the *JournalService's post() will
 * just create the (correct) entry as normal.
 */
class LedgerResyncService
{
    public function __construct(
        private JournalReversalService $journalReversal,
        private InvoiceJournalService $invoiceJournal,
        private BillJournalService $billJournal,
        private CreditNoteJournalService $creditNoteJournal,
        private DebitNoteJournalService $debitNoteJournal
    ) {
    }

    public function resyncInvoice(Invoice $invoice, ?string $reason = null): JournalEntry
    {
        return DB::transaction(function () use ($invoice, $reason) {
            $existing = $this->currentEntry(JournalEntry::where('invoice_id', $invoice->id));

            if ($existing && $this->isUnchanged($existing, 'Accounts Receivable', (float) $invoice->total, $invoice->exchange_rate, $invoice->currency_id)) {
                return $existing;
            }

            $this->reverseExisting(
                JournalEntry::where('invoice_id', $invoice->id),
                $reason ?? "Invoice {$invoice->invoice_number} resynced to current figures"
            );

            return $this->invoiceJournal->post($invoice->fresh());
        });
    }

    public function resyncBill(Bill $bill, ?string $reason = null): JournalEntry
    {
        return DB::transaction(function () use ($bill, $reason) {
            $existing = $this->currentEntry(JournalEntry::where('bill_id', $bill->id));

            if ($existing && $this->isUnchanged($existing, 'Accounts Payable', (float) $bill->total, $bill->exchange_rate, $bill->currency_id)) {
                return $existing;
            }

            $this->reverseExisting(
                JournalEntry::where('bill_id', $bill->id),
                $reason ?? "Bill {$bill->bill_number} resynced to current figures"
            );

            return $this->billJournal->post($bill->fresh());
        });
    }

    public function resyncCreditNote(CreditNote $creditNote, ?string $reason = null): JournalEntry
    {
        return DB::transaction(function () use ($creditNote, $reason) {
            $existing = $this->currentEntry(JournalEntry::where('credit_note_id', $creditNote->id));

            if ($existing && $this->isUnchanged($existing, 'Accounts Receivable', (float) $creditNote->total, $creditNote->exchange_rate, $creditNote->currency_id)) {
                return $existing;
            }

            $this->reverseExisting(
                JournalEntry::where('credit_note_id', $creditNote->id),
                $reason ?? "Credit Note {$creditNote->credit_note_number} resynced to current figures"
            );

            return $this->creditNoteJournal->post($creditNote->fresh());
        });
    }

    public function resyncDebitNote(DebitNote $debitNote, ?string $reason = null): JournalEntry
    {
        return DB::transaction(function () use ($debitNote, $reason) {
            $existing = $this->currentEntry(JournalEntry::where('debit_note_id', $debitNote->id));

            if ($existing && $this->isUnchanged($existing, 'Accounts Payable', (float) $debitNote->total, $debitNote->exchange_rate, $debitNote->currency_id)) {
                return $existing;
            }

            $this->reverseExisting(
                JournalEntry::where('debit_note_id', $debitNote->id),
                $reason ?? "Debit Note {$debitNote->debit_note_number} resynced to current figures"
            );

            return $this->debitNoteJournal->post($debitNote->fresh());
        });
    }

    private function reverseExisting(Builder $query, string $reason): void
    {
        $query->where('status', '!=', 'reversed')
            ->get()
            ->each(fn (JournalEntry $entry) => $this->journalReversal->reverse($entry, $reason));
    }

    /**
     * The document's genuine current posting, if any - excludes reversal
     * records. JournalReversalService copies the document's foreign key
     * (invoice_id etc.) onto the reversal it creates, and that reversal's
     * own status is 'posted' (only the entry it reversed flips to
     * 'reversed'), so a plain status filter alone would mistake it for the
     * real posting.
     */
    private function currentEntry(Builder $query): ?JournalEntry
    {
        return $query->where('status', '!=', 'reversed')
            ->where(fn ($q) => $q->whereNull('reference')->orWhere('reference', 'not like', 'REV-%'))
            ->first();
    }

    /**
     * Whether $entry already reflects the document's current total, exchange
     * rate and currency - checked against the entry's line on the document's
     * control account (Accounts Receivable for invoices/credit notes,
     * Accounts Payable for bills/debit notes), which always carries the
     * document's `total` verbatim regardless of its internal tax/discount
     * breakdown. If unchanged, resync can skip reversing and reposting.
     */
    private function isUnchanged(JournalEntry $entry, string $controlAccountName, float $currentTotal, $currentRate, $currentCurrencyId): bool
    {
        $line = $entry->journal_entry_lines()
            ->whereHas('account', fn ($q) => $q->where('name', $controlAccountName))
            ->first();

        if (!$line) {
            return false;
        }

        $postedAmount = (float) ($line->debit ?: $line->credit);
        $postedRate   = (float) ($line->exchange_rate ?? 1);

        return round($postedAmount, 2) === round($currentTotal, 2)
            && round($postedRate, 6) === round((float) ($currentRate ?? 1), 6)
            && (int) $line->currency_id === (int) $currentCurrencyId;
    }
}
