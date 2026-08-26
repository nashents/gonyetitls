<?php

namespace App\Services\Accounting;

use App\Models\Invoice;
use Illuminate\Support\Facades\Log;

class InvoiceTripFreightSyncService
{
    /**
     * Applies any "Update Trip Freight" line items on this invoice to their
     * trips' freight figure. Deferred until the invoice is authorized
     * (approved) - called from InvoiceObserver alongside journal posting -
     * so an invoice that's still pending (and might be rejected) never
     * touches a trip's figures.
     */
    public function syncApprovedFreightUpdates(Invoice $invoice): void
    {
        $items = $invoice->invoice_items()
            ->where('is_update_trip_freight', true)
            ->whereNotNull('trip_id')
            ->get();

        foreach ($items as $item) {
            $trip = $item->trip;

            if (!$trip || !is_numeric($item->amount)) {
                continue;
            }

            $lockedByOtherInvoice = $trip->invoice_items()
                ->where('invoice_id', '!=', $invoice->id)
                ->whereHas('invoice', fn ($q) => $q->where('authorization', 'approved'))
                ->exists();

            if ($lockedByOtherInvoice) {
                Log::warning("Skipped trip freight sync for trip #{$trip->id} ({$trip->trip_number}) from invoice {$invoice->invoice_number}: trip is already financially locked by another approved invoice.");
                continue;
            }

            $trip->freight = $item->amount;
            $trip->save();
        }
    }
}
