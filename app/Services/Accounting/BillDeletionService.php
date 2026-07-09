<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\Bill;
use App\Models\JournalEntry;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class BillDeletionService
{
    public function __construct(protected JournalReversalService $journalReversal)
    {
    }

    /**
     * Delete a bill, reversing every account/wallet balance and journal entry
     * its payments posted, and recording who deleted it.
     */
    public function delete(Bill $bill, ?int $deletedById = null, ?string $reason = null): void
    {
        DB::transaction(function () use ($bill, $deletedById, $reason) {

            $bill = Bill::with([
                'bill_expenses',
                'bill_payments',
                'payments.denominations',
                'payments.documents',
                'payments.receipt',
                'payments.cash_flow',
            ])->lockForUpdate()->findOrFail($bill->id);

            $reasonText = $reason ?? "Bill {$bill->bill_number} deleted";

            // -----------------------------
            // 1) Reverse allocations recorded against this bill (direct + drawdown)
            // -----------------------------
            foreach ($bill->bill_payments as $bill_payment) {

                if ($bill_payment->source === 'drawdown' && $bill_payment->payment_id) {
                    // Give the money back to the wallet it was drawn down from
                    $funding_payment = Payment::where('id', $bill_payment->payment_id)->lockForUpdate()->first();
                    if ($funding_payment) {
                        $funding_payment->drawdown_balance = (float) ($funding_payment->drawdown_balance ?? 0) + (float) $bill_payment->amount;
                        $funding_payment->save();
                    }
                }

                $bill_payment->delete();
            }

            // -----------------------------
            // 2) Reverse and remove payments recorded directly against this bill
            // -----------------------------
            foreach ($bill->payments as $payment) {

                if ($payment->account_id) {
                    $account = Account::where('id', $payment->account_id)->lockForUpdate()->first();
                    if ($account) {
                        $account->balance = (float) $account->balance + (float) $payment->amount;
                        $account->save();
                    }
                }

                // Post a reversing journal entry for whatever this payment originally posted
                JournalEntry::where('payment_id', $payment->id)
                    ->where('status', '!=', 'reversed')
                    ->get()
                    ->each(fn ($entry) => $this->journalReversal->reverse($entry, $reasonText));

                $payment->denominations?->each->delete();
                $payment->documents?->each->delete();

                if ($payment->receipt) {
                    $payment->receipt->delete();
                }

                if ($payment->cash_flow) {
                    $payment->cash_flow->delete();
                }

                $payment->deleted_by_id = $deletedById;
                $payment->save();
                $payment->delete();
            }

            // -----------------------------
            // 3) Reverse the bill's own journal entry, remove line items and the bill itself
            // -----------------------------
            JournalEntry::where('bill_id', $bill->id)
                ->where('status', '!=', 'reversed')
                ->get()
                ->each(fn ($entry) => $this->journalReversal->reverse($entry, $reasonText));

            foreach ($bill->bill_expenses as $bill_expense) {
                $bill_expense->delete();
            }

            $bill->deleted_by_id = $deletedById;
            $bill->save();
            $bill->delete();
        });
    }
}
