<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Models\CreditNote;
use App\Models\JournalEntry;
use App\Services\Accounting\LedgerResyncService;
use App\Services\Accounting\JournalReversalService;
use App\Services\Accounting\CreditNoteJournalService;

/**
 * The one place a credit note's effect on its invoice and the ledger is kept
 * in step: approving, un-approving, editing an approved note's total/invoice,
 * deleting and restoring all recompute the invoice balance from scratch
 * (Invoice::recalculateBalance) and post/reverse/resync the journal entry.
 * Screens must not touch invoice balances for credit notes themselves.
 */
class CreditNoteObserver
{
    /**
     * Handle the CreditNote "created" event.
     *
     * @param  \App\Models\CreditNote  $creditNote
     * @return void
     */
    public function created(CreditNote $creditNote)
    {
        if ($creditNote->authorization === 'approved') {
            app(CreditNoteJournalService::class)->post($creditNote);
            $this->recalculateInvoices([$creditNote->invoice_id]);
        }
    }

    /**
     * Handle the CreditNote "updated" event.
     *
     * @param  \App\Models\CreditNote  $creditNote
     * @return void
     */
    public function updated(CreditNote $creditNote)
    {
        if (! $creditNote->wasChanged(['authorization', 'total', 'invoice_id'])) {
            return;
        }

        $wasApproved = $creditNote->getOriginal('authorization') === 'approved';
        $isApproved  = $creditNote->authorization === 'approved';

        if (! $wasApproved && ! $isApproved) {
            return;
        }

        if (! $wasApproved && $isApproved) {
            app(CreditNoteJournalService::class)->post($creditNote);
        } elseif ($wasApproved && ! $isApproved) {
            $this->reverseLedger($creditNote, "Credit Note {$creditNote->credit_note_number} un-approved");
        } else {
            app(LedgerResyncService::class)->resyncCreditNote(
                $creditNote,
                "Credit Note {$creditNote->credit_note_number} edited after approval"
            );
        }

        $this->recalculateInvoices([$creditNote->getOriginal('invoice_id'), $creditNote->invoice_id]);
    }

    /**
     * Handle the CreditNote "deleted" event.
     *
     * @param  \App\Models\CreditNote  $creditNote
     * @return void
     */
    public function deleted(CreditNote $creditNote)
    {
        if ($creditNote->authorization === 'approved') {
            $this->reverseLedger($creditNote, "Credit Note {$creditNote->credit_note_number} deleted");
            $this->recalculateInvoices([$creditNote->invoice_id]);
        }
    }

    /**
     * Handle the CreditNote "restored" event.
     *
     * @param  \App\Models\CreditNote  $creditNote
     * @return void
     */
    public function restored(CreditNote $creditNote)
    {
        if ($creditNote->authorization === 'approved') {
            app(CreditNoteJournalService::class)->post($creditNote);
            $this->recalculateInvoices([$creditNote->invoice_id]);
        }
    }

    /**
     * Handle the CreditNote "force deleted" event.
     *
     * @param  \App\Models\CreditNote  $creditNote
     * @return void
     */
    public function forceDeleted(CreditNote $creditNote)
    {
        //
    }

    private function reverseLedger(CreditNote $creditNote, string $reason): void
    {
        $reversal = app(JournalReversalService::class);

        JournalEntry::where('credit_note_id', $creditNote->id)
            ->where('status', '!=', 'reversed')
            ->get()
            ->filter(fn ($entry) => $reversal->isLiveEffect($entry))
            ->each(fn ($entry) => $reversal->reverse($entry, $reason));
    }

    private function recalculateInvoices(array $invoiceIds): void
    {
        foreach (array_unique(array_filter($invoiceIds)) as $invoiceId) {
            Invoice::find($invoiceId)?->recalculateBalance();
        }
    }
}
