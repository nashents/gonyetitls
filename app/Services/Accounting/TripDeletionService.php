<?php

namespace App\Services\Accounting;

use App\Models\Invoice;
use App\Models\Trip;
use App\Models\TransportOrder;
use App\Models\TripTransportOrder;
use App\Services\TripCompletionCascadeService;
use Illuminate\Support\Facades\DB;

class TripDeletionService
{
    public function __construct(
        protected InvoiceDeletionService $invoiceDeletion,
        protected BillDeletionService $billDeletion,
        protected TripCompletionCascadeService $completionCascade
    ) {
    }

    /**
     * Delete a trip, reversing every invoice/bill raised against it (and every
     * payment/journal entry those posted) via the existing Invoice/
     * BillDeletionService, detaching it from any Transport Order it fulfilled
     * (recomputing that order's/its Deal's completion flags), and removing
     * its own operational child records.
     *
     * Deliberately refuses (throws \RuntimeException) rather than guessing
     * on the cases this service does not know how to reverse safely:
     *  - an invoice that also has line items for OTHER trips - deleting it
     *    whole would wipe their revenue too; split the invoice first.
     *  - a Transport Order already invoiced as a single line - that invoice
     *    isn't per-trip, so this trip's share can't be isolated from it.
     *  - any Payment left once invoices/bills above are gone - reachable
     *    directly (trip_id), via a Recovery, or via a Requisition. Each
     *    payment category (customer/vendor/recovery/withdrawal/deposit/
     *    transfer/...) has its own account-balance sign convention (see
     *    PaymentJournalService) that this service does not attempt to
     *    reverse; delete/reallocate those through their own module first.
     *  - a Recovery already pulled into a payroll run (has salary_items) -
     *    deleting it would corrupt payroll history.
     *
     * Returns an array of non-fatal warning strings - e.g. an invoice or the
     * trip itself was already synced to Sage Intacct, which this service
     * does not reach out to, so it needs voiding there separately.
     */
    public function delete(Trip $trip, ?int $deletedById = null, ?string $reason = null): array
    {
        return DB::transaction(function () use ($trip, $deletedById, $reason) {

            $trip = Trip::with([
                'invoice_items',
                'bills',
                'trip_expenses',
                'payments',
                'recoveries.payments',
                'recoveries.salary_items',
                'requisitions.payments',
                'requisitions.bill',
                'trip_transport_orders',
                'sageMapping',
            ])->lockForUpdate()->findOrFail($trip->id);

            $reasonText = $reason ?? "Trip {$trip->trip_number} deleted";
            $warnings = [];

            if ($trip->sageMapping) {
                $warnings[] = "Trip {$trip->trip_number} was synced to Sage Intacct as a project (ref {$trip->sageMapping->external_reference}) - void/remove it there too.";
            }

            // -----------------------------
            // 1) Delete every invoice directly covering this trip - refuse if
            // it's part of an already-invoiced Transport Order, or if any of
            // its own invoices also covers other trips.
            // -----------------------------
            if ($trip->transport_orders()->whereHas('invoice_items')->exists()) {
                throw new \RuntimeException(
                    "Trip {$trip->trip_number} is part of a Transport Order that has already been invoiced as a single line. That invoice covers the whole order, not just this trip - handle it at the Transport Order level first."
                );
            }

            $invoiceIds = $trip->invoice_items->pluck('invoice_id')->unique();

            foreach ($invoiceIds as $invoiceId) {
                $invoice = Invoice::with('invoice_items', 'sageMapping')->lockForUpdate()->findOrFail($invoiceId);

                $otherTripIds = $invoice->invoice_items->pluck('trip_id')->filter()
                    ->unique()->reject(fn ($id) => (int) $id === (int) $trip->id);

                if ($otherTripIds->isNotEmpty()) {
                    throw new \RuntimeException(
                        "Invoice {$invoice->invoice_number} also has line items for other trip(s) (id: {$otherTripIds->implode(', ')}). Split its line items first so deleting this trip doesn't reverse their revenue too."
                    );
                }

                if ($invoice->sageMapping) {
                    $warnings[] = "Invoice {$invoice->invoice_number} was synced to Sage Intacct (ref {$invoice->sageMapping->external_reference}) - void it there too.";
                }

                $this->invoiceDeletion->delete($invoice, $deletedById, $reasonText);
            }

            // -----------------------------
            // 2) Delete every bill raised against this trip (transporter
            // freight, fuel-consumption, trip-expense bills all carry
            // Bill.trip_id - see FuelJournalService / TripExpenseJournalService),
            // plus any requisition-linked bill that slipped through without it.
            // -----------------------------
            foreach ($trip->bills as $bill) {
                $this->billDeletion->delete($bill, $deletedById, $reasonText);
            }

            foreach ($trip->requisitions as $requisition) {
                if ($requisition->bill && ! $requisition->bill->trashed()) {
                    $this->billDeletion->delete($requisition->bill, $deletedById, $reasonText);
                }
            }

            // -----------------------------
            // 3) Refuse if a Payment remains anywhere reachable from this
            // trip that the above didn't already clean up.
            // -----------------------------
            $trip->load(['payments', 'recoveries.payments', 'requisitions.payments']);

            $remainingPayments = collect()
                ->merge($trip->payments)
                ->merge($trip->recoveries->flatMap->payments)
                ->merge($trip->requisitions->flatMap->payments);

            if ($remainingPayments->isNotEmpty()) {
                $numbers = $remainingPayments->pluck('payment_number')->implode(', ');
                throw new \RuntimeException(
                    "Trip {$trip->trip_number} still has payment(s) not tied to the invoice/bills just removed ({$numbers}). Reverse/delete those through the Payments module first."
                );
            }

            // -----------------------------
            // 4) Refuse if a Recovery on this trip already fed a payroll run.
            // -----------------------------
            $payrolledRecoveries = $trip->recoveries->filter(fn ($recovery) => $recovery->salary_items->isNotEmpty());
            if ($payrolledRecoveries->isNotEmpty()) {
                throw new \RuntimeException(
                    "Trip {$trip->trip_number} has a driver recovery already included in a payroll run. Deleting it would corrupt payroll history - reverse that payroll line first."
                );
            }

            // -----------------------------
            // 5) Detach from any (non-invoiced) Transport Order and recompute
            // its/its Deal's completion flags now this trip no longer counts.
            // -----------------------------
            $transportOrderIds = $trip->trip_transport_orders->pluck('transport_order_id')->filter()->unique();
            TripTransportOrder::where('trip_id', $trip->id)->delete();

            foreach ($transportOrderIds as $transportOrderId) {
                $siblingTrip = Trip::whereHas('trip_transport_orders', fn ($q) => $q->where('transport_order_id', $transportOrderId))
                    ->where('id', '!=', $trip->id)
                    ->first();

                if ($siblingTrip) {
                    $this->completionCascade->syncForTrip($siblingTrip);
                } else {
                    TransportOrder::where('id', $transportOrderId)->update(['completed' => false]);
                }
            }

            // -----------------------------
            // 6) Remove the trip's remaining operational child records
            // -----------------------------
            foreach ($trip->trip_expenses as $trip_expense) {
                $trip_expense->delete();
            }

            $trip->requisitions()->delete();
            $trip->driver_allowances()->delete();
            $trip->fuels()->delete();
            $trip->mileages()->delete();
            $trip->emptyruns()->delete();
            $trip->breakdown_assignments()->delete();
            $trip->breakdowns()->delete();
            $trip->recoveries()->delete();
            $trip->trip_positions()->delete();
            $trip->trip_destinations()->delete();
            $trip->trip_origins()->delete();
            $trip->trip_locations()->delete();
            $trip->trip_documents()->delete();
            $trip->incidents()->delete();
            $trip->cash_flows()->delete();
            $trip->receipts()->delete();
            $trip->invoice_trips()->delete();
            $trip->trip_statuses()->delete();
            $trip->edit_authorization_requests()->delete();
            optional($trip->delivery_note)->delete();
            optional($trip->trip_return)->delete();
            optional($trip->gate_pass)->delete();
            optional($trip->cmr_detail)->delete();
            optional($trip->commission)->delete();

            $trip->trailers()->detach();
            $trip->truck_stops()->detach();
            $trip->borders()->detach();
            $trip->clearing_agents()->detach();

            // -----------------------------
            // 7) Delete the trip itself
            // -----------------------------
            $trip->delete();

            return $warnings;
        });
    }
}
