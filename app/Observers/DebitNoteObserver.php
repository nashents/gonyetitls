<?php

namespace App\Observers;

use App\Models\DebitNote;
use App\Services\Accounting\DebitNoteJournalService;

class DebitNoteObserver
{
    /**
     * Handle the DebitNote "created" event.
     *
     * @param  \App\Models\DebitNote  $debitNote
     * @return void
     */
    public function created(DebitNote $debitNote)
    {
        if ($debitNote->isDirty('authorization') && $debitNote->authorization === 'approved') {
            app(DebitNoteJournalService::class)->post($debitNote);
        }
    }

    /**
     * Handle the DebitNote "updated" event.
     *
     * @param  \App\Models\DebitNote  $debitNote
     * @return void
     */
    public function updated(DebitNote $debitNote)
    {
        if ($debitNote->isDirty('authorization') && $debitNote->authorization === 'approved') {
            app(DebitNoteJournalService::class)->post($debitNote);
        }
    }

    /**
     * Handle the DebitNote "deleted" event.
     *
     * @param  \App\Models\DebitNote  $debitNote
     * @return void
     */
    public function deleted(DebitNote $debitNote)
    {
        //
    }

    /**
     * Handle the DebitNote "restored" event.
     *
     * @param  \App\Models\DebitNote  $debitNote
     * @return void
     */
    public function restored(DebitNote $debitNote)
    {
        //
    }

    /**
     * Handle the DebitNote "force deleted" event.
     *
     * @param  \App\Models\DebitNote  $debitNote
     * @return void
     */
    public function forceDeleted(DebitNote $debitNote)
    {
        //
    }
}
