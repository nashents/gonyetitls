<?php

namespace App\Observers;

use App\Models\CreditNote;
use App\Services\Accounting\CreditNoteJournalService;

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
        if ($creditNote->isDirty('authorization') && $creditNote->authorization === 'approved') {
            app(CreditNoteJournalService::class)->post($creditNote);
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
        if ($creditNote->isDirty('authorization') && $creditNote->authorization === 'approved') {
            app(CreditNoteJournalService::class)->post($creditNote);
        }
    }

    /**
     * Handle the CreditNote "deleted" event.
     *
     * @param  \App\Models\CreditNote  $creditNote
     * @return void
     */
    public function deleted(CreditNote $creditNote)
    {
        //
    }

    /**
     * Handle the CreditNote "restored" event.
     *
     * @param  \App\Models\CreditNote  $creditNote
     * @return void
     */
    public function restored(CreditNote $creditNote)
    {
        //
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
}
