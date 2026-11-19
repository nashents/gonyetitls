<?php

namespace App\Services\Accounting;

use App\Models\DebtorJournal;
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
     * Applies deposits to invoices exactly as paired by the user - each
     * line says which deposit pays which invoice and by how much, so one
     * deposit can be split across invoices and several deposits can go to
     * one invoice. Nothing is matched automatically. The whole batch is
     * rejected if any line is invalid or a deposit/invoice is over-allocated.
     *
     * A line's invoice_id may instead be "j<id>": a posted DEBIT debtors
     * journal (e.g. an opening balance) for the same customer + currency.
     * That allocation is an invoice_payments row with invoice_id null and
     * settled_debtor_journal_id set; it posts DR Customer Deposits / CR AR
     * like any drawdown (DrawdownJournalService).
     *
     * @param  array<int, array{payment_id: mixed, invoice_id: mixed, amount: mixed}>  $lines
     * @return array{applied: float, invoices: int}
     */
    public function allocate(int $customerId, int $currencyId, array $lines): array
    {
        return DB::transaction(function () use ($customerId, $currencyId, $lines) {

            $funds = $this->deposits($customerId, $currencyId, lock: true)
                ->keyBy(fn ($deposit) => (int) $deposit->payment->id);

            $journalIds = collect($lines)->map(fn ($line) => self::journalTarget($line['invoice_id'] ?? null))->filter()->unique();
            $journals = DebtorJournal::whereIn('id', $journalIds)
                ->where('customer_id', $customerId)
                ->where('currency_id', $currencyId)
                ->where('type', DebtorJournal::DEBIT)
                ->posted()
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            $journalDue = $journals->map(fn ($journal) => $journal->outstandingAmount());

            $invoices = Invoice::whereIn('id', collect($lines)->pluck('invoice_id')->filter(fn ($id) => is_numeric($id))->unique())
                ->where('customer_id', $customerId)
                ->where('currency_id', $currencyId)
                ->where('authorization', 'approved')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // Running figures as lines are applied; $available keeps what each
            // deposit started with, for the error message
            $available = $funds->map(fn ($deposit) => $deposit->available);
            $due = $invoices->map(fn ($invoice) => round((float) $invoice->balance, 2));
            $applied = 0.0;

            foreach ($lines as $line) {
                $fund = $funds->get((int) ($line['payment_id'] ?? 0));
                $journalId = self::journalTarget($line['invoice_id'] ?? null);
                $journal = $journalId ? $journals->get($journalId) : null;
                $invoice = $journalId ? null : $invoices->get((int) ($line['invoice_id'] ?? 0));
                $amount = is_numeric($line['amount'] ?? null) ? round((float) $line['amount'], 2) : 0.0;

                if (! $fund || (! $invoice && ! $journal) || $amount <= 0) {
                    throw new \RuntimeException('Each line needs a payment, an invoice or journal, and an amount greater than zero.');
                }

                if ($amount > $fund->available + 0.005) {
                    throw new \RuntimeException("Payment {$fund->payment->payment_number} only has " . number_format($available[$fund->payment->id], 2) . ' available - the lines against it add up to more than that.');
                }

                if ($journal) {
                    if ($amount > $journalDue[$journal->id] + 0.005) {
                        throw new \RuntimeException("Journal {$journal->journal_number} only has " . number_format($journal->outstandingAmount(), 2) . ' outstanding - the lines against it add up to more than that.');
                    }

                    $settlement = new InvoicePayment;
                    $settlement->customer_id = $journal->customer_id;
                    $settlement->settled_debtor_journal_id = $journal->id;
                    $settlement->payment_id = $fund->payment->id;
                    $settlement->source = 'drawdown';
                    $settlement->currency_id = $journal->currency_id;
                    $settlement->amount = $amount;
                    $settlement->save();

                    $fund->available = round($fund->available - $amount, 2);
                    $journalDue[$journal->id] = max(round($journalDue[$journal->id] - $amount, 2), 0);
                    $applied = round($applied + $amount, 2);
                    continue;
                }

                if ($amount > $due[$invoice->id] + 0.005) {
                    throw new \RuntimeException("Invoice {$invoice->invoice_number} only has " . number_format((float) $invoice->balance, 2) . ' outstanding - the lines against it add up to more than that.');
                }

                $invoice_payment = new InvoicePayment;
                $invoice_payment->customer_id = $invoice->customer_id;
                $invoice_payment->invoice_id = $invoice->id;
                $invoice_payment->payment_id = $fund->payment->id;
                $invoice_payment->source = 'drawdown';
                $invoice_payment->currency_id = $invoice->currency_id;
                $invoice_payment->amount = $amount;
                $invoice_payment->save();

                $fund->available = round($fund->available - $amount, 2);
                $due[$invoice->id] = max(round($due[$invoice->id] - $amount, 2), 0);
                $applied = round($applied + $amount, 2);
            }

            if ($applied <= 0) {
                throw new \RuntimeException('Nothing to allocate - add at least one line.');
            }

            foreach ($invoices as $invoice) {
                $invoice->balance = $due[$invoice->id];
                $invoice->status = $due[$invoice->id] <= 0 ? 'Paid' : 'Partial';
                $invoice->save();
            }

            $this->syncWalletBalance($customerId, $currencyId);

            return ['applied' => $applied, 'invoices' => $invoices->count() + $journals->count()];
        });
    }

    /**
     * Posted debit debtors journals (e.g. opening balances) for a customer +
     * currency that still have an amount outstanding - matchable to
     * deposits like invoices. Each gets ->outstanding.
     */
    public function openDebitJournals(int $customerId, int $currencyId): Collection
    {
        return DebtorJournal::where('customer_id', $customerId)
            ->where('currency_id', $currencyId)
            ->where('type', DebtorJournal::DEBIT)
            ->posted()
            ->orderBy('date')
            ->orderBy('id')
            ->get()
            ->each(fn ($journal) => $journal->outstanding = $journal->outstandingAmount())
            ->filter(fn ($journal) => $journal->outstanding > 0)
            ->values();
    }

    /** "j12" (a line target that is a debtors journal) → 12; an invoice id → null. */
    public static function journalTarget($target): ?int
    {
        return is_string($target) && preg_match('/^j(\d+)$/', $target, $m) ? (int) $m[1] : null;
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
