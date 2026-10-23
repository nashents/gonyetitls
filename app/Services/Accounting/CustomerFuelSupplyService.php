<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\Bill;
use App\Models\BillExpense;
use App\Models\BillPayment;
use App\Models\Company;
use App\Models\CustomerFuelSupply;
use App\Models\Fuel;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\JournalEntry;
use App\Models\TopUp;
use App\Models\TripExpense;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Customer-supplied fuel - fuel the customer hands over in kind as
 * part-settlement of the trip they're paying for.
 *
 * The invoice stays at the full freight value (no negative fuel line - that
 * nets revenue against a cost, which IAS 1 doesn't allow, and under IFRS 15
 * the fuel is non-cash consideration measured at fair value). The fuel is
 * settled against the customer's receivable instead of creating a payable:
 *
 *  - Once Off Buy (fuel goes straight into the unit):
 *        DR Fuel - COGS (trip) / Fuel - Ops (no trip)   CR Accounts Receivable
 *    and NO fuel Bill - there is no supplier to pay.
 *  - Bulk Buy top-up (customer's fuel goes into our tank/stock first):
 *        DR Fuel Inventory   CR Accounts Receivable
 *    Consumption out of the tank is unchanged (DR COGS/Ops, CR Fuel
 *    Inventory) - it's our stock once received. So a Bulk Buy *fuel order*
 *    is never customer-supplied on its own; the supply is recorded on the
 *    top-up that brought the fuel in.
 *
 * The supply is then allocated to the trip's invoice through
 * invoice_payments (source = 'customer_fuel'), reducing its open balance
 * exactly like a receipt would, and shows on the customer statement as its
 * own line: Invoice 1,500 / Fuel supplied (500) / Balance 1,000.
 *
 * Trip expense lines work the same way, for clients who capture fuel (or any
 * trip cost the customer paid for) on the trip's expenses list rather than as
 * a fuel order: a customer-funded line posts DR Trip Expense / CR Accounts
 * Receivable instead of a supplier Bill.
 *
 * It deliberately isn't a Payment row: Payments feed cash flow, receipts,
 * the transactions ledger and bank reconciliation, and no cash moved here.
 *
 * Supplier bill (2026-10-23): the customer paid a real supplier (the
 * station's vendor / the expense line's vendor), so the supply also raises
 * that supplier's Bill - DR cost / CR Accounts Payable (vendor) - and the
 * supply itself posts DR Accounts Payable (vendor) / CR Accounts Receivable,
 * allocated to the bill through bill_payments (source = 'customer_fuel').
 * The bill shows as paid by the customer, the vendor statement nets to nil,
 * and the cost is a normal bill line for the horse/transporter P&L. Net
 * ledger effect is unchanged: DR cost / CR AR. A non-billable trip expense
 * still raises no bill and posts DR cost / CR AR directly.
 */
class CustomerFuelSupplyService
{
    public const SOURCE = 'customer_fuel';

    public function __construct(private JournalReversalService $journalReversal)
    {
    }

    // ── Sources ──────────────────────────────────────────────────────────────

    /**
     * Whether this fuel order should settle against the customer rather than
     * post a fuel Bill. Bulk Buy fuel orders never do - see class docblock.
     */
    public function appliesToFuel(Fuel $fuel): bool
    {
        return (bool) $fuel->supplied_by_customer
            && $fuel->customer_id
            && optional($fuel->container)->purchase_type !== 'Bulk Buy';
    }

    /**
     * Generic trip expense lines only - fuel-order and transporter lines are
     * posted through their own flows.
     */
    public function appliesToTripExpense(TripExpense $tripExpense): bool
    {
        return (bool) $tripExpense->supplied_by_customer
            && $tripExpense->customer_id
            && !$tripExpense->fuel_id
            && !$tripExpense->transporter_id;
    }

    public function appliesToTopUp(TopUp $topUp): bool
    {
        return (bool) $topUp->supplied_by_customer && $topUp->customer_id;
    }

    /**
     * Create/refresh the supply for an approved Once Off Buy fuel order and
     * (re)post it. Idempotent - safe to call again on every edit.
     */
    public function syncFromFuel(Fuel $fuel): CustomerFuelSupply
    {
        $fuel->loadMissing('container', 'trip', 'customer');

        $account = $fuel->trip_id
            ? Account::where('name', 'Fuel - COGS')->firstOrFail()
            : Account::where('name', 'Fuel - Ops')->firstOrFail();

        $supply = CustomerFuelSupply::where('fuel_id', $fuel->id)->first() ?? new CustomerFuelSupply(['fuel_id' => $fuel->id]);
        $tripExpense = $fuel->trip_expense;

        $bill = [
            'key'             => 'fuel_id',
            'prefix'          => 'FB',
            'category'        => $fuel->trip_id ? 'Trip Expense - Fuel Order' : 'Fuel',
            // Trip expense's supplier, else the station's vendor (created if missing).
            'vendor_id'       => app(\App\Services\StationVendorService::class)->forFuel($fuel),
            'fuel_id'         => $fuel->id,
            'trip_id'         => $fuel->trip_id,
            'trip_expense_id' => optional($tripExpense)->id,
            'container_id'    => $fuel->container_id,
            'horse_id'        => $fuel->horse_id,
            'vehicle_id'      => $fuel->vehicle_id,
            'asset_id'        => $fuel->asset_id,
            'driver_id'       => $fuel->driver_id,
            'expense_id'      => \App\Models\Expense::where('name', \App\Models\Expense::FUEL_TOPUP)->value('id'),
        ];

        return $this->fill($supply, $bill, [
            'customer_id'   => $fuel->customer_id,
            'currency_id'   => $fuel->currency_id,
            'exchange_rate' => $this->rate($fuel->exchange_rate),
            'trip_id'       => $fuel->trip_id,
            'container_id'  => $fuel->container_id,
            'purchase_type' => 'Once Off Buy',
            'account_id'    => $account->id,
            'date'          => $fuel->date ?: now()->toDateString(),
            'quantity'      => is_numeric($fuel->quantity) ? $fuel->quantity : null,
            'unit_price'    => is_numeric($fuel->unit_price) ? $fuel->unit_price : null,
            'amount'        => $this->fuelAmount($fuel),
            'description'   => trim("Fuel order {$fuel->order_number}" . ($fuel->trip ? " - Trip {$fuel->trip->trip_number}" : '')),
        ]);
    }

    /**
     * Create/refresh the supply for an approved Bulk Buy top-up the customer
     * delivered into our tank.
     */
    public function syncFromTopUp(TopUp $topUp): CustomerFuelSupply
    {
        $topUp->loadMissing('container', 'trip');

        $account = Account::where('name', 'Fuel Inventory')->firstOrFail();

        $amount = is_numeric($topUp->amount) && (float) $topUp->amount > 0
            ? (float) $topUp->amount
            : round((float) $topUp->quantity * (float) $topUp->rate, 2);

        $supply = CustomerFuelSupply::where('top_up_id', $topUp->id)->first() ?? new CustomerFuelSupply(['top_up_id' => $topUp->id]);

        // Like a company-paid top-up's bill: no trip, so it isn't swept up by trip deletion.
        $bill = [
            'key'          => 'top_up_id',
            'prefix'       => 'FB',
            'category'     => 'Fuel Topup',
            'vendor_id'    => app(\App\Services\StationVendorService::class)->forTopUp($topUp),
            'top_up_id'    => $topUp->id,
            'container_id' => $topUp->container_id,
            'expense_id'   => \App\Models\Expense::where('name', \App\Models\Expense::FUEL_TOPUP)->value('id'),
        ];

        return $this->fill($supply, $bill, [
            'customer_id'   => $topUp->customer_id,
            'currency_id'   => $topUp->currency_id,
            'exchange_rate' => $this->rate($topUp->exchange_rate),
            'trip_id'       => $topUp->trip_id,
            'container_id'  => $topUp->container_id,
            'purchase_type' => 'Bulk Buy',
            'account_id'    => $account->id,
            'date'          => $topUp->date ?: now()->toDateString(),
            'quantity'      => is_numeric($topUp->quantity) ? $topUp->quantity : null,
            'unit_price'    => is_numeric($topUp->rate) ? $topUp->rate : null,
            'amount'        => $amount,
            'description'   => trim("Top up {$topUp->order_number}" . ($topUp->container ? " into {$topUp->container->name}" : '') . ($topUp->trip ? " - Trip {$topUp->trip->trip_number}" : '')),
        ]);
    }

    /**
     * Create/refresh the supply for a customer-funded trip expense line and
     * (re)post it - DR the account its Bill would have debited (the
     * expense's/allowance's own account, "Trip Expense" only as a fallback -
     * same as TripExpenseJournalService::accountFor) / CR Accounts Receivable.
     */
    public function syncFromTripExpense(TripExpense $tripExpense): CustomerFuelSupply
    {
        $tripExpense->loadMissing('trip', 'expense.account', 'allowance.account');

        // Category Transporter: the customer paid, but the cost is the
        // transporter's - it is recoverable from them, not a company cost.
        $account = app(TripExpenseRecoveryService::class)->customerFundedDebitAccount($tripExpense)
            ?: optional($tripExpense->expense)->account
            ?: optional($tripExpense->allowance)->account
            ?: Account::where('name', 'Trip Expense')->firstOrFail();
        $label = optional($tripExpense->expense)->name ?? optional($tripExpense->allowance)->name ?? 'Trip expense';

        $supply = CustomerFuelSupply::where('trip_expense_id', $tripExpense->id)->first() ?? new CustomerFuelSupply(['trip_expense_id' => $tripExpense->id]);
        $trip = $tripExpense->trip;

        // Non-billable expenses (Expenses > Billable unchecked) raise no
        // supplier bill when company paid either - same here.
        $bill = app(TripExpenseJournalService::class)->isBillable($tripExpense) ? [
            'key'             => 'trip_expense_id',
            'prefix'          => 'TEB',
            'category'        => 'Trip Expense',
            'vendor_id'       => $tripExpense->vendor_id,
            'trip_id'         => $tripExpense->trip_id,
            'trip_expense_id' => $tripExpense->id,
            'horse_id'        => optional($trip)->horse_id,
            'vehicle_id'      => optional($trip)->vehicle_id,
            'driver_id'       => optional($trip)->driver_id,
            'expense_id'      => $tripExpense->expense_id,
            'allowance_id'    => $tripExpense->allowance_id,
        ] : null;

        return $this->fill($supply, $bill, [
            'customer_id'   => $tripExpense->customer_id,
            'currency_id'   => $tripExpense->currency_id,
            'exchange_rate' => $this->rate($tripExpense->exchange_rate),
            'trip_id'       => $tripExpense->trip_id,
            'purchase_type' => 'Trip Expense',
            'account_id'    => $account->id,
            'date'          => $tripExpense->date ?: (optional($tripExpense->trip)->start_date ? substr($tripExpense->trip->start_date, 0, 10) : now()->toDateString()),
            'quantity'      => null,
            'unit_price'    => null,
            'amount'        => round((float) $tripExpense->amount, 2),
            'description'   => trim($label . ($tripExpense->trip ? " - Trip {$tripExpense->trip->trip_number}" : '')),
        ]);
    }

    public function voidForTripExpense(TripExpense $tripExpense, ?string $reason = null): void
    {
        if ($supply = CustomerFuelSupply::where('trip_expense_id', $tripExpense->id)->first()) {
            $this->void($supply, $reason ?? "Trip expense #{$tripExpense->id} no longer customer funded");
        }
    }

    /**
     * Reverse + remove a supplier Bill that a customer-funded line replaces.
     * Refuses once the bill has payments against it - those need reversing
     * first, or the vendor would have been paid for something the customer
     * supplied.
     */
    public function removeBill(Bill $bill, string $reason): void
    {
        if ($bill->bill_payments()->exists()) {
            throw new \RuntimeException("Bill {$bill->bill_number} already has payments recorded against it - reverse those payments first.");
        }

        JournalEntry::where('bill_id', $bill->id)
            ->where('status', '!=', 'reversed')
            ->get()
            ->filter(fn ($entry) => $this->journalReversal->isLiveEffect($entry))
            ->each(fn ($entry) => $this->journalReversal->reverse($entry, $reason));

        $bill->bill_expenses()->delete();
        $bill->delete();
    }

    /** Undo a fuel order's supply (flag switched off, order deleted). */
    public function voidForFuel(Fuel $fuel, ?string $reason = null): void
    {
        if ($supply = CustomerFuelSupply::where('fuel_id', $fuel->id)->first()) {
            $this->void($supply, $reason ?? "Fuel order {$fuel->order_number} no longer customer-supplied");
        }
    }

    public function voidForTopUp(TopUp $topUp, ?string $reason = null): void
    {
        if ($supply = CustomerFuelSupply::where('top_up_id', $topUp->id)->first()) {
            $this->void($supply, $reason ?? "Top up {$topUp->order_number} no longer customer-supplied");
        }
    }

    /**
     * Reverse the supply's journal, release its invoice allocations back
     * onto the invoices' balances, and soft-delete it.
     */
    public function void(CustomerFuelSupply $supply, ?string $reason = null): void
    {
        DB::transaction(function () use ($supply, $reason) {
            foreach ($supply->invoice_payments()->get() as $allocation) {
                $this->deallocate($allocation);
            }

            // Release the supplier bill's "settled by customer" allocation.
            // The bill itself stays for the caller: the company-paid path
            // takes it over (the vendor is now owed), or the source's
            // deletion removes it.
            $this->releaseBillSettlements($supply);

            $this->reverseEntries($supply, $reason ?? "Customer fuel supply {$supply->supply_number} voided");

            $supply->delete();
        });
    }

    // ── Posting ──────────────────────────────────────────────────────────────

    /**
     * $bill describes the supplier Bill the supply raises (see
     * syncSupplierBill), or null for none.
     */
    private function fill(CustomerFuelSupply $supply, ?array $bill, array $attributes): CustomerFuelSupply
    {
        return DB::transaction(function () use ($supply, $bill, $attributes) {
            $wasCustomer = $supply->customer_id;
            $wasCurrency = $supply->currency_id;

            $supply->fill($attributes);
            $supply->user_id = $supply->user_id ?? Auth::id();
            $supply->company_id = $supply->company_id ?? $this->companyId();
            $supply->supply_number = $supply->supply_number ?? $this->supplyNumber();

            $postingChanged = !$supply->exists || $supply->isDirty(['customer_id', 'currency_id', 'exchange_rate', 'account_id', 'amount', 'date']);

            $supply->save();

            $this->syncSupplierBill($supply, $bill);

            // Bill raised/removed or its vendor changed - the debit side
            // (AP for that vendor vs the cost account) is now different.
            $postingChanged = $postingChanged || !$this->entryMatches($supply);

            // Allocations only make sense against the same customer's
            // invoices in the same currency - release them if either moved.
            if (($wasCustomer && $wasCustomer != $supply->customer_id) || ($wasCurrency && $wasCurrency != $supply->currency_id)) {
                foreach ($supply->invoice_payments()->get() as $allocation) {
                    $this->deallocate($allocation);
                }
            }

            $this->trimAllocations($supply);

            if ($postingChanged || !$this->activeEntry($supply)) {
                // A correction - the reversal stays in the original period.
                $this->reverseEntries($supply, "Customer fuel supply {$supply->supply_number} updated", true);
                $this->post($supply);
            }

            $this->settleBill($supply);

            $this->autoAllocate($supply);

            return $supply->fresh();
        });
    }

    public function post(CustomerFuelSupply $supply): JournalEntry
    {
        if ($existing = $this->activeEntry($supply)) {
            return $existing;
        }

        $supply->loadMissing('customer', 'fuel', 'account');

        $arAccount = Account::where('name', 'Accounts Receivable')->firstOrFail();
        $bill = $this->liveBill($supply);
        // With a supplier bill the customer settles that bill: DR AP (vendor).
        $debitAccount = $bill
            ? Account::where('name', 'Accounts Payable')->firstOrFail()
            : ($supply->account ?? Account::findOrFail($supply->account_id));
        $vendorId = $bill ? $bill->vendor_id : null;
        $amount = round((float) $supply->amount, 2);
        $rate = $this->rate($supply->exchange_rate);
        $customerName = optional($supply->customer)->name;

        return DB::transaction(function () use ($supply, $arAccount, $debitAccount, $vendorId, $bill, $amount, $rate, $customerName) {
            $entry = JournalEntry::create([
                'company_id'              => $supply->company_id ?? $this->companyId(),
                'customer_fuel_supply_id' => $supply->id,
                'journal_number'          => $this->journalNumber(),
                'date'                    => $supply->date,
                'reference'               => $supply->supply_number,
                'description'             => "Customer supplied fuel - {$customerName} - {$supply->supply_number}",
                'is_manual'               => false,
                'status'                  => 'posted',
                'created_by_id'           => Auth::id(),
                'posted_by_id'            => Auth::id(),
                'posted_at'               => now(),
            ]);

            $fuel = $supply->fuel;

            $entry->journal_entry_lines()->create([
                'account_id'      => $debitAccount->id,
                'vendor_id'       => $vendorId,
                'horse_id'        => $fuel?->horse_id,
                'driver_id'       => $fuel?->driver_id,
                'vehicle_id'      => $fuel?->vehicle_id,
                'container_id'    => $supply->container_id,
                'debit'           => $amount,
                'credit'          => 0,
                'exchange_debit'  => $amount * $rate,
                'exchange_credit' => 0,
                'currency_id'     => $supply->currency_id,
                'exchange_rate'   => $rate,
                'description'     => $bill
                    ? "AP - " . (optional($bill->vendor)->name ?? 'supplier') . " - Bill {$bill->bill_number} settled by {$customerName} - {$supply->description}"
                    : "{$debitAccount->name} - customer supplied fuel - {$supply->description}",
            ]);

            $entry->journal_entry_lines()->create([
                'account_id'      => $arAccount->id,
                'customer_id'     => $supply->customer_id,
                'debit'           => 0,
                'credit'          => $amount,
                'exchange_debit'  => 0,
                'exchange_credit' => $amount * $rate,
                'currency_id'     => $supply->currency_id,
                'exchange_rate'   => $rate,
                'description'     => "Fuel supplied by {$customerName} - {$supply->supply_number}",
            ]);

            $entry->assertBalanced();

            return $entry;
        });
    }

    // ── Supplier bill ────────────────────────────────────────────────────────

    /**
     * Create/refresh the supplier's Bill for the supply - DR the supply's
     * cost account / CR Accounts Payable (vendor) - or remove it when $spec
     * is null. Takes over the bill the company-paid path raised for the same
     * source (same fuel_id / top_up_id / trip_expense_id), so switching
     * Funded By back and forth never duplicates it. Refuses when that bill
     * already has real supplier payments - the vendor would have been paid
     * for something the customer paid for.
     *
     * $spec: key (source column), prefix, category, vendor_id and the
     * bill's source/dimension columns.
     */
    private function syncSupplierBill(CustomerFuelSupply $supply, ?array $spec): ?Bill
    {
        $bill = $supply->bill_id ? Bill::find($supply->bill_id) : null;
        if (!$bill && $spec) {
            $bill = Bill::where($spec['key'], $spec[$spec['key']])->first();
        }

        if ($bill && $bill->bill_payments()->where(fn ($q) => $q->whereNull('customer_fuel_supply_id')->orWhere('customer_fuel_supply_id', '!=', $supply->id))->exists()) {
            throw new \RuntimeException("Bill {$bill->bill_number} already has supplier payments recorded against it - reverse those payments first.");
        }

        if (!$spec) {
            if ($bill) {
                $this->releaseBillSettlements($supply);
                $this->removeBill($bill, "{$supply->supply_number} raises no supplier bill");
            }
            if ($supply->bill_id) {
                $supply->bill_id = null;
                $supply->save();
            }

            return null;
        }

        // Fresh lookup - the loaded relation may predate an account change.
        $account = Account::findOrFail($supply->account_id);
        $amount = round((float) $supply->amount, 2);
        $rate = $this->rate($supply->exchange_rate);
        $date =$supply->date ? $supply->date->toDateString() : now()->toDateString();

        $bill = $bill ?? new Bill;
        $previousDate = $bill->bill_date;
        $bill->user_id = $bill->user_id ?? Auth::id();
        $bill->bill_number = $bill->bill_number ?? $this->billNumber($spec['prefix']);
        $bill->category = $spec['category'];
        foreach (['fuel_id', 'top_up_id', 'trip_id', 'trip_expense_id', 'container_id', 'horse_id', 'vehicle_id', 'asset_id', 'driver_id'] as $column) {
            if (array_key_exists($column, $spec)) {
                $bill->{$column} = $spec[$column];
            }
        }
        // Never blank out a vendor someone set on the bill by hand.
        $bill->vendor_id = $spec['vendor_id'] ?: $bill->vendor_id;
        $bill->account_id = $account->id;
        $bill->account_type_id = $account->account_type_id;
        $bill->bill_date = $date;
        $bill->currency_id = $supply->currency_id;
        $bill->exchange_rate = $rate;
        $bill->exchange_amount = round($amount * $rate, 2);
        $bill->subtotal = $amount;
        $bill->total = $amount;
        $bill->authorized_by_id = $bill->authorized_by_id ?? Auth::id();
        $vendorChanged = $bill->exists && $bill->isDirty('vendor_id');
        $dateChanged = $previousDate !== null && substr((string) $previousDate, 0, 10) !== $date;
        // authorization/to_be_paid deliberately NOT set on this save - see
        // FuelJournalService::postTopUp (BillObserver would post before the
        // BillExpense below exists).
        $bill->save();

        // One line - drop any other (e.g. a transporter fuel markup line
        // from when the fuel was company paid).
        $lines = $bill->bill_expenses()->orderBy('id')->get();
        $line = $lines->shift() ?? new BillExpense;
        $linesRemoved = $lines->isNotEmpty();
        $lines->each->delete();

        $quantity = is_numeric($supply->quantity) && (float) $supply->quantity > 0 ? (float) $supply->quantity : null;
        $line->user_id = $line->user_id ?? Auth::id();
        $line->bill_id = $bill->id;
        $line->currency_id = $bill->currency_id;
        $line->expense_id = $spec['expense_id'] ?? null;
        $line->allowance_id = $spec['allowance_id'] ?? null;
        $line->account_id = $account->id;
        $line->account_type_id = $account->account_type_id;
        $line->description = "{$supply->description} - paid by " . (optional($supply->customer)->name ?? 'customer');
        $line->qty = $quantity ?? 1;
        $line->amount = $quantity && is_numeric($supply->unit_price) ? $supply->unit_price : $amount;
        $line->exchange_rate = $rate;
        $line->exchange_amount = round($amount * $rate, 2);
        $line->subtotal = $amount;
        $line->subtotal_incl = $amount;
        $accountChanged = $line->exists && $line->isDirty(['account_id', 'subtotal']);
        $line->save();

        $bill->to_be_paid = true;
        $bill->authorization = 'approved';
        $bill->save();

        if ((int) $supply->bill_id !== (int) $bill->id) {
            $supply->bill_id = $bill->id;
            $supply->save();
        }

        // resyncBill: reposts only when the bill differs from what's posted
        // (forced for a date/account/vendor change it can't see itself).
        app(LedgerResyncService::class)->resyncBill(
            $bill->fresh(),
            "Customer funded {$supply->supply_number} - supplier bill posted/updated",
            $dateChanged || $accountChanged || $vendorChanged || $linesRemoved
        );

        return $bill;
    }

    /**
     * Allocate the supply to its supplier bill (bill_payments, source
     * 'customer_fuel') so the bill shows as settled by the customer.
     */
    private function settleBill(CustomerFuelSupply $supply): void
    {
        $bill = $this->liveBill($supply);

        // An allocation left on another bill (the supply moved bills).
        BillPayment::where('customer_fuel_supply_id', $supply->id)
            ->when($bill, fn ($q) => $q->where('bill_id', '!=', $bill->id))
            ->get()
            ->each(fn ($allocation) => $this->removeBillSettlement($allocation));

        if (!$bill) {
            return;
        }

        $allocation = BillPayment::where('customer_fuel_supply_id', $supply->id)->where('bill_id', $bill->id)->first() ?? new BillPayment;
        $allocation->vendor_id = $bill->vendor_id;
        $allocation->bill_id = $bill->id;
        $allocation->customer_fuel_supply_id = $supply->id;
        $allocation->source = self::SOURCE;
        $allocation->currency_id = $bill->currency_id;
        $allocation->amount = round(min((float) $supply->amount, (float) $bill->total), 2);
        $allocation->save();

        $this->refreshBillBalance($bill);
    }

    private function releaseBillSettlements(CustomerFuelSupply $supply): void
    {
        BillPayment::where('customer_fuel_supply_id', $supply->id)
            ->get()
            ->each(fn ($allocation) => $this->removeBillSettlement($allocation));
    }

    private function removeBillSettlement(BillPayment $allocation): void
    {
        $bill = Bill::find($allocation->bill_id);
        $allocation->delete();

        if ($bill) {
            $this->refreshBillBalance($bill);
        }
    }

    private function refreshBillBalance(Bill $bill): void
    {
        $total = round((float) $bill->total, 2);
        $paid = round((float) $bill->bill_payments()->sum(DB::raw('COALESCE(amount+0,0)')), 2);
        $balance = round(max(0, $total - $paid), 2);

        $bill->balance = $balance;
        $bill->status = $balance <= 0 ? 'Paid' : ($balance < $total ? 'Partial' : 'Unpaid');
        $bill->save();
    }

    private function liveBill(CustomerFuelSupply $supply): ?Bill
    {
        $bill = $supply->bill_id ? Bill::find($supply->bill_id) : null;

        return $bill && $bill->authorization === 'approved' ? $bill : null;
    }

    /** Whether the posted entry still debits what it should (AP for the bill's vendor, else the cost account). */
    private function entryMatches(CustomerFuelSupply $supply): bool
    {
        $entry = $this->activeEntry($supply);
        if (!$entry) {
            return false;
        }

        $debit = $entry->journal_entry_lines()->where('debit', '>', 0)->first();
        if (!$debit) {
            return false;
        }

        $bill = $this->liveBill($supply);
        if ($bill) {
            return (int) $debit->account_id === (int) Account::where('name', 'Accounts Payable')->value('id')
                && (int) $debit->vendor_id === (int) $bill->vendor_id;
        }

        return (int) $debit->account_id === (int) $supply->account_id;
    }

    private function activeEntry(CustomerFuelSupply $supply): ?JournalEntry
    {
        return JournalEntry::where('customer_fuel_supply_id', $supply->id)
            ->where('status', '!=', 'reversed')
            ->where(fn ($q) => $q->whereNull('reference')->orWhere('reference', 'not like', 'REV-%'))
            ->whereHas('journal_entry_lines')
            ->latest('id')
            ->first();
    }

    private function reverseEntries(CustomerFuelSupply $supply, string $reason, bool $originalDate = false): void
    {
        $this->journalReversal->reverseLiveEffects(
            JournalEntry::where('customer_fuel_supply_id', $supply->id),
            $reason,
            $originalDate
        );
    }

    // ── Allocation to invoices ───────────────────────────────────────────────

    /**
     * Apply (part of) a supply against an invoice's open balance. Returns
     * the allocation, or null when there's nothing to apply.
     */
    public function allocate(CustomerFuelSupply $supply, Invoice $invoice, ?float $amount = null): ?InvoicePayment
    {
        if ((int) $invoice->customer_id !== (int) $supply->customer_id || (int) $invoice->currency_id !== (int) $supply->currency_id) {
            throw new \RuntimeException("Fuel supply {$supply->supply_number} can only be applied to the same customer's invoices in the same currency.");
        }

        return DB::transaction(function () use ($supply, $invoice, $amount) {
            $invoice = Invoice::lockForUpdate()->find($invoice->id);
            $invoiceBalance = round((float) $invoice->balance, 2);
            $available = $supply->unallocatedAmount();

            $apply = round(min($amount ?? $available, $available, $invoiceBalance), 2);

            if ($apply <= 0) {
                return null;
            }

            $allocation = new InvoicePayment;
            $allocation->customer_id = $invoice->customer_id;
            $allocation->invoice_id = $invoice->id;
            $allocation->customer_fuel_supply_id = $supply->id;
            $allocation->source = self::SOURCE;
            $allocation->currency_id = $invoice->currency_id;
            $allocation->amount = $apply;
            $allocation->save();

            $this->setInvoiceBalance($invoice, $invoiceBalance - $apply);

            return $allocation;
        });
    }

    public function deallocate(InvoicePayment $allocation): void
    {
        DB::transaction(function () use ($allocation) {
            $invoice = Invoice::lockForUpdate()->find($allocation->invoice_id);

            if ($invoice) {
                $this->setInvoiceBalance($invoice, (float) $invoice->balance + (float) $allocation->amount);
            }

            $allocation->delete();
        });
    }

    /**
     * Apply a supply to the approved invoice(s) raised for its trip.
     */
    public function autoAllocate(CustomerFuelSupply $supply): void
    {
        if (!$supply->trip_id || $supply->unallocatedAmount() <= 0) {
            return;
        }

        $invoices = Invoice::where('customer_id', $supply->customer_id)
            ->where('currency_id', $supply->currency_id)
            ->where('authorization', 'approved')
            ->whereHas('invoice_items', fn ($q) => $q->where('trip_id', $supply->trip_id))
            ->whereRaw('CAST(balance AS DECIMAL(20,2)) > 0')
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        foreach ($invoices as $invoice) {
            if ($supply->unallocatedAmount() <= 0) {
                break;
            }
            $this->allocate($supply, $invoice);
        }
    }

    /**
     * Apply any open supplies for the trips on a just-approved invoice.
     */
    public function autoAllocateForInvoice(Invoice $invoice): void
    {
        $tripIds = $invoice->invoice_items()->whereNotNull('trip_id')->pluck('trip_id')->unique();

        if ($tripIds->isEmpty()) {
            return;
        }

        $supplies = CustomerFuelSupply::whereIn('trip_id', $tripIds)
            ->where('customer_id', $invoice->customer_id)
            ->where('currency_id', $invoice->currency_id)
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        foreach ($supplies as $supply) {
            if ((float) Invoice::find($invoice->id)->balance <= 0) {
                break;
            }
            $this->allocate($supply, $invoice);
        }
    }

    /** Total customer-supplied fuel applied to an invoice. */
    public function allocatedToInvoice(Invoice $invoice): float
    {
        return round((float) InvoicePayment::where('invoice_id', $invoice->id)
            ->where('source', self::SOURCE)
            ->sum(DB::raw('COALESCE(amount+0,0)')), 2);
    }

    /**
     * If the supply's amount was reduced below what's already applied,
     * release the most recent allocations until it fits again.
     */
    private function trimAllocations(CustomerFuelSupply $supply): void
    {
        $excess = round($supply->allocatedAmount() - (float) $supply->amount, 2);

        if ($excess <= 0) {
            return;
        }

        foreach ($supply->invoice_payments()->orderByDesc('id')->get() as $allocation) {
            if ($excess <= 0) {
                break;
            }

            $amount = (float) $allocation->amount;

            if ($amount <= $excess) {
                $this->deallocate($allocation);
                $excess = round($excess - $amount, 2);
                continue;
            }

            $invoice = Invoice::lockForUpdate()->find($allocation->invoice_id);
            if ($invoice) {
                $this->setInvoiceBalance($invoice, (float) $invoice->balance + $excess);
            }
            $allocation->amount = round($amount - $excess, 2);
            $allocation->save();
            $excess = 0;
        }
    }

    private function setInvoiceBalance(Invoice $invoice, float $balance): void
    {
        $balance = round(max(0, min($balance, (float) $invoice->total)), 2);

        $invoice->balance = $balance;
        $invoice->status = $balance <= 0 ? 'Paid' : ($balance < round((float) $invoice->total, 2) ? 'Partial' : 'Unpaid');
        $invoice->save();
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function fuelAmount(Fuel $fuel): float
    {
        $amount = is_numeric($fuel->amount) ? (float) $fuel->amount : 0.0;

        return $amount > 0 ? round($amount, 2) : round((float) $fuel->quantity * (float) $fuel->unit_price, 2);
    }

    private function rate($rate): float
    {
        return is_numeric($rate) && (float) $rate > 0 ? (float) $rate : 1.0;
    }

    private function companyId(): ?int
    {
        return Auth::user()?->employee?->company_id ?? Company::value('id');
    }

    private function supplyNumber(): string
    {
        $next = (int) CustomerFuelSupply::withTrashed()->max('id') + 1;

        return 'CFS' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }

    /** Same numbering as the company-paid bills (FB / TEB + next bill id). */
    private function billNumber(string $prefix): string
    {
        $next = (int) Bill::withTrashed()->max('id') + 1;

        return $prefix . str_pad($next, 5, '0', STR_PAD_LEFT);
    }

    private function journalNumber(): string
    {
        $last = JournalEntry::orderByDesc('id')->value('journal_number');
        $next = $last ? ((int) substr($last, 4)) + 1 : 1;

        return 'JNL-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }
}
