<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\Bill;
use App\Models\BillPayment;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\SupplierJournal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Suppliers journal - manual adjustments to a single vendor's account. The
 * payables-side mirror of DebtorJournalService.
 *
 *  - credit: DR contra account                CR Accounts Payable (vendor)
 *  - debit:  DR Accounts Payable (vendor)     CR contra account
 *
 * A debit can be allocated to the vendor's open bills through bill_payments
 * (source = 'supplier_journal'), reducing the bill balance the same way a
 * payment does. Unallocated debits and credit journals still show on the
 * vendor statement and in aged payables.
 *
 * Posted journals aren't edited - they're voided (journal reversed,
 * allocations released) and re-captured, so the audit trail stays intact.
 * It isn't a Payment row: no cash moved.
 */
class SupplierJournalService
{
    public const SOURCE = 'supplier_journal';

    public function __construct(private JournalReversalService $journalReversal)
    {
    }

    /**
     * Create and post a suppliers journal. $allocations is an optional
     * [bill_id => amount] map applied straight away (debits only).
     */
    public function create(array $attributes, array $allocations = []): SupplierJournal
    {
        return DB::transaction(function () use ($attributes, $allocations) {
            $journal = new SupplierJournal($attributes);
            $journal->amount = round((float) $journal->amount, 2);
            $journal->exchange_rate = $this->rate($journal->exchange_rate);
            $journal->status = 'posted';
            $journal->user_id = Auth::id();
            $journal->company_id = $this->companyId();
            $journal->journal_number = $this->number();
            $journal->save();

            $this->post($journal);

            if ($journal->isDebit()) {
                foreach ($allocations as $billId => $amount) {
                    if ((float) $amount > 0 && ($bill = Bill::find($billId))) {
                        $this->allocate($journal, $bill, (float) $amount);
                    }
                }
            }

            return $journal->fresh();
        });
    }

    public function post(SupplierJournal $journal): JournalEntry
    {
        if ($existing = $this->activeEntry($journal)) {
            return $existing;
        }

        $journal->loadMissing('vendor', 'account');

        $apAccount = Account::where('name', 'Accounts Payable')->firstOrFail();
        $contra = $journal->account ?? Account::findOrFail($journal->account_id);
        $amount = round((float) $journal->amount, 2);
        $rate = $this->rate($journal->exchange_rate);
        $vendorName = optional($journal->vendor)->name;
        $isDebit = $journal->isDebit();
        $narration = $journal->description ?: ($isDebit ? 'Suppliers journal debit' : 'Suppliers journal credit');

        return DB::transaction(function () use ($journal, $apAccount, $contra, $amount, $rate, $vendorName, $isDebit, $narration) {
            $entry = JournalEntry::create([
                'company_id'          => $journal->company_id ?? $this->companyId(),
                'supplier_journal_id' => $journal->id,
                'journal_number'      => $this->journalNumber(),
                'date'                => $journal->date,
                'reference'           => $journal->journal_number,
                'description'         => "Suppliers journal {$journal->journal_number} - {$vendorName} - {$narration}",
                'is_manual'           => false,
                'status'              => 'posted',
                'created_by_id'       => Auth::id(),
                'posted_by_id'        => Auth::id(),
                'posted_at'           => now(),
            ]);

            // Accounts Payable - tagged to the vendor
            $entry->journal_entry_lines()->create([
                'account_id'      => $apAccount->id,
                'vendor_id'       => $journal->vendor_id,
                'debit'           => $isDebit ? $amount : 0,
                'credit'          => $isDebit ? 0 : $amount,
                'exchange_debit'  => $isDebit ? $amount * $rate : 0,
                'exchange_credit' => $isDebit ? 0 : $amount * $rate,
                'currency_id'     => $journal->currency_id,
                'exchange_rate'   => $rate,
                'description'     => "AP - {$vendorName} - {$journal->journal_number} - {$narration}",
            ]);

            // Contra account
            $entry->journal_entry_lines()->create([
                'account_id'      => $contra->id,
                'vendor_id'       => $journal->vendor_id,
                'debit'           => $isDebit ? 0 : $amount,
                'credit'          => $isDebit ? $amount : 0,
                'exchange_debit'  => $isDebit ? 0 : $amount * $rate,
                'exchange_credit' => $isDebit ? $amount * $rate : 0,
                'currency_id'     => $journal->currency_id,
                'exchange_rate'   => $rate,
                'description'     => "{$contra->name} - {$vendorName} - {$journal->journal_number} - {$narration}",
            ]);

            $entry->assertBalanced();

            return $entry;
        });
    }

