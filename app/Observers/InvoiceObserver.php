<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Services\Accounting\InvoiceJournalService;
use App\Services\Sage\SageSyncService;
use Illuminate\Support\Facades\Log;

class InvoiceObserver
{
    /**
     * Handle the Invoice "created" event.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return void
     */
    public function created(Invoice $invoice)
    {
        if ($invoice->isDirty('authorization') && $invoice->authorization === 'approved') {
            app(InvoiceJournalService::class)->post($invoice);
            $this->pushToSage($invoice);
        }
    }

    /**
     * Handle the Invoice "updated" event.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return void
     */
    public function updated(Invoice $invoice)
    {
        if ($invoice->isDirty('authorization') && $invoice->authorization === 'approved') {
            app(InvoiceJournalService::class)->post($invoice);
            $this->pushToSage($invoice);
        }
    }

    /**
     * Push an approved invoice to Sage as an OE sales invoice / Job Card Invoice.
     * Guarded: a missing/inactive integration no-ops and a Sage error never blocks
     * the approval or the journal posting above.
     */
    protected function pushToSage(Invoice $invoice): void
    {
        try {
            app(SageSyncService::class)->syncInvoice($invoice);
        } catch (\Throwable $e) {
            Log::warning('Sage invoice sync failed: ' . $e->getMessage());
        }
    }

    /**
     * Handle the Invoice "deleted" event.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return void
     */
    public function deleted(Invoice $invoice)
    {
        //
    }

    /**
     * Handle the Invoice "restored" event.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return void
     */
    public function restored(Invoice $invoice)
    {
        //
    }

    /**
     * Handle the Invoice "force deleted" event.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return void
     */
    public function forceDeleted(Invoice $invoice)
    {
        //
    }
}
