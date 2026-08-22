<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\Bill;
use App\Models\BillExpense;
use App\Models\JournalEntry;
use App\Models\TripExpense;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Posts manually-added trip expenses (the "Trip Expenses" tab under
 * Trips\Show - a driver toll, a manual cost, an allowance-type line picked
 * from the "Trip Expense" account dropdown) - the generic sibling of
 * FuelJournalService/InventoryJournalService. Does NOT handle fuel_id or
 * transporter_id-linked TripExpense rows - those are auto-managed and post
 * through FuelJournalService::postConsumption() and the transporter-payment
 * flow respectively.
 *
 * Bill::where('trip_expense_id', ...)->first() ?: new Bill is both the
 * idempotency guard and the fix for a real, confirmed-in-production bug:
 * every one of add/edit/trip-approve/trip-re-approve used to blindly create
 * a brand new Bill+BillExpense with no check for an existing one, leaving
 * up to 6 duplicate, mostly-unposted Bill rows for a single trip expense in
 * production data. Keying on trip_expense_id means every caller now finds
 * and updates the same one Bill instead.
 */
class TripExpenseJournalService
{
    public function __construct(private LedgerResyncService $ledgerResync)
    {
    }

    /**
     * Only handles generic trip expenses (not fuel/transporter-linked).
     * Returns null for those - the caller should route them through
     * FuelJournalService/the transporter-payment flow instead.
     */
    public function postExpense(TripExpense $tripExpense): ?JournalEntry
    {
        if ($tripExpense->fuel_id || $tripExpense->transporter_id) {
            return null;
        }

        return DB::transaction(function () use ($tripExpense) {
            $account = Account::where('name', 'Trip Expense')->firstOrFail();
            $trip = $tripExpense->trip;

            $bill = Bill::where('trip_expense_id', $tripExpense->id)->first() ?: new Bill;
            $bill->user_id = $bill->user_id ?? Auth::id();
            $bill->bill_number = $bill->bill_number ?? $this->billNumber();
            $bill->trip_id = $tripExpense->trip_id;
            $bill->trip_expense_id = $tripExpense->id;
            $bill->horse_id = optional($trip)->horse_id;
            $bill->vehicle_id = optional($trip)->vehicle_id;
            $bill->driver_id = optional($trip)->driver_id;
            $bill->account_id = $account->id;
            $bill->account_type_id = $account->account_type_id;
            $bill->category = 'Trip Expense';
            $bill->bill_date = $bill->bill_date ?? now()->toDateString();
            $bill->currency_id = $tripExpense->currency_id;
            $bill->vendor_id = $tripExpense->vendor_id;
            $bill->exchange_rate = $tripExpense->exchange_rate;
            $bill->exchange_amount = $tripExpense->exchange_amount;
            $bill->subtotal = $tripExpense->amount;
            $bill->total = $tripExpense->amount;
            $bill->balance = $tripExpense->amount;
            $bill->authorized_by_id = $bill->authorized_by_id ?? Auth::id();
            // authorization/to_be_paid deliberately NOT set on this save -
            // see FuelJournalService for why (BillObserver would otherwise
            // auto-post before the BillExpense below exists).
            $bill->save();

            $billExpense = $bill->bill_expenses()->first() ?? new BillExpense;
            $billExpense->user_id = $billExpense->user_id ?? Auth::id();
            $billExpense->bill_id = $bill->id;
            $billExpense->currency_id = $bill->currency_id;
            $billExpense->expense_id = $tripExpense->expense_id;
            $billExpense->allowance_id = $tripExpense->allowance_id;
            $billExpense->account_id = $account->id;
            $billExpense->account_type_id = $account->account_type_id;
            $billExpense->qty = 1;
            $billExpense->exchange_rate = $tripExpense->exchange_rate;
            $billExpense->exchange_amount = $tripExpense->exchange_amount;
            $billExpense->amount = $tripExpense->amount;
            $billExpense->subtotal = $tripExpense->amount;
            $billExpense->subtotal_incl = $tripExpense->amount;
            $billExpense->save();

            $bill->to_be_paid = true;
            $bill->authorization = 'approved';
            $bill->save();

            $bill = $bill->fresh();

            // resyncBill (not a bare post()) so editing an already-posted
            // trip expense's amount actually reaches the ledger - post()'s
            // own dedup check would otherwise just hand back the original,
            // now-stale entry since a JournalEntry already exists for this
            // bill. resyncBill compares the bill's current total/rate/
            // currency against what's already posted and only reverses +
            // reposts when they genuinely differ - a no-op for a plain add
            // (nothing posted yet) or an edit that didn't touch the amount.
            return $this->ledgerResync->resyncBill(
                $bill,
                "Trip expense #{$tripExpense->id} added/updated"
            );
        });
    }

    private function billNumber(): string
    {
        $bill = Bill::latest()->orderBy('id', 'desc')->first();
        $number = $bill ? $bill->id + 1 : 1;
        return 'TEB' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }
}