    /**
     * Reverse the journal entry, release any bill allocations back onto the
     * bills' balances, and mark the journal voided (kept for audit).
     */
    public function void(SupplierJournal $journal, ?string $reason = null): void
    {
        if ($journal->isVoided()) {
            return;
        }

        DB::transaction(function () use ($journal, $reason) {
            foreach ($journal->bill_payments()->get() as $allocation) {
                $this->deallocate($allocation);
            }

            $reason = $reason ?: 'Voided';

            JournalEntry::where('supplier_journal_id', $journal->id)
                ->get()
                ->filter(fn ($entry) => $this->journalReversal->isLiveEffect($entry))
                ->each(fn ($entry) => $this->journalReversal->reverse($entry, "Suppliers journal {$journal->journal_number} voided - {$reason}"));

            $journal->status = 'voided';
            $journal->voided_by_id = Auth::id();
            $journal->voided_at = now();
            $journal->void_reason = $reason;
            $journal->save();
        });
    }

    private function activeEntry(SupplierJournal $journal): ?JournalEntry
    {
        return JournalEntry::where('supplier_journal_id', $journal->id)
            ->whereHas('journal_entry_lines')
            ->orderByDesc('id')
            ->get()
            ->first(fn ($entry) => $this->journalReversal->isLiveEffect($entry));
    }

    // ── Allocation to bills ──────────────────────────────────────────────────

    /**
     * Apply (part of) a debit journal against a bill's open balance.
     * Returns the allocation, or null when there's nothing to apply.
     */
    public function allocate(SupplierJournal $journal, Bill $bill, ?float $amount = null): ?BillPayment
    {
        if (!$journal->isDebit() || $journal->isVoided()) {
            throw new \RuntimeException("Only posted debit journals can be allocated to bills.");
        }

        if ((int) $bill->vendor_id !== (int) $journal->vendor_id || (int) $bill->currency_id !== (int) $journal->currency_id) {
            throw new \RuntimeException("Suppliers journal {$journal->journal_number} can only be applied to the same supplier's bills in the same currency.");
        }

        if ($bill->authorization !== 'approved') {
            throw new \RuntimeException("Bill {$bill->bill_number} isn't approved.");
        }

        return DB::transaction(function () use ($journal, $bill, $amount) {
            $bill = Bill::lockForUpdate()->find($bill->id);
            $billBalance = round((float) $bill->balance, 2);
            $available = $journal->unallocatedAmount();

            $apply = round(min($amount ?? $available, $available, $billBalance), 2);

            if ($apply <= 0) {
                return null;
            }

            $allocation = new BillPayment;
            $allocation->vendor_id = $bill->vendor_id;
            $allocation->bill_id = $bill->id;
            $allocation->supplier_journal_id = $journal->id;
            $allocation->source = self::SOURCE;
            $allocation->currency_id = $bill->currency_id;
            $allocation->amount = $apply;
            $allocation->save();

            $this->setBillBalance($bill, $billBalance - $apply);

            return $allocation;
        });
    }

    public function deallocate(BillPayment $allocation): void
    {
        DB::transaction(function () use ($allocation) {
            $bill = Bill::lockForUpdate()->find($allocation->bill_id);

            if ($bill) {
                $this->setBillBalance($bill, (float) $bill->balance + (float) $allocation->amount);
            }

            $allocation->delete();
        });
    }

    /** Total suppliers-journal debits applied to a bill. */
    public function allocatedToBill(Bill $bill): float
    {
        return round((float) BillPayment::where('bill_id', $bill->id)
            ->where('source', self::SOURCE)
            ->sum(DB::raw('COALESCE(amount+0,0)')), 2);
    }

    private function setBillBalance(Bill $bill, float $balance): void
    {
        $balance = round(max(0, min($balance, (float) $bill->total)), 2);

        $bill->balance = $balance;
        $bill->status = $balance <= 0 ? 'Paid' : ($balance < round((float) $bill->total, 2) ? 'Partial' : 'Unpaid');
        $bill->save();
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function rate($rate): float
    {
        return is_numeric($rate) && (float) $rate > 0 ? (float) $rate : 1.0;
    }

    private function companyId(): ?int
    {
        return Auth::user()?->employee?->company_id ?? Company::value('id');
    }

    private function number(): string
    {
        $next = (int) SupplierJournal::withTrashed()->max('id') + 1;

        return 'SJ' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }

    private function journalNumber(): string
    {
        $last = JournalEntry::orderByDesc('id')->value('journal_number');
        $next = $last ? ((int) substr($last, 4)) + 1 : 1;

        return 'JNL-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }
}
