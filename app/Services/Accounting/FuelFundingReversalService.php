<?php

namespace App\Services\Accounting;

use App\Models\Bill;
use App\Models\BillPayment;
use App\Models\CustomerFuelSupply;
use App\Models\Fuel;
use App\Models\Payment;
use App\Models\SupplierJournal;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Switching a fuel order's Funded By from Company to Customer while its
 * supplier bill is already settled.
 *
 * The customer supply takes the bill over and settles it itself (DR AP
 * vendor / CR AR), so whatever settled it before has to go first or the
 * supplier is paid twice. CustomerFuelSupplyService refuses the switch while
 * such a settlement exists; this service says what is in the way (plan) and,
 * once the user has confirmed, reverses it in full (reverse):
 *
 *  - a Suppliers Journal debit applied to the bill -> the journal is voided
 *    (entry reversed, every bill it was applied to reopens);
 *  - a supplier payment against the bill -> the payment is deleted (ledger
 *    reversed, cash put back on its account, every bill it paid reopens).
 *
 * Anything else (a prepayment drawdown, a bulk catch-up allocation) is left
 * alone and keeps the switch blocked - those are undone from the bill.
 */
class FuelFundingReversalService
{
    public function __construct(
        private SupplierJournalService $supplierJournals,
        private PaymentDeletionService $paymentDeletion
    ) {
    }

    /** Bills the customer-funded path takes over (fuel_id) or removes (legacy trip-expense bill). */
    public function bills(Fuel $fuel): Collection
    {
        $bills = Bill::where('fuel_id', $fuel->id)->get();

        if ($tripExpenseId = optional($fuel->trip_expense)->id) {
            $bills = $bills->merge(Bill::where('trip_expense_id', $tripExpenseId)->whereNull('fuel_id')->get());
        }

        return $bills->unique('id')->values();
    }

    /** Settlements on those bills that aren't this fuel order's own customer supply. */
    public function settlements(Fuel $fuel): Collection
    {
        $billIds = $this->bills($fuel)->pluck('id');
        if ($billIds->isEmpty()) {
            return collect();
        }

        $supplyId = CustomerFuelSupply::where('fuel_id', $fuel->id)->value('id');

        return BillPayment::whereIn('bill_id', $billIds)
            ->when($supplyId, fn ($q) => $q->where(fn ($w) => $w->whereNull('customer_fuel_supply_id')->orWhere('customer_fuel_supply_id', '!=', $supplyId)))
            ->get();
    }

    /**
     * What has to be reversed before the fuel order can become customer
     * funded. ['items' => [[title, action, others[]]...], 'blocked' => ?string].
     * Empty items = nothing in the way.
     */
    public function plan(Fuel $fuel): array
    {
        $settlements = $this->settlements($fuel);
        $ownBillIds = $this->bills($fuel)->pluck('id')->all();
        $items = [];
        $blocked = null;

        foreach ($this->journals($settlements) as $journal) {
            $journal->loadMissing('currency');
            $symbol = optional($journal->currency)->symbol;
            $items[] = [
                'title'  => "Suppliers Journal {$journal->journal_number}"
                    . ($journal->date ? ' dated ' . substr((string) $journal->date, 0, 10) : '')
                    . ' - ' . $symbol . number_format((float) $journal->amount, 2)
                    . ($journal->description ? " ({$journal->description})" : ''),
                'action' => 'The journal will be voided and its ledger entry reversed.',
                'others' => $this->otherBills($journal->bill_payments()->get(), $ownBillIds),
            ];
        }

        foreach ($this->payments($settlements) as $payment) {
            $payment->loadMissing('currency', 'account');
            $symbol = optional($payment->currency)->symbol;
            $items[] = [
                'title'  => 'Payment ' . ($payment->payment_number ?: "#{$payment->id}")
                    . ($payment->date ? ' dated ' . substr((string) $payment->date, 0, 10) : '')
                    . ' - ' . $symbol . number_format((float) $payment->amount, 2),
                'action' => 'The payment will be deleted, its ledger entry reversed and the money put back'
                    . (optional($payment->account)->name ? " on {$payment->account->name}." : ' on its account.'),
                'others' => $this->otherBills($payment->bill_payments()->get(), $ownBillIds),
            ];
        }

        if ($unsupported = $this->unsupported($settlements)->first()) {
            $bill = Bill::find($unsupported->bill_id);
            $blocked = 'Bill ' . optional($bill)->bill_number . ' is also settled by '
                . ($unsupported->source ? str_replace('_', ' ', $unsupported->source) : 'another allocation')
                . ' - that cannot be reversed from here. Undo it from the bill first.';
        }

        return ['items' => $items, 'blocked' => $blocked];
    }

    /** Reverse every settlement in the plan. Throws when something can't be reversed here. */
    public function reverse(Fuel $fuel): void
    {
        $settlements = $this->settlements($fuel);
        if ($settlements->isEmpty()) {
            return;
        }

        $plan = $this->plan($fuel);
        if ($plan['blocked']) {
            throw new \RuntimeException($plan['blocked']);
        }

        $reason = "Fuel order {$fuel->order_number} changed to customer funded";

        DB::transaction(function () use ($settlements, $reason) {
            foreach ($this->journals($settlements) as $journal) {
                // A correction - the reversal stays in the journal's own period.
                $this->supplierJournals->void($journal, $reason, true);
            }

            foreach ($this->payments($settlements) as $payment) {
                $this->paymentDeletion->delete($payment, Auth::id(), "Payment " . ($payment->payment_number ?: "#{$payment->id}") . " deleted - {$reason}");
            }
        });
    }

    private function journals(Collection $settlements): Collection
    {
        $ids = $settlements->where('source', SupplierJournalService::SOURCE)->pluck('supplier_journal_id')->filter()->unique();

        return $ids->isEmpty() ? collect() : SupplierJournal::whereIn('id', $ids)->get();
    }

    private function payments(Collection $settlements): Collection
    {
        $ids = $settlements->filter(fn ($s) => $this->isDirectPayment($s))->pluck('payment_id')->unique();

        return $ids->isEmpty() ? collect() : Payment::whereIn('id', $ids)->get();
    }

    private function unsupported(Collection $settlements): Collection
    {
        return $settlements->filter(fn ($s) => !$this->isDirectPayment($s)
            && !($s->source === SupplierJournalService::SOURCE && $s->supplier_journal_id));
    }

    /** A plain supplier payment (Bills > Pay), not a prepayment drawdown etc. */
    private function isDirectPayment(BillPayment $settlement): bool
    {
        return $settlement->payment_id && in_array($settlement->source, [null, '', 'direct'], true);
    }

    /** "CEB00240 (R500.00)" for every bill the document also settles. */
    private function otherBills(Collection $allocations, array $ownBillIds): array
    {
        return $allocations
            ->reject(fn ($a) => in_array($a->bill_id, $ownBillIds))
            ->map(function ($a) {
                $bill = Bill::with('currency')->find($a->bill_id);

                return $bill
                    ? $bill->bill_number . ' (' . optional($bill->currency)->symbol . number_format((float) $a->amount, 2) . ')'
                    : null;
            })
            ->filter()
            ->values()
            ->all();
    }
}
