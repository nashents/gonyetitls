<?php

namespace App\Services\Accounting;

use App\Models\Bill;
use App\Models\BillPayment;
use App\Models\Payment;
use App\Models\SupplierJournal;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Payables mirror of CustomerDepositService: a vendor's prepayments
 * (payments on account, transaction_category = "Vendor Payments", posted
 * DR Vendor Prepayments / CR Bank) broken down per payment, and their
 * allocation to bills - or to CREDIT suppliers journals (e.g. an opening
 * balance owed to the vendor).
 *
 * payments.drawdown_balance only ever holds the running wallet total on the
 * latest row; per-prepayment availability is derived here instead: amount
 * less every live bill_payments row drawn against it.
 *
 * Every allocation is a bill_payments row with source 'drawdown', which
 * DrawdownJournalService posts DR Accounts Payable / CR Vendor Prepayments.
 */
class VendorPrepaymentService
{
    public const CATEGORY = 'Vendor Payments';

    /** Every prepayment for a vendor + currency, oldest first, each as {payment, allocated, available}. */
    public function prepayments(int $vendorId, int $currencyId, bool $lock = false): Collection
    {
        $payments = Payment::where('vendor_id', $vendorId)
            ->where('currency_id', $currencyId)
            ->where('transaction_category', self::CATEGORY)
            ->orderBy('date')
            ->orderBy('id')
            ->when($lock, fn ($q) => $q->lockForUpdate())
            ->get();

        $allocated = BillPayment::whereIn('payment_id', $payments->pluck('id'))
            ->groupBy('payment_id')
            ->selectRaw('payment_id, SUM(COALESCE(amount+0,0)) as total')
            ->pluck('total', 'payment_id');

        $overdrawn = 0.0;

        $prepayments = $payments->map(function ($payment) use ($allocated, &$overdrawn) {
            $prepayment = new \stdClass;
            $prepayment->payment = $payment;
            $prepayment->allocated = round((float) ($allocated[$payment->id] ?? 0), 2);
            $prepayment->available = round((float) $payment->amount - $prepayment->allocated, 2);

            if ($prepayment->available < 0) {
                $overdrawn = round($overdrawn - $prepayment->available, 2);
                $prepayment->available = 0.0;
            }

            return $prepayment;
        });

        // The old drawdown always charged the LATEST prepayment, even when the
        // money came from older ones - take that excess off them, oldest first.
        foreach ($prepayments as $prepayment) {
            if ($overdrawn <= 0) {
                break;
            }

            $taken = min($prepayment->available, $overdrawn);
            $prepayment->available = round($prepayment->available - $taken, 2);
            $overdrawn = round($overdrawn - $taken, 2);
        }

        return $prepayments;
    }

