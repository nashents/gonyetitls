<?php

namespace App\Services\Accounting;

use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Payment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * A customer's deposits (payments on account, transaction_category =
 * "Customer Deposits") broken down per payment, and their allocation to
 * invoices.
 *
 * payments.drawdown_balance only ever holds the running wallet total, and
 * only on the latest deposit row - it can't say how much of any one deposit
 * is left. That is derived here instead: a deposit's amount less every live
 * invoice_payments row drawn against it.
 */
class CustomerDepositService
{
    public const CATEGORY = 'Customer Deposits';

    /**
     * Every deposit for a customer + currency, oldest first, each as
     * {payment, allocated, available}.
     */
    public function deposits(int $customerId, int $currencyId, bool $lock = false): Collection
    {
        $payments = Payment::where('customer_id', $customerId)
            ->where('currency_id', $currencyId)
            ->where('transaction_category', self::CATEGORY)
            ->orderBy('date')
            ->orderBy('id')
            ->when($lock, fn ($q) => $q->lockForUpdate())
            ->get();

        $allocated = InvoicePayment::whereIn('payment_id', $payments->pluck('id'))
            ->groupBy('payment_id')
            ->selectRaw('payment_id, SUM(COALESCE(amount+0,0)) as total')
            ->pluck('total', 'payment_id');

        $overdrawn = 0.0;

        $deposits = $payments->map(function ($payment) use ($allocated, &$overdrawn) {
            $deposit = new \stdClass;
            $deposit->payment = $payment;
            $deposit->allocated = round((float) ($allocated[$payment->id] ?? 0), 2);
            $deposit->available = round((float) $payment->amount - $deposit->allocated, 2);

            if ($deposit->available < 0) {
                $overdrawn = round($overdrawn - $deposit->available, 2);
                $deposit->available = 0.0;
            }

            return $deposit;
        });

        // Drawdowns used to be recorded against whichever deposit was latest
        // at the time, even when the money came from older ones - so a
        // deposit can carry more allocations than its own amount. That
        // excess was really funded by the older deposits; take it off them,
        // oldest first.
        foreach ($deposits as $deposit) {
            if ($overdrawn <= 0) {
                break;
            }

            $taken = min($deposit->available, $overdrawn);
            $deposit->available = round($deposit->available - $taken, 2);
            $overdrawn = round($overdrawn - $taken, 2);
        }

        return $deposits;
    }

    /**
     * Applies the chosen deposits to the chosen invoices - oldest deposit
     * and oldest invoice first - until either the deposits or the invoice
     * balances run out. Each invoice_payments row is recorded against the
     * deposit that actually funded it.
     *
     * @return array{applied: float, invoices: int}
     */
    public function allocate(int $customerId, int $currencyId, array $paymentIds, array $invoiceIds): array
    {
        return DB::transaction(function () use ($customerId, $currencyId, $paymentIds, $invoiceIds) {

            $paymentIds = array_map('intval', $paymentIds);

            $funds = $this->deposits($customerId, $currencyId, lock: true)
                ->filter(fn ($deposit) => in_array((int) $deposit->payment->id, $paymentIds, true) && $deposit->available > 0)
                ->values();

            $invoices = Invoice::whereIn('id', $invoiceIds)
                ->where('customer_id', $customerId)
                ->where('currency_id', $currencyId)
                ->where('authorization', 'approved')
                ->orderBy('date')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $applied = 0.0;
            $settled = 0;

            foreach ($invoices as $invoice) {
                $due = round((float) $invoice->balance, 2);
                $paid = 0.0;

                foreach ($funds as $fund) {
                    if ($due <= 0) {
                        break;
                    }
                    if ($fund->available <= 0) {
                        continue;
                    }

                    $amount = round(min($fund->available, $due), 2);

                    $invoice_payment = new InvoicePayment;
                    $invoice_payment->customer_id = $invoice->customer_id;
                    $invoice_payment->invoice_id = $invoice->id;
                    $invoice_payment->payment_id = $fund->payment->id;
                    $invoice_payment->source = 'drawdown';
                    $invoice_payment->currency_id = $invoice->currency_id;
                    $invoice_payment->amount = $amount;
                    $invoice_payment->save();

                    $fund->available = round($fund->available - $amount, 2);
                    $due = round($due - $amount, 2);
                    $paid = round($paid + $amount, 2);
                }

                if ($paid <= 0) {
                    continue;
                }

                $invoice->balance = $due;
                $invoice->status = $due <= 0 ? 'Paid' : 'Partial';
                $invoice->save();

                $applied = round($applied + $paid, 2);
                $settled++;
            }

            if ($applied <= 0) {
                throw new \RuntimeException('Nothing to allocate - select at least one payment with funds available and one invoice with a balance.');
            }

            $this->syncWalletBalance($customerId, $currencyId);

            return ['applied' => $applied, 'invoices' => $settled];
        });
    }

    /**
     * Rewrites the running wallet total held on the latest deposit row
     * (payments.drawdown_balance) from the per-deposit figures, so the
     * flows that still read it - topping the wallet up, deleting or
     * restoring a deposit - carry on from the right number.
     */
    public function syncWalletBalance(int $customerId, int $currencyId): void
    {
        $deposits = $this->deposits($customerId, $currencyId, lock: true);
        $latest = $deposits->sortByDesc(fn ($deposit) => $deposit->payment->id)->first();

        if (! $latest) {
            return;
        }

        $latest->payment->drawdown_balance = round($deposits->sum('available'), 2);
        $latest->payment->save();
    }
}
