<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Trip;
use App\Models\TripTransportOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdvanceInvoiceReclassService
{
    /**
     * Called after a trip's trip_status is persisted as "Offloaded". Finds any
     * approved Advance invoices linked to this trip and, once every trip on
     * that invoice is Offloaded, reclassifies the deferred Customer Advances
     * balance to Sales (and VAT Payable) — the revenue is now earned.
     */
    public function handleTripOffloaded(Trip $trip): void
    {
        $invoices = Invoice::where('invoice_type', 'advance')
            ->where('authorization', 'approved')
            ->whereHas('invoice_items', function ($q) use ($trip) {
                $q->where('trip_id', $trip->id)
                    ->orWhereHas('trip_transport_order', function ($q2) use ($trip) {
                        $q2->where('trip_id', $trip->id);
                    });
            })
            ->get();

        foreach ($invoices as $invoice) {
            $this->reclassIfEarned($invoice->id);
        }
    }

    protected function reclassIfEarned(int $invoiceId): void
    {
        DB::transaction(function () use ($invoiceId) {
            // Row-lock the invoice for the duration of this check-then-post
            // sequence so two near-simultaneous Offload transitions for two
            // trips on the same multi-trip advance invoice can't both pass
            // the "already reclassed" check before either commits.
            $invoice = Invoice::whereKey($invoiceId)->lockForUpdate()->first();

            if (!$invoice || $this->alreadyReclassed($invoice)) {
                return;
            }

            if (!$this->allLinkedTripsOffloaded($invoice)) {
                return;
            }

            $this->postReclass($invoice);
        });
    }

    protected function alreadyReclassed(Invoice $invoice): bool
    {
        return JournalEntry::where('invoice_id', $invoice->id)
            ->where('reference', 'like', 'RECLASS-%')
            ->exists();
    }

    protected function allLinkedTripsOffloaded(Invoice $invoice): bool
    {
        $items = $invoice->invoice_items()->get(['trip_id', 'trip_transport_order_id']);

        $tripIds = $items->pluck('trip_id')->filter()->values();

        $ttoIds = $items->pluck('trip_transport_order_id')->filter()->values();
        if ($ttoIds->isNotEmpty()) {
            $tripIds = $tripIds->merge(
                TripTransportOrder::whereIn('id', $ttoIds)->pluck('trip_id')->filter()
            );
        }

        $tripIds = $tripIds->unique();

        if ($tripIds->isEmpty()) {
            return false;
        }

        return Trip::whereIn('id', $tripIds)
            ->where('trip_status', '!=', 'Offloaded')
            ->doesntExist();
    }

    protected function postReclass(Invoice $invoice): JournalEntry
    {
        $customerAdvancesAccount = Account::where('name', 'Customer Advances')->firstOrFail();
        $salesAccount            = Account::where('name', 'Sales')->firstOrFail();
        $vatAccount              = Account::where('name', 'Value Added Tax')->firstOrFail();

        $rate = $invoice->exchange_rate ?? 1;

        $entry = JournalEntry::create([
            'company_id'     => $invoice->company_id ? $invoice->company_id : Auth::user()?->employee?->company_id,
            'invoice_id'     => $invoice->id,
            'journal_number' => app(InvoiceJournalService::class)->generateNumber(),
            'date'           => now(),
            'reference'      => 'RECLASS-' . $invoice->invoice_number,
            'description'    => "Revenue recognition (advance reclass) - Invoice {$invoice->invoice_number}",
            'is_manual'      => false,
            'status'         => 'posted',
            'created_by_id'  => Auth::id(),
            'posted_by_id'   => Auth::id(),
            'posted_at'      => now(),
        ]);

        // ── DR Customer Advances (full total, incl. deferred VAT) ────────
        $entry->journal_entry_lines()->create([
            'account_id'      => $customerAdvancesAccount->id,
            'customer_id'     => $invoice->customer_id,
            'debit'           => $invoice->total,
            'credit'          => 0,
            'exchange_debit'  => $invoice->total * $rate,
            'exchange_credit' => 0,
            'currency_id'     => $invoice->currency_id,
            'exchange_rate'   => $rate,
            'description'     => "Advance earned - Invoice {$invoice->invoice_number}",
        ]);

        // ── CR Sales (subtotal) ───────────────────────────────────────────
        $entry->journal_entry_lines()->create([
            'account_id'      => $salesAccount->id,
            'customer_id'     => $invoice->customer_id,
            'debit'           => 0,
            'credit'          => $invoice->subtotal,
            'exchange_debit'  => 0,
            'exchange_credit' => $invoice->subtotal * $rate,
            'currency_id'     => $invoice->currency_id,
            'exchange_rate'   => $rate,
            'description'     => "Sales - Invoice {$invoice->invoice_number}",
        ]);

        // ── CR VAT Payable (tax_amount, if any) ───────────────────────────
        if ($invoice->tax_amount > 0) {
            $entry->journal_entry_lines()->create([
                'account_id'      => $vatAccount->id,
                'customer_id'     => $invoice->customer_id,
                'debit'           => 0,
                'credit'          => $invoice->tax_amount,
                'exchange_debit'  => 0,
                'exchange_credit' => $invoice->tax_amount * $rate,
                'currency_id'     => $invoice->currency_id,
                'exchange_rate'   => $rate,
                'description'     => "VAT - Invoice {$invoice->invoice_number}",
            ]);
        }

        return $entry;
    }
}