    /**
     * Applies prepayments exactly as paired by the user. A line's bill_id is
     * a bill id, or "j<id>" for a posted CREDIT suppliers journal for the
     * same vendor + currency (stored with bill_id null and
     * settled_supplier_journal_id set). The whole batch is rejected if any
     * line is invalid or anything is over-allocated.
     *
     * @param  array<int, array{payment_id: mixed, bill_id: mixed, amount: mixed}>  $lines
     * @return array{applied: float, documents: int}
     */
    public function allocate(int $vendorId, int $currencyId, array $lines): array
    {
        return DB::transaction(function () use ($vendorId, $currencyId, $lines) {

            $funds = $this->prepayments($vendorId, $currencyId, lock: true)
                ->keyBy(fn ($prepayment) => (int) $prepayment->payment->id);

            $journalIds = collect($lines)->map(fn ($line) => self::journalTarget($line['bill_id'] ?? null))->filter()->unique();
            $journals = SupplierJournal::whereIn('id', $journalIds)
                ->where('vendor_id', $vendorId)
                ->where('currency_id', $currencyId)
                ->where('type', SupplierJournal::CREDIT)
                ->posted()
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            $journalDue = $journals->map(fn ($journal) => $journal->outstandingAmount());

            $bills = Bill::whereIn('id', collect($lines)->pluck('bill_id')->filter(fn ($id) => is_numeric($id))->unique())
                ->where('vendor_id', $vendorId)
                ->where('currency_id', $currencyId)
                ->where('authorization', 'approved')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $available = $funds->map(fn ($prepayment) => $prepayment->available);
            $due = $bills->map(fn ($bill) => round((float) $bill->balance, 2));
            $applied = 0.0;

            foreach ($lines as $line) {
                $fund = $funds->get((int) ($line['payment_id'] ?? 0));
                $journalId = self::journalTarget($line['bill_id'] ?? null);
                $journal = $journalId ? $journals->get($journalId) : null;
                $bill = $journalId ? null : $bills->get((int) ($line['bill_id'] ?? 0));
                $amount = is_numeric($line['amount'] ?? null) ? round((float) $line['amount'], 2) : 0.0;

                if (! $fund || (! $bill && ! $journal) || $amount <= 0) {
                    throw new \RuntimeException('Each line needs a payment, a bill or journal, and an amount greater than zero.');
                }

                if ($amount > $fund->available + 0.005) {
                    throw new \RuntimeException("Payment {$fund->payment->payment_number} only has " . number_format($available[$fund->payment->id], 2) . ' available - the lines against it add up to more than that.');
                }

                $allocation = new BillPayment;
                $allocation->vendor_id = $vendorId;
                $allocation->payment_id = $fund->payment->id;
                $allocation->source = 'drawdown';
                $allocation->amount = $amount;

                if ($journal) {
                    if ($amount > $journalDue[$journal->id] + 0.005) {
                        throw new \RuntimeException("Journal {$journal->journal_number} only has " . number_format($journal->outstandingAmount(), 2) . ' outstanding - the lines against it add up to more than that.');
                    }

                    $allocation->settled_supplier_journal_id = $journal->id;
                    $allocation->currency_id = $journal->currency_id;
                    $journalDue[$journal->id] = max(round($journalDue[$journal->id] - $amount, 2), 0);
                } else {
                    if ($amount > $due[$bill->id] + 0.005) {
                        throw new \RuntimeException("Bill {$bill->bill_number} only has " . number_format((float) $bill->balance, 2) . ' outstanding - the lines against it add up to more than that.');
                    }

                    $allocation->bill_id = $bill->id;
                    $allocation->currency_id = $bill->currency_id;
                    $due[$bill->id] = max(round($due[$bill->id] - $amount, 2), 0);
                }

                $allocation->save();

                $fund->available = round($fund->available - $amount, 2);
                $applied = round($applied + $amount, 2);
            }

            if ($applied <= 0) {
                throw new \RuntimeException('Nothing to allocate - add at least one line.');
            }

            foreach ($bills as $bill) {
                $bill->balance = $due[$bill->id];
                $bill->status = $due[$bill->id] <= 0 ? 'Paid' : 'Partial';
                $bill->save();
            }

            $this->syncWalletBalance($vendorId, $currencyId);

            return ['applied' => $applied, 'documents' => $bills->count() + $journals->count()];
        });
    }

    /** Posted credit suppliers journals (opening balances etc.) for a vendor + currency still owed. Each gets ->outstanding. */
    public function openCreditJournals(int $vendorId, int $currencyId): Collection
    {
        return SupplierJournal::where('vendor_id', $vendorId)
            ->where('currency_id', $currencyId)
            ->where('type', SupplierJournal::CREDIT)
            ->posted()
            ->orderBy('date')
            ->orderBy('id')
            ->get()
            ->each(fn ($journal) => $journal->outstanding = $journal->outstandingAmount())
            ->filter(fn ($journal) => $journal->outstanding > 0)
            ->values();
    }

    /** Rewrites the running wallet total on the latest prepayment row (payments.drawdown_balance). */
    public function syncWalletBalance(int $vendorId, int $currencyId): void
    {
        $prepayments = $this->prepayments($vendorId, $currencyId, lock: true);
        $latest = $prepayments->sortByDesc(fn ($prepayment) => $prepayment->payment->id)->first();

        if (! $latest) {
            return;
        }

        $latest->payment->drawdown_balance = round($prepayments->sum('available'), 2);
        $latest->payment->save();
    }

    /** "j12" (a line target that is a suppliers journal) → 12; a bill id → null. */
    public static function journalTarget($target): ?int
    {
        return is_string($target) && preg_match('/^j(\d+)$/', $target, $m) ? (int) $m[1] : null;
    }
}
