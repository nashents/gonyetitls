<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Services\Accounting\AdvanceInvoiceReclassService;
use App\Services\Accounting\CustomerFuelSupplyService;
use App\Services\Accounting\JournalReversalService;
use App\Services\Accounting\InvoiceJournalService;
use App\Services\Accounting\InvoiceTripFreightSyncService;
use App\Services\Sage\SageSyncService;
use Illuminate\Support\Facades\DB;
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
            $this->releaseAdvanceIfEarned($invoice);
            app(InvoiceTripFreightSyncService::class)->syncApprovedFreightUpdates($invoice);
            $this->pushToSage($invoice);
            $this->applyCustomerSuppliedFuel($invoice);
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
            $this->releaseAdvanceIfEarned($invoice);
            app(InvoiceTripFreightSyncService::class)->syncApprovedFreightUpdates($invoice);
            $this->pushToSage($invoice);
            $this->applyCustomerSuppliedFuel($invoice);
        } elseif ($invoice->authorization === 'approved'
            && $invoice->isDirty(['total', 'subtotal', 'tax_amount', 'discount_amount', 'exchange_rate', 'currency_id', 'date', 'customer_id'])) {
            // Edited after approval: the ledger must follow. Only posting at
            // the approval transition left edited invoices posted at their old
            // figures (COMDELTIC CEI00001-4: AR / Customer Advances off by
            // 15-20k each). resyncInvoice() is a no-op if the posting already
            // matches, and redoes an advance invoice's release.
            app(\App\Services\Accounting\LedgerResyncService::class)->resyncInvoice(
                $invoice,
                "Invoice {$invoice->invoice_number} edited",
                force: $invoice->isDirty('customer_id') // amounts unchanged, AR customer moved
            );
        }
    }

    /**
     * An advance invoice approved after its trips were already Offloaded is
     * earned straight away - release it to Sales now, since no further trip
     * status change will come along to trigger it. Never blocks approval.
     */
    protected function releaseAdvanceIfEarned(Invoice $invoice): void
    {
        if ($invoice->invoice_type !== 'advance') {
            return;
        }
        try {
            app(AdvanceInvoiceReclassService::class)->reclassIfEarned($invoice->id);
        } catch (\Throwable $e) {
            Log::warning("Advance invoice {$invoice->invoice_number} release to Sales failed: " . $e->getMessage());
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
     * Apply any customer-supplied fuel recorded for this invoice's trips
     * against its balance. Deferred until the surrounding transaction
     * commits - invoices are often saved approved before their trip line
     * items (and final balance) are written - and guarded so a failure here
     * never blocks the approval itself.
     */
    protected function applyCustomerSuppliedFuel(Invoice $invoice): void
    {
        DB::afterCommit(function () use ($invoice) {
            try {
                $fresh = Invoice::find($invoice->id);
                if ($fresh && $fresh->authorization === 'approved') {
                    app(CustomerFuelSupplyService::class)->autoAllocateForInvoice($fresh);
                }
            } catch (\Throwable $e) {
                Log::error('Customer supplied fuel allocation failed for invoice #' . $invoice->id . ': ' . $e->getMessage());
            }
        });
    }

    /**
     * Handle the Invoice "deleted" event.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return void
     */
    public function deleted(Invoice $invoice)
    {
        // However it was deleted (e.g. its sale being deleted), nothing under
        // the invoice may be left hanging - see InvoiceDeletionService::cascade().
        // Idempotent, so a no-op after InvoiceDeletionService already did it.
        app(\App\Services\Accounting\InvoiceDeletionService::class)->cascade(
            $invoice,
            "Invoice {$invoice->invoice_number} deleted",
            $invoice->deleted_by_id
        );
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
