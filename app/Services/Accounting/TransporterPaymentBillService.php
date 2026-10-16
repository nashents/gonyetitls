<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\Bill;
use App\Models\BillExpense;
use App\Models\Expense;
use App\Models\JournalEntry;
use App\Models\Trip;
use App\Models\TripExpense;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Keeps a subcontracted trip's "Transporter Payment" bill in step with its
 * transporter trip expense (TripExpense.transporter_id) and posts it to the
 * ledger: DR Creditor Payment (the Transporter Payment expense's account) /
 * CR Accounts Payable.
 *
 * Sibling of TripExpenseJournalService. Bill::where('trip_expense_id', ...)
 * is the idempotency key, so trip edits, approval and re-approval all update
 * the one bill instead of stacking duplicates. Previously trip approval
 * built these bills inline and they never reached the ledger, and editing a
 * trip only created a bill the first time its transporter expense appeared.
 */
class TransporterPaymentBillService
{
    public const CATEGORY = 'Trip Expense - Transporter Payment';

    public function __construct(
        private LedgerResyncService $ledgerResync,
        private CustomerFuelSupplyService $billRemover
    ) {
    }

    /**
     * Create/update the bill for an approved trip's transporter expense and
     * (re)post it. Returns the bill, or null when there is nothing to bill.
     * A posting failure is logged and leaves the bill unposted (picked up by
     * the unposted-bills "Post to Ledger" retry) rather than rolling back the
     * trip save that called it.
     */
    public function sync(TripExpense $tripExpense, ?string &$warning = null): ?Bill
    {
        $trip = $tripExpense->trip;

        if (! $tripExpense->transporter_id || ! $trip || $trip->authorization !== 'approved') {
            return null;
        }

        $amount = round((float) $tripExpense->amount, 2);
        $bill = $this->findBill($tripExpense);

        // No transporter freight (yet) - nothing to owe. Drop an unpaid bill
        // left over from an earlier figure; a paid one is left for review.
        if ($amount <= 0) {
            if ($bill && ! $bill->bill_payments()->exists()) {
                $this->billRemover->removeBill($bill, "Trip {$trip->trip_number} transporter freight cleared");
            }
            return null;
        }

        // Deduct mode (Business Settings): company-paid Transporter-category
        // trip expenses come off this bill as a contra line crediting
        // Transporter Recoverable - capped at the payment itself.
        [$deduction, $deductionNote] = $this->deduction($tripExpense, $trip, $amount);
        $net = round($amount - $deduction, 2);

        // Posted only once the trip is Offloaded - the transporter has
        // delivered and the debt is real (matching advance-invoice revenue
        // recognition). Before that the bill is a pending commitment. A bill
        // already paid stays posted (its payments need the payable).
        $offloaded = $trip->trip_status === 'Offloaded';
        $keepPosted = !$offloaded && $bill && $bill->bill_payments()->exists() && $this->isPosted($bill);
        $post = $offloaded || $keepPosted;

        $dateChanged = false;
        $bill = DB::transaction(function () use ($tripExpense, $trip, $amount, $bill, &$dateChanged, $deduction, $deductionNote, $net, $offloaded, $post) {
            // The seeded "Transporter Payment" expense's own account (Creditor
            // Payment - COGS, see DefaultExpenseResolver), matching
            // TripExpenseRepairService; Trip Expense only as a fallback.
            $expense = Expense::where('name', 'Transporter Payment')->first();
            $account = optional($expense)->account ?: Account::where('name', 'Trip Expense')->firstOrFail();
            $foreign = $tripExpense->currency_id && $tripExpense->currency_id != optional($trip->company)->currency_id;

            $bill = $bill ?: new Bill;
            $previousBillDate = $bill->bill_date;
            $bill->user_id = $bill->user_id ?? Auth::id();
            $bill->bill_number = $bill->bill_number ?? $this->billNumber($trip);
            $bill->trip_id = $trip->id;
            $bill->trip_expense_id = $tripExpense->id;
            $bill->transporter_id = $tripExpense->transporter_id;
            $bill->vendor_id = $tripExpense->vendor_id;
            $bill->horse_id = $trip->horse_id;
            $bill->vehicle_id = $trip->vehicle_id;
            $bill->category = self::CATEGORY;
            $bill->account_id = $account->id;
            $bill->account_type_id = $account->account_type_id;
            // Dated when the debt arises: the offload date once Offloaded.
            $bill->bill_date = $offloaded
                ? (substr((string) ($trip->end_date ?: $trip->start_date), 0, 10) ?: now()->toDateString())
                : ($trip->start_date ?: ($bill->bill_date ?? now()->toDateString()));
            $bill->currency_id = $tripExpense->currency_id;
            $bill->exchange_rate = $foreign ? $tripExpense->exchange_rate : null;
            $bill->exchange_amount = $foreign ? $tripExpense->exchange_amount : null;
            $bill->subtotal = $net;
            $bill->total = $net;
            $bill->balance = round($net - (float) $bill->bill_payments()->sum('amount'), 2);
            $bill->authorized_by_id = $bill->authorized_by_id ?? ($trip->authorized_by_id ?: Auth::id());
            // authorization/to_be_paid set only after the line exists - see
            // TripExpenseJournalService (BillObserver would post too early).
            $bill->save();

            $recoverable = app(TripExpenseRecoveryService::class)->transporterRecoverable();
            $line = $bill->bill_expenses()->where('account_id', '!=', $recoverable->id)->first() ?? new BillExpense;
            $line->user_id = $line->user_id ?? Auth::id();
            $line->bill_id = $bill->id;
            $line->currency_id = $bill->currency_id;
            $line->expense_id = optional($expense)->id;
            $line->account_id = $account->id;
            $line->account_type_id = $account->account_type_id;
            $line->qty = 1;
            $line->exchange_rate = $bill->exchange_rate;
            $line->exchange_amount = $bill->exchange_amount;
            $line->amount = $amount;
            $line->subtotal = $amount;
            $line->subtotal_incl = $amount;
            $line->save();

            $keep = [$line->id];
            if ($deduction > 0) {
                $less = $bill->bill_expenses()->where('account_id', $recoverable->id)->first() ?? new BillExpense;
                $less->user_id = $less->user_id ?? Auth::id();
                $less->bill_id = $bill->id;
                $less->currency_id = $bill->currency_id;
                $less->account_id = $recoverable->id;
                $less->account_type_id = $recoverable->account_type_id;
                $less->description = $deductionNote;
                $less->qty = 1;
                $less->exchange_rate = $bill->exchange_rate;
                $less->amount = -$deduction;
                $less->subtotal = -$deduction;
                $less->subtotal_incl = -$deduction;
                $less->save();
                $keep[] = $less->id;
            }

            // Any extra lines from older duplicate-building flows would
            // unbalance the posting - the bill is the payment line plus the
            // optional deduction line.
            $bill->bill_expenses()->whereNotIn('id', $keep)->delete();

            $bill->to_be_paid = $post;
            $bill->authorization = $post ? 'approved' : 'pending';
            $bill->save();

            $dateChanged = $previousBillDate !== null && $previousBillDate !== $bill->bill_date;

            return $bill;
        });

        try {
            if ($post) {
                $this->ledgerResync->resyncBill(
                    $bill->fresh(),
                    "Trip {$trip->trip_number} transporter payment updated",
                    $dateChanged
                );
            } else {
                // Not Offloaded (yet, or any more): no live posting.
                $reversal = app(JournalReversalService::class);
                $reversal->reverseLiveEffects(
                    JournalEntry::where('bill_id', $bill->id),
                    "Trip {$trip->trip_number} not Offloaded - transporter payment not yet due"
                );
            }
        } catch (\Throwable $e) {
            $warning = "Transporter bill {$bill->bill_number} saved but its ledger posting was not updated: " . $e->getMessage();
            Log::error($warning);
        }

        return $bill->fresh();
    }

    /**
     * [amount, description] to take off the transporter payment in deduct
     * mode: company-paid Transporter-category trip expenses converted to the
     * payment's currency, capped at the payment. [0, null] in invoice mode.
     */
    private function deduction(TripExpense $tripExpense, Trip $trip, float $amount): array
    {
        $recovery = app(TripExpenseRecoveryService::class);
        if ($recovery->transporterRecoveryMode($trip->company) !== 'deduct') {
            return [0.0, null];
        }

        $items = $recovery->transporterDeductions($trip);
        if ($items->isEmpty()) {
            return [0.0, null];
        }

        $total = round($items->sum(fn (TripExpense $e) => $recovery->amountIn($e, $tripExpense->currency_id, $trip->company, $tripExpense->exchange_rate)), 2);
        $names = $items->map(fn (TripExpense $e) => optional($e->expense)->name ?? optional($e->allowance)->name ?? 'Expense')->unique()->implode(', ');

        return [min($total, $amount), "Less: trip expenses paid on the transporter's behalf ({$names})"];
    }

    /** Sync every transporter expense on the trip (normally one). */
    public function syncTrip(Trip $trip, ?string &$warning = null): void
    {
        foreach ($trip->trip_expenses()->whereNotNull('transporter_id')->get() as $tripExpense) {
            $this->sync($tripExpense, $warning);
        }
    }

    public function isPosted(Bill $bill): bool
    {
        $reversal = app(JournalReversalService::class);

        return JournalEntry::where('bill_id', $bill->id)->get()
            ->contains(fn ($entry) => $reversal->isLiveEffect($entry));
    }

    private function findBill(TripExpense $tripExpense): ?Bill
    {
        return Bill::where('trip_expense_id', $tripExpense->id)->first()
            // Bills built by the old inline flows before trip_expense_id was
            // always stamped.
            ?? Bill::where('trip_id', $tripExpense->trip_id)
                ->where('transporter_id', $tripExpense->transporter_id)
                ->where('category', self::CATEGORY)
                ->whereNull('trip_expense_id')
                ->first();
    }

    /** Same <company initials>B00000 format the trip screens use. */
    private function billNumber(Trip $trip): string
    {
        $words = explode(' ', (string) optional($trip->company ?? optional(optional(Auth::user())->employee)->company)->name);
        $initials = ($words[0][0] ?? '') . ($words[1][0] ?? '');

        $bill = Bill::latest()->orderBy('id', 'desc')->first();
        $number = $bill ? $bill->id + 1 : 1;

        return $initials . 'B' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }
}
