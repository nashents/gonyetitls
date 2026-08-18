<?php

namespace App\Http\Livewire\DebitNotes;

use App\Models\Bill;
use App\Models\DebitNote;
use Livewire\Component;

class Index extends Component
{
    public $debit_notes;

    public function mount(){
        $this->debit_notes = DebitNote::with('journal_entry')->latest()->get();
    }

    public function getUnpostedDebitNotesCountProperty()
    {
        return app(\App\Services\Accounting\LedgerBackfillService::class)->missingDebitNotesQuery()->count();
    }

    /**
     * Posts every approved debit note with no JournalEntry yet - the same
     * query/logic as `php artisan ledger:backfill`, just triggered from
     * the list page instead of the CLI.
     */
    public function bulkPostToLedger()
    {
        $result = app(\App\Services\Accounting\LedgerBackfillService::class)->runDebitNotes();
        $posted = count($result['posted']);
        $errors = count($result['errors']);

        $this->dispatchBrowserEvent('alert', [
            'type'    => $errors > 0 ? 'warning' : 'success',
            'message' => "Posted {$posted} of {$result['total']} debit note(s) to the ledger."
                . ($errors > 0 ? " {$errors} failed - see logs." : ''),
        ]);

        $this->debit_notes = DebitNote::with('journal_entry')->latest()->get();
    }

    /**
     * Manually push an approved debit note to the general ledger. Covers
     * debit notes approved without ever triggering DebitNoteObserver (e.g.
     * created already-approved in one save, which never fires an
     * isDirty('authorization') transition) - the same gap fixed for
     * bills/invoices via the ledger backfill.
     */
    public function postToLedger($id)
    {
        $debitNote = DebitNote::find($id);
        if (! $debitNote) {
            return;
        }

        if (strcasecmp((string) $debitNote->authorization, 'approved') !== 0) {
            $this->dispatchBrowserEvent('alert', [
                'type'    => 'warning',
                'message' => 'Only authorized (approved) debit notes can be posted to the ledger.',
            ]);
            return;
        }

        try {
            app(\App\Services\Accounting\DebitNoteJournalService::class)->post($debitNote);
            $this->dispatchBrowserEvent('alert', [
                'type'    => 'success',
                'message' => 'Debit note posted to the general ledger.',
            ]);
            $this->debit_notes = DebitNote::with('journal_entry')->latest()->get();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Manual debit note ledger post failed: ' . $e->getMessage());
            $this->dispatchBrowserEvent('alert', [
                'type'    => 'error',
                'message' => 'Could not post this debit note to the ledger: ' . $e->getMessage(),
            ]);
        }
    }

    public function render()
    {
        return view('livewire.debit-notes.index');
    }
}
