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
use Illuminate\Support\Facades\Log;

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
    /** Bill line holding a split order's litres not allocated to any trip. */
    public const UNALLOCATED_LINE = 'Unallocated fuel (not on a trip)';

    public function __construct(
        private BillJournalService $billJournal,
        private JournalReversalService $journalReversal,
        private LedgerResyncService $ledgerResync,
        private CustomerFuelSupplyService $customerFuel
    ) {
    }

    /**
     * Bulk Buy container top-up. Builds/reuses the TopUp's Bill+BillExpense
     * (account -> Fuel Inventory, dimensioned to the container) and posts it
     * - a genuine new payable to the fuel vendor.
     *
     * A customer-supplied top-up (the customer delivered fuel into our tank)
     * has no vendor to pay: it settles against the customer instead -
     * DR Fuel Inventory / CR Accounts Receivable, see
     * CustomerFuelSupplyService.
     */
    public function postTopUp(TopUp $topUp): JournalEntry
    {
        if ($this->customerFuel->appliesToTopUp($topUp)) {
            return DB::transaction(function () use ($topUp) {
                // The supply takes over the top-up's supplier bill, settled by the customer.
                $supply = $this->customerFuel->syncFromTopUp($topUp);

                return $this->customerFuel->post($supply);
            });
        }

        $this->customerFuel->voidForTopUp($topUp);

        return DB::transaction(function () use ($topUp) {
            $fuelInventory = Account::where('name', 'Fuel Inventory')->firstOrFail();
            $fuelExpense = Expense::where('name', Expense::FUEL_TOPUP)->first();

            // A top-up entered by monetary value (Containers/Index.php's "account"
            // mode) leaves quantity/rate/amount null and puts the value in
            // account_amount instead - fall back to it so those top-ups still post.
            $billAmount = $topUp->amount ?: $topUp->account_amount;

            $bill = Bill::where('top_up_id', $topUp->id)->first() ?: new Bill;
            $bill->user_id = $bill->user_id ?? Auth::id();
            $bill->bill_number = $bill->bill_number ?? $this->billNumber();
            $bill->container_id = $topUp->container_id;
            $bill->top_up_id = $topUp->id;
            $bill->vendor_id = app(\App\Services\StationVendorService::class)->forTopUp($topUp);
            $bill->category = 'Fuel Topup';
            $bill->bill_date = $topUp->date;
            $bill->currency_id = $topUp->currency_id;
            $bill->exchange_rate = $topUp->exchange_rate;
            $bill->exchange_amount = $topUp->exchange_amount;
            $bill->total = $billAmount;
            $bill->subtotal = $billAmount;
            $bill->balance = $this->outstanding($bill, $billAmount);
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
            $billExpense->amount = $billAmount;
            $billExpense->subtotal = $billAmount;
            $billExpense->subtotal_incl = $billAmount;
            $accountChanged = $billExpense->exists && $billExpense->isDirty('account_id');
            $billExpense->save();

            $bill->to_be_paid = true;
            $bill->authorization = 'approved';
            $bill->save();

            // resyncBill, not post() - postTopUp() also runs when an approved
            // top-up is edited, and post() would hand back the existing entry
            // at the old amount. Resync is a no-op when nothing changed.
            return $this->ledgerResync->resyncBill(
                $bill->fresh(),
                "Top up {$topUp->order_number} posted/updated",
                force: $accountChanged
            );
        });
    }

    /**
     * Take a top-up back out of the ledger - it was deleted, or rejected
     * after approval. Reverses its supplier Bill (and removes it) or its
     * customer supply. Refuses, via CustomerFuelSupplyService::removeBill(),
     * when the supplier has already been paid against the bill.
     */
    public function reverseTopUp(TopUp $topUp, string $reason): void
    {
        DB::transaction(function () use ($topUp, $reason) {
            $this->customerFuel->voidForTopUp($topUp, $reason);
            $this->removeTopUpBill($topUp, $reason);
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
        // Customer-supplied Once Off Buy fuel: the station's bill is raised
        // as usual but settled by the customer (CustomerFuelSupplyService -
        // DR Fuel - COGS/Ops / CR AP, then DR AP / CR AR). A Bulk Buy fuel
        // order is drawn from our own stock, so it always falls through to
        // the normal inventory consumption below even if flagged - the
        // customer supply for it is recorded on the top-up that filled the tank.
        if ($this->customerFuel->appliesToFuel($fuel)) {
            return DB::transaction(function () use ($fuel) {
                // Legacy inline trip-approval bills (trip_expense_id only) - the
                // supply raises its own bill keyed on fuel_id.
                $tripExpenseId = optional($fuel->trip_expense)->id;
                if ($tripExpenseId) {
                    foreach (Bill::where('trip_expense_id', $tripExpenseId)->whereNull('fuel_id')->get() as $bill) {
                        $this->removeBill($bill, "Fuel order {$fuel->order_number} marked as customer supplied");
                    }
                }
                $supply = $this->customerFuel->syncFromFuel($fuel);

                return $this->customerFuel->post($supply);
            });
        }

        // Flag switched off (or the order moved to a Bulk Buy station) after
        // a supply was already posted - undo it before the normal Bill path.
        $this->customerFuel->voidForFuel($fuel);

        $entry = DB::transaction(function () use ($fuel) {
            // Fuel for a subcontracted (Transporter-category) trip is the
            // transporter's cost: DR Transporter Recoverable at cost, or cost +
            // markup with the markup credited to Fuel Margin (Business Settings).
            $recovery = app(TripExpenseRecoveryService::class);
            $isTransporterFuel = $recovery->isTransporterFuel($fuel);

            // An order split across trips may leave litres unallocated: only
            // the allocated share goes to the trip account (and is marked up);
            // the balance is booked to Fuel - Ops on its own line.
            [$allocatedQty, $allocatedCost] = $this->tripAllocation($fuel);
            $unallocatedCost = $fuel->trip_id ? round((float) $fuel->amount - $allocatedCost, 2) : 0.0;

            $markup = 0.0;
            if ($isTransporterFuel) {
                $targetAccount = $recovery->transporterRecoverable();
                $markup = $recovery->applyFuelMarkup($fuel, optional($fuel->trip)->company ?? optional(optional(Auth::user())->employee)->company, $allocatedCost);
            } else {
                $targetAccount = $fuel->trip_id
                    ? Account::where('name', 'Fuel - COGS')->firstOrFail()
                    : Account::where('name', 'Fuel - Ops')->firstOrFail();
            }
            $marginAccount = Account::where('name', 'Fuel Margin')->first();

            $fuelExpense = Expense::where('name', Expense::FUEL_TOPUP)->first();
            $isOnceOffBuy = optional($fuel->container)->purchase_type === 'Once Off Buy';

            $bill = Bill::where('fuel_id', $fuel->id)->first() ?: new Bill;
            // Line changes only need a forced repost when something was already posted.
            $hadEntry = $bill->exists && JournalEntry::where('bill_id', $bill->id)->exists();
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
            $bill->balance = $this->outstanding($bill, (float) $fuel->amount);
            $bill->authorized_by_id = $bill->authorized_by_id ?? Auth::id();
            // The fuel's own trip expense names the supplier; the station's
            // vendor is the fallback (created and linked if the station has
            // none). Never overwrite one already on the bill.
            $bill->vendor_id = $bill->vendor_id
                ?? app(\App\Services\StationVendorService::class)->forFuel($fuel);
            // authorization/to_be_paid deliberately NOT set on this save -
            // see the matching note in postTopUp() above: setting both
            // together here would let BillObserver::created() auto-post
            // before the BillExpense below exists, producing an AP-only
            // entry with no debit line that then blocks the correct post()
            // call further down via its own existing-entry guard.
            $bill->save();

            $billExpense = $bill->bill_expenses()
                ->when($marginAccount, fn ($q) => $q->where('account_id', '!=', $marginAccount->id))
                ->where(fn ($q) => $q->whereNull('description')->orWhere('description', '!=', self::UNALLOCATED_LINE))
                ->first() ?? new BillExpense;
            $billExpense->user_id = $billExpense->user_id ?? Auth::id();
            $billExpense->bill_id = $bill->id;
            $billExpense->currency_id = $bill->currency_id;
            $billExpense->expense_id = $fuelExpense?->id;
            $billExpense->account_id = $targetAccount->id;
            $billExpense->account_type_id = $targetAccount->account_type_id;
            $billExpense->qty = $fuel->trip_id ? $allocatedQty : $fuel->quantity;
            $billExpense->amount = $markup > 0 ? $fuel->transporter_price : $fuel->unit_price;
            $billExpense->subtotal = round(($fuel->trip_id ? $allocatedCost : (float) $fuel->amount) + $markup, 2);
            // A bill built by the old inline trip-approval code was booked to
            // "Trip Expense" - moving it to Fuel - COGS/Ops must repost, since
            // resync otherwise only compares the control account's amount.
            $accountChanged = $billExpense->exists && $billExpense->isDirty(['account_id', 'subtotal']);
            $billExpense->save();

            // Markup: a contra line crediting Fuel Margin keeps the bill total
            // (the payable / inventory drawn) at cost.
            $marginLine = $marginAccount ? $bill->bill_expenses()->where('account_id', $marginAccount->id)->first() : null;
            if ($markup > 0 && $marginAccount) {
                $marginLine = $marginLine ?? new BillExpense;
                $accountChanged = $accountChanged || !$marginLine->exists || round((float) $marginLine->subtotal, 2) !== round(-$markup, 2);
                $marginLine->user_id = $marginLine->user_id ?? Auth::id();
                $marginLine->bill_id = $bill->id;
                $marginLine->currency_id = $bill->currency_id;
                $marginLine->account_id = $marginAccount->id;
                $marginLine->account_type_id = $marginAccount->account_type_id;
                $marginLine->description = 'Fuel markup charged to the transporter';
                $marginLine->qty = 1;
                $marginLine->amount = -$markup;
                $marginLine->subtotal = -$markup;
                $marginLine->save();
            } elseif ($marginLine) {
                $marginLine->delete();
                $accountChanged = true;
            }

            $unallocatedLine = $bill->bill_expenses()->where('description', self::UNALLOCATED_LINE)->first();
            if ($unallocatedCost > 0.004) {
                $opsAccount = Account::where('name', 'Fuel - Ops')->firstOrFail();
                $unallocatedLine = $unallocatedLine ?? new BillExpense;
                $accountChanged = $accountChanged || !$unallocatedLine->exists || round((float) $unallocatedLine->subtotal, 2) !== $unallocatedCost;
                $unallocatedLine->user_id = $unallocatedLine->user_id ?? Auth::id();
                $unallocatedLine->bill_id = $bill->id;
                $unallocatedLine->currency_id = $bill->currency_id;
                $unallocatedLine->expense_id = $fuelExpense?->id;
                $unallocatedLine->account_id = $opsAccount->id;
                $unallocatedLine->account_type_id = $opsAccount->account_type_id;
                $unallocatedLine->description = self::UNALLOCATED_LINE;
                $unallocatedLine->qty = round((float) $fuel->quantity - $allocatedQty, 2);
                $unallocatedLine->amount = $fuel->unit_price;
                $unallocatedLine->subtotal = $unallocatedCost;
                $unallocatedLine->save();
            } elseif ($unallocatedLine) {
                $unallocatedLine->delete();
                $accountChanged = true;
            }

            // Once Off Buy is a genuine new payable; Bulk Buy consumption
            // draws down inventory already paid for at top-up time - not a
            // new payable.
            $bill->to_be_paid = $isOnceOffBuy;
            $bill->authorization = 'approved';
            $bill->save();

            $bill = $bill->fresh();
            $accountChanged = $accountChanged && $hadEntry;

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
                    "Fuel order #{$fuel->id} consumption posted/updated",
                    force: $accountChanged
                );
            }

            $fuelInventory = Account::where('name', 'Fuel Inventory')->firstOrFail();
            return $this->ledgerResync->resyncBillWithCreditAccount(
                $bill,
                $fuelInventory,
                "Fuel order #{$fuel->id} consumption posted/updated",
                force: $accountChanged
            );
        });

        // Transporter fuel is deducted from each allocated trip's transporter payment bill.
        if ($fuel->trip_id && app(TripExpenseRecoveryService::class)->isTransporterFuel($fuel)) {
            $tripIds = $fuel->trip_allocations()->pluck('trip_id')->push($fuel->trip_id)->unique();
            foreach (\App\Models\Trip::whereIn('id', $tripIds)->get() as $trip) {
                try {
                    app(TransporterPaymentBillService::class)->syncTrip($trip);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning("Transporter bill deductions for fuel order {$fuel->order_number} on trip {$trip->trip_number} not refreshed: " . $e->getMessage());
                }
            }
        }

        return $entry;
    }

    /**
     * postConsumption() for the trip approval/edit screens, which approve a
     * whole trip in one go: a posting failure (e.g. an unseeded Fuel - COGS /
     * Fuel Inventory account) is logged instead of rolling back the trip
     * approval around it - the inner transaction is a savepoint, so only the
     * fuel posting is undone. The fuel order's own Pending/Approved screens
     * keep calling postConsumption() directly.
     */
    public function postConsumptionSafely(Fuel $fuel): ?JournalEntry
    {
        try {
            return $this->postConsumption($fuel);
        } catch (\Throwable $e) {
            Log::error("FuelJournalService: posting fuel order #{$fuel->id} ({$fuel->order_number}) failed: " . $e->getMessage());

            return null;
        }
    }

    /**
     * For Fuels/Index::destroy() (Phase 3) - reverses the fuel order's
     * JournalEntry, if one was posted, instead of leaving it orphaned when
     * the fuel order itself gets deleted. container.balance/account_balance
     * restoration stays in destroy() itself - orthogonal to the GL.
     */
    public function reverseConsumption(Fuel $fuel): void
    {
        $this->customerFuel->voidForFuel($fuel, "Fuel order {$fuel->order_number} deleted");
        $this->removeConsumptionBill($fuel, "Fuel order #{$fuel->id} deleted");
    }

    /**
     * Reverse + remove the Bill a fuel order posted. Matches both the
     * service-built bill (bills.fuel_id) and the legacy inline trip-approval
     * bills, which only carry the trip_expense_id of the fuel's trip expense.
     */
    private function removeConsumptionBill(Fuel $fuel, string $reason): void
    {
        $tripExpenseId = optional($fuel->trip_expense)->id;

        $bills = Bill::where('fuel_id', $fuel->id)
            ->when($tripExpenseId, fn ($q) => $q->orWhere('trip_expense_id', $tripExpenseId))
            ->get();

        foreach ($bills as $bill) {
            $this->removeBill($bill, $reason);
        }
    }

    private function removeTopUpBill(TopUp $topUp, string $reason): void
    {
        foreach (Bill::where('top_up_id', $topUp->id)->get() as $bill) {
            $this->removeBill($bill, $reason);
        }
    }

    private function removeBill(Bill $bill, string $reason): void
    {
        $this->customerFuel->removeBill($bill, $reason);
    }

    /**
     * What's still owed on $bill at $total - re-running a posting after the
     * supplier was part-paid must not reset the balance to the full amount.
     */
    private function outstanding(Bill $bill, float $total): float
    {
        $paid = $bill->exists ? (float) $bill->bill_payments()->sum('amount') : 0.0;

        return max($total - $paid, 0);
    }

    /**
     * [litres, cost] of the order allocated to trips (FuelTripAllocationService).
     * A trip order with no allocation rows, or legacy rows without a quantity,
     * counts as wholly allocated.
     */
    private function tripAllocation(Fuel $fuel): array
    {
        $allocations = $fuel->trip_allocations()->get();
        if (!$fuel->trip_id || $allocations->isEmpty() || $allocations->contains(fn ($te) => $te->quantity === null)) {
            return [(float) $fuel->quantity, round((float) $fuel->amount, 2)];
        }

        $orderQty = (float) $fuel->quantity;
        $qty = min($orderQty, round((float) $allocations->sum('quantity'), 2));
        $cost = ($orderQty <= 0 || abs($orderQty - $qty) < 0.005)
            ? (float) $fuel->amount
            : (float) $fuel->amount * $qty / $orderQty;

        return [$qty, round($cost, 2)];
    }

    private function billNumber(): string
    {
        $bill = Bill::latest()->orderBy('id', 'desc')->first();
        $number = $bill ? $bill->id + 1 : 1;
        return 'FB' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }
}
