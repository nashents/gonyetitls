<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Trip;
use App\Models\TripTransportOrder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Advance invoices post DR AR / CR Customer Advances (revenue + VAT deferred).
 * Once every trip on the invoice is Offloaded the revenue is earned, so this
 * posts the release: DR Customer Advances / CR revenue (per line account,
 * falling back to Sales) / CR VAT / DR Sales Discounts - the same split a
 * normal invoice posts on approval.
 *
 * Triggered automatically from TripObserver whenever a trip is saved as
 * Offloaded, from InvoiceObserver when an advance invoice is approved after
 * its trips were already Offloaded, and manually via "Post to Sales" on the
 * invoices list (postToSales(), which explains why it can't post).
 */
class AdvanceInvoiceReclassService
{
    public const POSTED = 'posted';
    public const ALREADY_POSTED = 'already_posted';
    public const NOT_ADVANCE = 'not_advance';
    public const NOT_APPROVED = 'not_approved';
    public const NOT_DEFERRED = 'not_deferred';
    public const NO_TRIPS = 'no_trips';
    public const TRIPS_NOT_OFFLOADED = 'trips_not_offloaded';

    /** Called whenever a trip is saved with trip_status "Offloaded". */
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

    /**
     * Post the release if it is due. Returns one of the status constants;
     * for TRIPS_NOT_OFFLOADED, $pendingTrips is filled with the trip numbers
     * still not Offloaded.
     */
    public function reclassIfEarned(int $invoiceId, ?array &$pendingTrips = null): string
    {
        $pendingTrips = [];

        return DB::transaction(function () use ($invoiceId, &$pendingTrips) {
            // Row-lock the invoice for the check-then-post sequence so two
            // near-simultaneous Offload transitions for trips on the same
            // multi-trip invoice can't both post the release.
            $invoice = Invoice::whereKey($invoiceId)->lockForUpdate()->first();

            if (!$invoice || $invoice->invoice_type !== 'advance') {
                return self::NOT_ADVANCE;
            }
            if (strcasecmp((string) $invoice->authorization, 'approved') !== 0) {
                return self::NOT_APPROVED;
            }
            if ($this->alreadyReclassed($invoice)) {
                return self::ALREADY_POSTED;
            }

            $deferral = $this->deferralEntry($invoice);
            if (!$deferral) {
                return self::NOT_DEFERRED;
            }

            $trips = $this->linkedTrips($invoice);
            if ($trips->isEmpty()) {
                return self::NO_TRIPS;
            }
            $pending = $trips->filter(fn ($t) => $t->trip_status !== 'Offloaded');
            if ($pending->isNotEmpty()) {
                $pendingTrips = $pending->map(fn ($t) => "{$t->trip_number} ({$t->trip_status})")->values()->all();
                return self::TRIPS_NOT_OFFLOADED;
            }

            $this->postReclass($invoice, $deferral);

            return self::POSTED;
        });
    }

    /** "Post to Sales" button: returns [ok, message]. */
    public function postToSales(Invoice $invoice): array
    {
        $result = $this->reclassIfEarned($invoice->id, $pending);

        return match ($result) {
            self::POSTED => [true, "Invoice {$invoice->invoice_number} posted to Sales."],
            self::ALREADY_POSTED => [false, "Invoice {$invoice->invoice_number} has already been posted to Sales."],
            self::NOT_ADVANCE => [false, "Invoice {$invoice->invoice_number} is not an advance invoice - it posted to Sales on approval."],
            self::NOT_APPROVED => [false, "Invoice {$invoice->invoice_number} must be approved first."],
            self::NOT_DEFERRED => [false, "Invoice {$invoice->invoice_number} has no Customer Advances posting to release."],
            self::NO_TRIPS => [false, "Invoice {$invoice->invoice_number} has no trips attached, so it can't be checked for offloading."],
            self::TRIPS_NOT_OFFLOADED => [false, "Not posted - these trips are not Offloaded yet: " . implode(', ', $pending) . '.'],
        };
    }

    /** A live (not reversed / not cancelled) RECLASS- entry already exists. */
    protected function alreadyReclassed(Invoice $invoice): bool
    {
        $reversal = app(JournalReversalService::class);

        return JournalEntry::where('invoice_id', $invoice->id)
            ->where('reference', 'like', 'RECLASS-%')
            ->get()
            ->contains(fn ($entry) => $reversal->isLiveEffect($entry));
    }

    /** The invoice's live original posting (the one that credited Customer Advances). */
    protected function deferralEntry(Invoice $invoice): ?JournalEntry
    {
        $reversal = app(JournalReversalService::class);

        return JournalEntry::where('invoice_id', $invoice->id)
            ->where('reference', $invoice->invoice_number)
            ->orderBy('id')
            ->get()
            ->first(fn ($entry) => $reversal->isLiveEffect($entry));
    }

    protected function linkedTrips(Invoice $invoice): Collection
    {
        $items = $invoice->invoice_items()->get(['trip_id', 'trip_transport_order_id']);

        $tripIds = $items->pluck('trip_id')->filter()->values();

        $ttoIds = $items->pluck('trip_transport_order_id')->filter()->values();
        if ($ttoIds->isNotEmpty()) {
            $tripIds = $tripIds->merge(
                TripTransportOrder::whereIn('id', $ttoIds)->pluck('trip_id')->filter()
            );
        }

        return Trip::whereIn('id', $tripIds->unique())->get(['id', 'trip_number', 'trip_status']);
    }

    protected function postReclass(Invoice $invoice, JournalEntry $deferral): JournalEntry
    {
        // Debit back the account the invoice actually credited, so a renamed
        // "Customer Advances" account (e.g. "Customer Advances (Deferred)")
        // still releases cleanly.
        $advanceAccountId = $deferral->journal_entry_lines()
            ->where('credit', '>', 0)
            ->orderByDesc('credit')
            ->value('account_id')
            ?? Account::where('name', 'like', 'Customer Advances%')->value('id');

        $salesAccount    = Account::where('name', 'Sales')->firstOrFail();
        $vatAccount      = Account::where('name', 'Value Added Tax')->firstOrFail();
        $discountAccount = Account::where('name', 'Sales Discounts')->first();

        $rate = $invoice->exchange_rate ?? 1;
        $line = fn (int $accountId, float $debit, float $credit, string $description) => [
            'account_id'      => $accountId,
            'customer_id'     => $invoice->customer_id,
            'debit'           => $debit,
            'credit'          => $credit,
            'exchange_debit'  => $debit * $rate,
            'exchange_credit' => $credit * $rate,
            'currency_id'     => $invoice->currency_id,
            'exchange_rate'   => $rate,
            'description'     => $description,
        ];

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

        // DR Customer Advances (full total, incl. deferred VAT)
        $entry->journal_entry_lines()->create($line($advanceAccountId, (float) $invoice->total, 0, "Advance earned - Invoice {$invoice->invoice_number}"));

        // CR revenue per line account (falls back to Sales) - as InvoiceJournalService
        $revenueGroups = $invoice->invoice_items()
            ->selectRaw('account_id, SUM(subtotal) as total_subtotal, SUM(COALESCE(recharge_amount, 0)) as total_recharge')
            ->groupBy('account_id')
            ->get();
        // Lines that don't add up to the header subtotal would leave the entry
        // unbalanced against the deferred total - release to Sales in one line.
        if ($revenueGroups->isEmpty() || abs($revenueGroups->sum('total_subtotal') - (float) $invoice->subtotal) > 0.01) {
            $recharge = round(min((float) $revenueGroups->sum('total_recharge'), (float) $invoice->subtotal), 2);
            $entry->journal_entry_lines()->create($line($salesAccount->id, 0, (float) $invoice->subtotal - $recharge, "Sales - Invoice {$invoice->invoice_number}"));
            if ($recharge > 0) {
                $entry->journal_entry_lines()->create($line(app(TripExpenseRecoveryService::class)->customerRecoverable()->id, 0, $recharge, "Recharged trip expenses - Invoice {$invoice->invoice_number}"));
            }
        } else {
            foreach ($revenueGroups as $group) {
                $account = ($group->account_id ? Account::find($group->account_id) : null) ?? $salesAccount;
                $revenue = (float) $group->total_subtotal - (float) $group->total_recharge;
                if (round($revenue, 2) != 0.0) {
                    $entry->journal_entry_lines()->create($line($account->id, 0, $revenue, "{$account->name} - Invoice {$invoice->invoice_number}"));
                }
            }
            // Recharged Customer-category trip expenses clear Customer Recoverable
            $recharge = round((float) $revenueGroups->sum('total_recharge'), 2);
            if ($recharge > 0) {
                $entry->journal_entry_lines()->create($line(app(TripExpenseRecoveryService::class)->customerRecoverable()->id, 0, $recharge, "Recharged trip expenses - Invoice {$invoice->invoice_number}"));
            }
        }

        // CR VAT
        if ($invoice->tax_amount > 0) {
            $entry->journal_entry_lines()->create($line($vatAccount->id, 0, (float) $invoice->tax_amount, "VAT - Invoice {$invoice->invoice_number}"));
        }

        // DR Sales Discounts
        if (!empty($invoice->discount_amount) && $invoice->discount_amount > 0 && $discountAccount) {
            $entry->journal_entry_lines()->create($line($discountAccount->id, (float) $invoice->discount_amount, 0, "Discount - Invoice {$invoice->invoice_number}"));
        }

        $entry->assertBalanced();

        return $entry;
    }
}
