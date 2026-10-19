<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\Bill;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

/**
 * Deleting a payment undoes everything it did: its allocations to invoices
 * and bills (their balances/status restored, trip line splits taken back),
 * the cash/bank account balance it moved (both legs of a transfer), the
 * customer deposit wallet, its receipt/denominations/documents/cash-flow
 * rows and its ledger posting.
 *
 * Runs from PaymentObserver::deleted() too, so a payment removed by ANY path
 * (credit note / loan / recovery / sale deletion, cash-flow screen, a bill
 * or invoice being deleted) leaves nothing hanging - those used to leave
 * invoices showing as paid by a payment that no longer existed. The
 * per-request $done guard makes the account-balance step run exactly once
 * when a caller deletes through delete() and the observer then fires.
 */
class PaymentDeletionService
{
    /** @var array<int, true> payments already cascaded in this request */
    private static array $done = [];

    public function __construct(
        private JournalReversalService $journalReversal,
        private CustomerDepositService $customerDeposits
    ) {
    }

    public function delete(Payment $payment, ?int $deletedById = null, ?string $reason = null): void
    {
        DB::transaction(function () use ($payment, $deletedById, $reason) {
            $payment = Payment::lockForUpdate()->findOrFail($payment->id);

            $this->cascade($payment, $reason ?? "Payment {$payment->payment_number} deleted", $deletedById);

            if ($deletedById) {
                $payment->deleted_by_id = $deletedById;
                $payment->save();
            }
            $payment->delete();
        });
    }

    public function cascade(Payment $payment, string $reasonText, ?int $deletedById = null): void
    {
        if (isset(self::$done[$payment->id])) {
            return;
        }
        self::$done[$payment->id] = true;

        DB::transaction(function () use ($payment, $reasonText) {

            // 1) Allocations to invoices (incl. deposit drawdowns funded by this payment)
            $allocations = $payment->invoice_payments()->get();
            foreach ($allocations as $allocation) {
                if ($invoice = Invoice::whereKey($allocation->invoice_id)->lockForUpdate()->first()) {
                    $invoice->balance = (float) $invoice->balance + (float) $allocation->amount;
                    $invoice->status = $this->status($invoice->balance, $invoice->total);
                    $invoice->save();
                }
                app(InvoicePaymentLineService::class)->reverse($allocation);
                $allocation->delete();
            }
            if ($allocations->isEmpty() && $payment->invoice_id
                && ($invoice = Invoice::whereKey($payment->invoice_id)->lockForUpdate()->first())) {
                // older single-invoice payments with no allocation row
                $invoice->balance = (float) $invoice->balance + (float) $payment->amount;
                $invoice->status = $this->status($invoice->balance, $invoice->total);
                $invoice->save();
            }

            // 1b) Allocations to bills
            $billAllocations = $payment->bill_payments()->get();
            foreach ($billAllocations as $allocation) {
                if ($bill = Bill::whereKey($allocation->bill_id)->lockForUpdate()->first()) {
                    $bill->balance = (float) $bill->balance + (float) $allocation->amount;
                    $bill->status = $this->status($bill->balance, $bill->total);
                    $bill->save();
                }
                $allocation->delete();
            }
            if ($billAllocations->isEmpty() && $payment->bill_id
                && ($bill = Bill::whereKey($payment->bill_id)->lockForUpdate()->first())) {
                $bill->balance = (float) $bill->balance + (float) $payment->amount;
                $bill->status = $this->status($bill->balance, $bill->total);
                $bill->save();
            }

            // 2) Cash/bank account balance it moved (money in -> take out, money out -> put back)
            if ($payment->account_id && ($account = Account::whereKey($payment->account_id)->lockForUpdate()->first())) {
                $account->balance = $this->moneyIn($payment)
                    ? (float) $account->balance - $payment->cashAmount()
                    : (float) $account->balance + (float) $payment->amount;
                $account->save();
            }
            // ...and the destination leg of a transfer
            if ($payment->category === 'transfer' && $payment->to_account_id
                && ($toAccount = Account::whereKey($payment->to_account_id)->lockForUpdate()->first())) {
                $toAccount->balance = (float) $toAccount->balance - (float) $payment->to_amount;
                $toAccount->save();
            }

            // 3) Child rows
            $payment->denominations()->get()->each->delete();
            $payment->documents()->get()->each->delete();
            $payment->receipt()->first()?->delete();
            $payment->cash_flow()->first()?->delete();

            // 4) Its ledger posting (live effects only)
            $this->journalReversal->reverseLiveEffects(JournalEntry::where('payment_id', $payment->id), $reasonText);
        });
    }

    /**
     * Customer deposit wallet: recomputed from the per-deposit figures once
     * the payment is gone (PaymentObserver::deleted calls this after delete).
     */
    public function syncWallet(Payment $payment): void
    {
        if ($payment->customer_id && $payment->currency_id && $payment->transaction_category === CustomerDepositService::CATEGORY) {
            $this->customerDeposits->syncWalletBalance((int) $payment->customer_id, (int) $payment->currency_id);
        }
    }

    private function moneyIn(Payment $payment): bool
    {
        if ($payment->movement === 'Crt') {
            return true;
        }
        if ($payment->movement === 'Dbt') {
            return false;
        }

        return ! in_array(strtolower((string) $payment->category), ['vendor', 'bill', 'withdrawal', 'transfer'], true);
    }

    private function status($balance, $total): string
    {
        $balance = (float) $balance;
        $total = (float) $total;
        $eps = 0.00001;

        if (abs($balance - $total) < $eps) return 'Unpaid';
        if ($balance > $eps && $balance < ($total - $eps)) return 'Partial';
        if ($balance <= $eps) return 'Paid';

        return 'Unpaid';
    }
}
