<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\Bill;
use App\Models\BillExpense;
use App\Models\Expense;
use App\Models\Fuel;
use App\Models\JournalEntry;
use App\Models\TopUp;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Single place fuel/top-up Bills get built and posted - replaces the ~10
 * near-duplicate inline Bill/BillExpense blocks previously scattered across
 * Fuels/Pending.php, Fuels/Rejected.php, Fuels/Approved.php,
 * Containers/TopUps.php, TopUps/*.php and Trips/*.php (see the Fuel
 * Inventory design plan). Every method here posts explicitly via
 * BillJournalService rather than relying on BillObserver's
 * isDirty('authorization') auto-trigger, which is what let several of those
 * blocks silently never post in the first place (forgetting to set
 * to_be_paid alongside authorization in the same save).
 *
 * Accounting model:
 *  - Once Off Buy fuel: purchase and consumption are the same event -
 *    debit the trip/no-trip expense account, credit Accounts Payable.
 *  - Bulk Buy top-up: a genuine new payable - debit Fuel Inventory, credit
 *    Accounts Payable.
 *  - Bulk Buy consumption (a fuel order drawn from an already-paid-for
 *    tank): NOT a new payable - debit the trip/no-trip expense account,
 *    credit Fuel Inventory. Crediting AP again here would fabricate a
 *    liability with nothing left to ever clear it.
 */
class FuelJournalService
{
    public function __construct(
        private BillJournalService $billJournal,
        private JournalReversalService $journalReversal,
        private LedgerResyncService $ledgerResync
    ) {
    }

    /**
     * Bulk Buy container top-up. Builds/reuses the TopUp's Bill+BillExpense
     * (account -> Fuel Inventory, dimensioned to the container) and posts it
     * - a genuine new payable to the fuel vendor.
     */
    public function postTopUp(TopUp $topUp): JournalEntry
    {
        return DB::transaction(function () use ($topUp) {
            $fuelInventory = Account::where('name', 'Fuel Inventory')->firstOrFail();
            $fuelExpense = Expense::where('name', 'Fuel Topup')->first();

            $bill = Bill::where('top_up_id', $topUp->id)->first() ?: new Bill;
            $bill->user_id = $bill->user_id ?? Auth::id();
            $bill->bill_number = $bill->bill_number ?? $this->billNumber();
            $bill->container_id = $topUp->container_id;
            $bill->top_up_id = $topUp->id;
            $bill->vendor_id = $topUp->vendor_id;
            $bill->category = 'Fuel Topup';
            $bill->bill_date = $topUp->date;
            $bill->currency_id = $topUp->currency_id;
            $bill->exchange_rate = $topUp->exchange_rate;
            $bill->exchange_amount = $topUp->exchange_amount;
            $bill->total = $topUp->amount;
            $bill->subtotal = $topUp->amount;
            $bill->balance = $topUp->amount;
            $bill->authorized_by_id = $bill->authorized_by_id ?? Auth::id();
            // authorization/to_be_paid deliberately NOT set on this save -
            // BillObserver::created() auto-posts as soon as both are dirty
            // together, which would fire here before the BillExpense below
            // exists (an AP-only entry with no debit line, exactly the bug
            // this service exists to fix) and then block the correct post()
            // call further down via its own existing-entry guard. Set once
            // the BillExpense is in place instead.
            $bill->save();

            $billExpense = $bill->bill_expenses()->first() ?? new BillExpense;
            $billExpense->user_id = $billExpense->user_id ?? Auth::id();
            $billExpense->bill_id = $bill->id;
            $billExpense->currency_id = $bill->currency_id;
            $billExpense->expense_id = $fuelExpense?->id;
            $billExpense->account_id = $fuelInventory->id;
            $billExpense->account_type_id = $fuelInventory->account_type_id;
            $billExpense->qty = 1;
            $billExpense->amount = $topUp->amount;
            $billExpense->subtotal = $topUp->amount;
            $billExpense->subtotal_incl = $topUp->amount;
            $billExpense->save();

            $bill->to_be_paid = true;
            $bill->authorization = 'approved';
            $bill->save();

            return $this->billJournal->post($bill->fresh());
        });
    }

