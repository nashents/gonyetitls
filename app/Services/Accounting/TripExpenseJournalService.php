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
    public function __construct(
        private LedgerResyncService $ledgerResync,
        private CustomerFuelSupplyService $customerFuel
    ) {
    }

    /**
     * Only handles generic trip expenses (not fuel/transporter-linked).
     * Returns null for those - the caller should route them through
     * FuelJournalService/the transporter-payment flow instead.
     */
    public function postExpense(TripExpense $tripExpense): ?JournalEntry
    {
        $entry = $this->postExpenseLine($tripExpense);
        $this->refreshTransporterDeductions($tripExpense);

        return $entry;
    }

    /**
     * A Transporter-category line can change what the trip's transporter
     * payment bill deducts (TripExpenseRecoveryService) - re-sync it. Never
     * blocks the expense save.
     */
    public function refreshTransporterDeductions(TripExpense $tripExpense): void
    {
        $trip = $tripExpense->trip;
        if (!$trip || $tripExpense->transporter_id || !$trip->trip_expenses()->whereNotNull('transporter_id')->exists()) {
            return;
        }
        try {
            app(TransporterPaymentBillService::class)->syncTrip($trip);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Transporter bill deductions for trip {$trip->trip_number} not refreshed: " . $e->getMessage());
        }
    }

    private function postExpenseLine(TripExpense $tripExpense): ?JournalEntry
    {
        if ($tripExpense->fuel_id || $tripExpense->transporter_id) {
            return null;
        }

        // Funded By: Customer - the customer paid this cost (typically fuel
        // captured on the expenses list instead of as a fuel order). No
        // supplier Bill: DR Trip Expense / CR Accounts Receivable, applied to
        // the trip's invoice. See CustomerFuelSupplyService.
        if ($this->customerFuel->appliesToTripExpense($tripExpense)) {
            return DB::transaction(function () use ($tripExpense) {
                foreach (Bill::where('trip_expense_id', $tripExpense->id)->get() as $bill) {
                    $this->customerFuel->removeBill($bill, "Trip expense #{$tripExpense->id} marked as customer funded");
                }

                return $this->customerFuel->post($this->customerFuel->syncFromTripExpense($tripExpense));
            });
        }

        // Switched back to company funded - undo the customer supply first.
        $this->customerFuel->voidForTripExpense($tripExpense);

        // Funded By: Transporter - their own driver paid, so it is only a
        // record on the trip: no supplier bill, no ledger, no deduction.
        if (app(TripExpenseRecoveryService::class)->isTransporterFunded($tripExpense)) {
            DB::transaction(function () use ($tripExpense) {
                foreach (Bill::where('trip_expense_id', $tripExpense->id)->get() as $bill) {
                    if (! $bill->bill_payments()->exists()) {
                        $this->customerFuel->removeBill($bill, "Trip expense #{$tripExpense->id} funded by the transporter");
                    }
                }
            });

            return null;
        }

        // Non-billable expense (Expenses > Billable unchecked): it stays a
        // cost on the trip only - no supplier Bill, so a share of fuel that
        // the truck's fuel order already billed isn't billed a second time.
        if (! $this->isBillable($tripExpense)) {
            DB::transaction(function () use ($tripExpense) {
                foreach (Bill::where('trip_expense_id', $tripExpense->id)->get() as $bill) {
                    // A paid bill is left alone - its payments must be reversed first.
                    if (! $bill->bill_payments()->exists()) {
                        $this->customerFuel->removeBill($bill, "Trip expense #{$tripExpense->id} is non-billable");
                    }
                }
            });

            return null;
        }

        return DB::transaction(function () use ($tripExpense) {
            // Transporter / Customer category: recoverable from them, not a company cost.
            $account = app(TripExpenseRecoveryService::class)->recoverableAccountFor($tripExpense)
                ?? $this->accountFor($tripExpense);
            $trip = $tripExpense->trip;

            $bill = Bill::where('trip_expense_id', $tripExpense->id)->first() ?: new Bill;
            $previousBillDate = $bill->bill_date;
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
            $bill->bill_date = $tripExpense->date ?: ($bill->bill_date ?? now()->toDateString());
            $dateChanged = $previousBillDate !== null && $previousBillDate !== $bill->bill_date;
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
            // isUnchanged() only compares the AP leg, so a re-pointed debit
            // account must force the reverse+repost too.
            $accountChanged = $billExpense->exists && (int) $billExpense->account_id !== (int) $account->id;
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
            // $dateChanged forces that reverse+repost even when the amount/
            // rate/currency didn't move, since isUnchanged() only looks at
            // the control-account line and wouldn't otherwise notice a
            // date-only edit, leaving the journal entry's date stale.
            return $this->ledgerResync->resyncBill(
                $bill,
                "Trip expense #{$tripExpense->id} added/updated",
                $dateChanged || $accountChanged
            );
        });
    }

    /**
     * The debit account the user configured on the expense (Expenses >
     * Account) or allowance; the generic "Trip Expense" account only when
     * neither has one.
     */
    public function accountFor(TripExpense $tripExpense): Account
    {
        return optional($tripExpense->expense)->account
            ?: optional($tripExpense->allowance)->account
            ?: Account::where('name', 'Trip Expense')->firstOrFail();
    }

    public function isBillable(TripExpense $tripExpense): bool
    {
        $expense = $tripExpense->expense;

        // Seeded core expenses are always billable (see Expense::booted).
        return ! $expense || $expense->is_locked || ($expense->billable ?? true);
    }

    private function billNumber(): string
    {
        $bill = Bill::latest()->orderBy('id', 'desc')->first();
        $number = $bill ? $bill->id + 1 : 1;
        return 'TEB' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }
}
