<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\Bill;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PaymentRestorationService
{
    public function __construct(protected JournalReversalService $journalReversal)
    {
    }

    /**
     * Restore a soft-deleted payment, reapplying every account/wallet balance
     * change and journal entry that deleting it reversed. This is the exact
     * inverse of Payments\Index::destroy().
     *
     * $adjustParentBalance must be false when the payment's own invoice/bill
     * is being restored in the same operation (BillRestorationService /
     * InvoiceRestorationService already leave that balance untouched here,
     * mirroring how BillDeletionService/InvoiceDeletionService never touch it
     * either when the whole parent is being removed alongside the payment).
     * When true (a payment restored on its own from the Deleted Payments
     * list), it also refuses to run if the payment's invoice/bill is still
     * trashed - that would mean it was deleted together with that parent, so
     * the parent must be restored first to avoid a dangling allocation.
     */
    public function restore(Payment $payment, bool $adjustParentBalance = true, ?string $reason = null): void
    {
        DB::transaction(function () use ($payment, $adjustParentBalance, $reason) {

            $payment = Payment::withTrashed()->with([
                'invoice',
                'bill',
                'account',
                'invoice_payments' => fn ($q) => $q->withTrashed(),
                'invoice_payments.invoice',
                'bill_payments' => fn ($q) => $q->withTrashed(),
                'bill_payments.bill',
                'denominations' => fn ($q) => $q->withTrashed(),
                'documents' => fn ($q) => $q->withTrashed(),
                'receipt' => fn ($q) => $q->withTrashed(),
                'cash_flow' => fn ($q) => $q->withTrashed(),
            ])->lockForUpdate()->findOrFail($payment->id);

            if ($adjustParentBalance) {
                if ($payment->invoice_id) {
                    $invoice = Invoice::withTrashed()->find($payment->invoice_id);
                    if ($invoice && $invoice->trashed()) {
                        throw new \RuntimeException("Restore Invoice {$invoice->invoice_number} first - this payment was deleted together with it.");
                    }
                }
                if ($payment->bill_id) {
                    $bill = Bill::withTrashed()->find($payment->bill_id);
                    if ($bill && $bill->trashed()) {
                        throw new \RuntimeException("Restore Bill {$bill->bill_number} first - this payment was deleted together with it.");
                    }
                }
            }

            $reasonText = $reason ?? "Payment {$payment->payment_number} restored";

            // -----------------------------
            // 1) Reinstate allocations to invoices (preferred)
            // -----------------------------
            $appliedTotal = 0.0;

            if ($payment->invoice_payments && $payment->invoice_payments->count() > 0) {

                foreach ($payment->invoice_payments as $invoice_payment) {
                    $inv = $invoice_payment->invoice;
                    if (! $inv || $inv->trashed()) {
                        continue;
                    }

                    $applied = (float) ($invoice_payment->amount ?? 0);
                    $appliedTotal += $applied;

                    $lockedInvoice = Invoice::where('id', $inv->id)->lockForUpdate()->first();
                    if ($lockedInvoice) {
                        $lockedInvoice->balance = (float) $lockedInvoice->balance - $applied;
                        $lockedInvoice->status  = $this->computeStatus($lockedInvoice->balance, $lockedInvoice->total);
                        $lockedInvoice->save();
                    }

                    $invoice_payment->restore();
                }

            } elseif ($adjustParentBalance) {
                $invoice = $payment->invoice;

                if ($invoice) {
                    $lockedInvoice = Invoice::where('id', $invoice->id)->lockForUpdate()->first();
                    if ($lockedInvoice) {
                        $lockedInvoice->balance = (float) $lockedInvoice->balance - (float) $payment->amount;
                        $lockedInvoice->status  = $this->computeStatus($lockedInvoice->balance, $lockedInvoice->total);
                        $lockedInvoice->save();
                    }
                }
            }

            // -----------------------------
            // 1b) Reinstate allocations to bills (mirrors invoice allocations above)
            // -----------------------------
            if ($payment->bill_payments && $payment->bill_payments->count() > 0) {

                foreach ($payment->bill_payments as $bill_payment) {
                    $bl = $bill_payment->bill;
                    if (! $bl || $bl->trashed()) {
                        continue;
                    }

                    $applied = (float) ($bill_payment->amount ?? 0);

                    $lockedBill = Bill::where('id', $bl->id)->lockForUpdate()->first();
                    if ($lockedBill) {
                        $lockedBill->balance = (float) $lockedBill->balance - $applied;
                        $lockedBill->status  = $this->computeStatus($lockedBill->balance, $lockedBill->total);
                        $lockedBill->save();
                    }

                    $bill_payment->restore();
                }

            } elseif ($adjustParentBalance) {
                $bill = $payment->bill;

                if ($bill) {
                    $lockedBill = Bill::where('id', $bill->id)->lockForUpdate()->first();
                    if ($lockedBill) {
                        $lockedBill->balance = (float) $lockedBill->balance - (float) $payment->amount;
                        $lockedBill->status  = $this->computeStatus($lockedBill->balance, $lockedBill->total);
                        $lockedBill->save();
                    }
                }
            }

            // -----------------------------
            // 2) Reinstate bank/cash account movement (category-aware, inverse of destroy())
            // -----------------------------
            if ($payment->account) {
                $lockedAccount = Account::where('id', $payment->account->id)->lockForUpdate()->first();

                if ($lockedAccount) {
                    $category = strtolower((string) $payment->category);

                    if ($category === 'customer') {
                        $lockedAccount->balance = (float) $lockedAccount->balance + (float) $payment->amount;
                    } elseif (in_array($category, ['vendor', 'bill'], true)) {
                        $lockedAccount->balance = (float) $lockedAccount->balance - (float) $payment->amount;
                    } else {
                        $lockedAccount->balance = (float) $lockedAccount->balance + (float) $payment->amount;
                    }

                    $lockedAccount->save();
                }
            }

            // -----------------------------
            // 3) Reinstate CUSTOMER wallet (drawdown_balance chain) - best-effort
            // inverse of destroy()'s wallet math. $payment's own drawdown_balance
            // column is never touched by delete/restore; only whichever row is
            // currently acting as the "latest deposit" holder is corrected.
            // -----------------------------
            if ($payment->customer_id && $payment->transaction_category === 'Customer Deposits') {

                $walletHolder = Payment::where('customer_id', $payment->customer_id)
                    ->where('currency_id', $payment->currency_id)
                    ->where('transaction_category', 'Customer Deposits')
                    ->where('id', '!=', $payment->id)
                    ->orderByDesc('id')
                    ->lockForUpdate()
                    ->first();

                if ($walletHolder) {
                    $restored = (float) $walletHolder->drawdown_balance - $appliedTotal + (float) $payment->amount;
                    $walletHolder->drawdown_balance = max($restored, 0);
                    $walletHolder->save();
                }
            }

            // -----------------------------
            // 4) Restore children
            // -----------------------------
            $payment->denominations?->each->restore();
            $payment->documents?->each->restore();

            if ($payment->receipt) {
                $payment->receipt->restore();
            }

            if ($payment->cash_flow) {
                $payment->cash_flow->restore();
            }

            // -----------------------------
            // 5) Reinstate the associated journal entry
            // -----------------------------
            $journalEntry = JournalEntry::where('payment_id', $payment->id)
                ->where('status', 'reversed')
                ->first();

            if ($journalEntry) {
                $this->journalReversal->unreverse($journalEntry, $reasonText);
            }

            // -----------------------------
            // 6) Restore payment
            // -----------------------------
            $payment->deleted_by_id = null;
            $payment->save();
            $payment->restore();
        });
    }

    /**
     * Mirrors Payments\Index::computeInvoiceStatus() so restored balances
     * land on the same status label a fresh payment/reversal would produce.
     */
    protected function computeStatus($balance, $total): string
    {
        $balance = (float) $balance;
        $total   = (float) $total;
        $eps     = 0.00001;

        if (abs($balance - $total) < $eps) return 'Unpaid';
        if ($balance > $eps && $balance < ($total - $eps)) return 'Partial';
        if ($balance <= $eps) return 'Paid';

        return 'Unpaid';
    }
}