    /**
     * Fuel order consumption - both purchase types, trip-attached or not.
     * Bill::firstOrNew(['fuel_id' => ...]) is both the idempotency guard and
     * the dedupe guard: whichever caller reaches this first for a given
     * fuel order (the fuel order's own approval, or - once Phase 2 migrates
     * those callers - its parent trip's approval) gets/creates the same
     * Bill row, and BillJournalService's own existing-entry check prevents
     * a second JournalEntry either way.
     */
    public function postConsumption(Fuel $fuel): JournalEntry
    {
        return DB::transaction(function () use ($fuel) {
            $targetAccount = $fuel->trip_id
                ? Account::where('name', 'Fuel - COGS')->firstOrFail()
                : Account::where('name', 'Fuel - Ops')->firstOrFail();

            $fuelExpense = Expense::where('name', 'Fuel Topup')->first();
            $isOnceOffBuy = optional($fuel->container)->purchase_type === 'Once Off Buy';

            $bill = Bill::where('fuel_id', $fuel->id)->first() ?: new Bill;
            $bill->user_id = $bill->user_id ?? Auth::id();
            $bill->bill_number = $bill->bill_number ?? $this->billNumber();
            $bill->fuel_id = $fuel->id;
            $bill->container_id = $fuel->container_id;
            $bill->trip_id = $fuel->trip_id;
            $bill->trip_expense_id = optional($fuel->trip_expense)->id;
            $bill->horse_id = $fuel->horse_id;
            $bill->vehicle_id = $fuel->vehicle_id;
            $bill->asset_id = $fuel->asset_id;
            $bill->driver_id = $fuel->driver_id;
            $bill->category = $fuel->trip_id ? 'Trip Expense - Fuel Order' : 'Fuel';
            $bill->bill_date = $fuel->date;
            $bill->currency_id = $fuel->currency_id;
            $bill->exchange_rate = $fuel->exchange_rate;
            $bill->exchange_amount = $fuel->exchange_amount;
            $bill->total = $fuel->amount;
            $bill->subtotal = $fuel->amount;
            $bill->balance = $fuel->amount;
            $bill->authorized_by_id = $bill->authorized_by_id ?? Auth::id();
            // authorization/to_be_paid deliberately NOT set on this save -
            // see the matching note in postTopUp() above: setting both
            // together here would let BillObserver::created() auto-post
            // before the BillExpense below exists, producing an AP-only
            // entry with no debit line that then blocks the correct post()
            // call further down via its own existing-entry guard.
            $bill->save();

            $billExpense = $bill->bill_expenses()->first() ?? new BillExpense;
            $billExpense->user_id = $billExpense->user_id ?? Auth::id();
            $billExpense->bill_id = $bill->id;
            $billExpense->currency_id = $bill->currency_id;
            $billExpense->expense_id = $fuelExpense?->id;
            $billExpense->account_id = $targetAccount->id;
            $billExpense->account_type_id = $targetAccount->account_type_id;
            $billExpense->qty = $fuel->quantity;
            $billExpense->amount = $fuel->unit_price;
            $billExpense->subtotal = $fuel->amount;
            $billExpense->save();

            // Once Off Buy is a genuine new payable; Bulk Buy consumption
            // draws down inventory already paid for at top-up time - not a
            // new payable.
            $bill->to_be_paid = $isOnceOffBuy;
            $bill->authorization = 'approved';
            $bill->save();

            $bill = $bill->fresh();

            // resyncBill*, not a bare post() - postConsumption() is called
            // again whenever the fuel order is edited after approval (see
            // Fuels/Index::update()), and by then a JournalEntry usually
            // already exists for this bill. post()'s own dedup guard would
            // just hand that stale entry back unchanged; resync compares
            // the bill's now-current total/rate/currency against what's
            // actually posted and only reverses + reposts when they differ
            // - a no-op for a plain approval (nothing posted yet) or an
            // edit that didn't touch the amount/rate.
            if ($isOnceOffBuy) {
                return $this->ledgerResync->resyncBill(
                    $bill,
                    "Fuel order #{$fuel->id} consumption posted/updated"
                );
            }

            $fuelInventory = Account::where('name', 'Fuel Inventory')->firstOrFail();
            return $this->ledgerResync->resyncBillWithCreditAccount(
                $bill,
                $fuelInventory,
                "Fuel order #{$fuel->id} consumption posted/updated"
            );
        });
    }

    /**
     * For Fuels/Index::destroy() (Phase 3) - reverses the fuel order's
     * JournalEntry, if one was posted, instead of leaving it orphaned when
     * the fuel order itself gets deleted. container.balance/account_balance
     * restoration stays in destroy() itself - orthogonal to the GL.
     */
    public function reverseConsumption(Fuel $fuel): void
    {
        $bill = Bill::where('fuel_id', $fuel->id)->first();

        if (!$bill) {
            return;
        }

        $entry = JournalEntry::where('bill_id', $bill->id)
            ->where('status', '!=', 'reversed')
            ->where(fn ($q) => $q->whereNull('reference')->orWhere('reference', 'not like', 'REV-%'))
            ->first();

        if ($entry) {
            $this->journalReversal->reverse($entry, "Fuel order #{$fuel->id} deleted");
        }

        $bill->bill_expenses()->delete();
        $bill->delete();
    }

    private function billNumber(): string
    {
        $bill = Bill::latest()->orderBy('id', 'desc')->first();
        $number = $bill ? $bill->id + 1 : 1;
        return 'FB' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }
}
