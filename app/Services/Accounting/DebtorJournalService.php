<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\Company;
use App\Models\DebtorJournal;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Debtors journal - manual adjustments to a single customer's account.
 *
 *  - debit:  DR Accounts Receivable (customer)   CR contra account
 *  - credit: DR contra account                   CR Accounts Receivable (customer)
 *
 * A credit can be allocated to the customer's open invoices through
 * invoice_payments (source = 'debtor_journal'), reducing the invoice balance
 * the same way a receipt or customer-supplied fuel does. Unallocated credits
 * and debit journals still show on the statement and in aged receivables.
 *
 * Posted journals aren't edited - they're voided (journal reversed,
 * allocations released) and re-captured, so the audit trail stays intact.
 * Like customer-supplied fuel, it isn't a Payment row: no cash moved.
 */
class DebtorJournalService
{
    public const SOURCE = 'debtor_journal';

    public function __construct(private JournalReversalService $journalReversal)
    {
    }

    /**
     * Create and post a debtors journal. $allocations is an optional
     * [invoice_id => amount] map applied straight away (credits only).
     */
    public function create(array $attributes, array $allocations = []): DebtorJournal
    {
        return DB::transaction(function () use ($attributes, $allocations) {
            $journal = new DebtorJournal($attributes);
            $journal->amount = round((float) $journal->amount, 2);
            $journal->exchange_rate = $this->rate($journal->exchange_rate);
            $journal->status = 'posted';
            $journal->user_id = Auth::id();
            $journal->company_id = $this->companyId();
            $journal->journal_number = $this->number();
            $journal->save();

            $this->post($journal);

            if ($journal->isCredit()) {
                foreach ($allocations as $invoiceId => $amount) {
                    if ((float) $amount > 0 && ($invoice = Invoice::find($invoiceId))) {
                        $this->allocate($journal, $invoice, (float) $amount);
                    }
                }
            }

            return $journal->fresh();
        });
    }

    public function post(DebtorJournal $journal): JournalEntry
    {
        if ($existing = $this->activeEntry($journal)) {
            return $existing;
        }

        $journal->loadMissing('customer', 'account');

        $arAccount = Account::where('name', 'Accounts Receivable')->firstOrFail();
        $contra = $journal->account ?? Account::findOrFail($journal->account_id);
        $amount = round((float) $journal->amount, 2);
        $rate = $this->rate($journal->exchange_rate);
        $customerName = optional($journal->customer)->name;
        $isCredit = $journal->isCredit();
        $narration = $journal->description ?: ($isCredit ? 'Debtors journal credit' : 'Debtors journal debit');

        return DB::transaction(function () use ($journal, $arAccount, $contra, $amount, $rate, $customerName, $isCredit, $narration) {
            $entry = JournalEntry::create([
                'company_id'        => $journal->company_id ?? $this->companyId(),
                'debtor_journal_id' => $journal->id,
                'journal_number'    => $this->journalNumber(),
                'date'              => $journal->date,
                'reference'         => $journal->journal_number,
                'description'       => "Debtors journal {$journal->journal_number} - {$customerName} - {$narration}",
                'is_manual'         => false,
                'status'            => 'posted',
                'created_by_id'     => Auth::id(),
                'posted_by_id'      => Auth::id(),
                'posted_at'         => now(),
            ]);

            // Accounts Receivable - tagged to the customer
            $entry->journal_entry_lines()->create([
                'account_id'      => $arAccount->id,
                'customer_id'     => $journal->customer_id,
                'debit'           => $isCredit ? 0 : $amount,
                'credit'          => $isCredit ? $amount : 0,
                'exchange_debit'  => $isCredit ? 0 : $amount * $rate,
                'exchange_credit' => $isCredit ? $amount * $rate : 0,
                'currency_id'     => $journal->currency_id,
                'exchange_rate'   => $rate,
                'description'     => "AR - {$customerName} - {$journal->journal_number} - {$narration}",
            ]);

            // Contra account
            $entry->journal_entry_lines()->create([
                'account_id'      => $contra->id,
                'customer_id'     => $journal->customer_id,
                'debit'           => $isCredit ? $amount : 0,
                'credit'          => $isCredit ? 0 : $amount,
                'exchange_debit'  => $isCredit ? $amount * $rate : 0,
                'exchange_credit' => $isCredit ? 0 : $amount * $rate,
                'currency_id'     => $journal->currency_id,
                'exchange_rate'   => $rate,
                'description'     => "{$contra->name} - {$customerName} - {$journal->journal_number} - {$narration}",
            ]);

            return $entry;
        });
    }

    /**
     * Reverse the journal entry, release any invoice allocations back onto
     * the invoices' balances, and mark the journal voided (kept for audit).
     */
    public function void(DebtorJournal $journal, ?string $reason = null): void
    {
        if ($journal->isVoided()) {
            return;
        }

        DB::transaction(function () use ($journal, $reason) {
            foreach ($journal->invoice_payments()->get() as $allocation) {
                $this->deallocate($allocation);
            }

            $reason = $reason ?: 'Voided';

            JournalEntry::where('debtor_journal_id', $journal->id)
                ->where('status', '!=', 'reversed')
                ->where(fn ($q) => $q->whereNull('reference')->orWhere('reference', 'not like', 'REV-%'))
                ->get()
                ->each(fn ($entry) => $this->journalReversal->reverse($entry, "Debtors journal {$journal->journal_number} voided - {$reason}"));

            $journal->status = 'voided';
            $journal->voided_by_id = Auth::id();
            $journal->voided_at = now();
            $journal->void_reason = $reason;
            $journal->save();
        });
    }

    private function activeEntry(DebtorJournal $journal): ?JournalEntry
    {
        return JournalEntry::where('debtor_journal_id', $journal->id)
            ->where('status', '!=', 'reversed')
            ->where(fn ($q) => $q->whereNull('reference')->orWhere('reference', 'not like', 'REV-%'))
            ->whereHas('journal_entry_lines')
            ->latest('id')
            ->first();
    }

    // ── Allocation to invoices ───────────────────────────────────────────────

    /**
     * Apply (part of) a credit journal against an invoice's open balance.
     * Returns the allocation, or null when there's nothing to apply.
     */
    public function allocate(DebtorJournal $journal, Invoice $invoice, ?float $amount = null): ?InvoicePayment
    {
        if (!$journal->isCredit() || $journal->isVoided()) {
            throw new \RuntimeException("Only posted credit journals can be allocated to invoices.");
        }

        if ((int) $invoice->customer_id !== (int) $journal->customer_id || (int) $invoice->currency_id !== (int) $journal->currency_id) {
            throw new \RuntimeException("Debtors journal {$journal->journal_number} can only be applied to the same customer's invoices in the same currency.");
        }

        if ($invoice->authorization !== 'approved') {
            throw new \RuntimeException("Invoice {$invoice->invoice_number} isn't approved.");
        }

        return DB::transaction(function () use ($journal, $invoice, $amount) {
            $invoice = Invoice::lockForUpdate()->find($invoice->id);
            $invoiceBalance = round((float) $invoice->balance, 2);
            $available = $journal->unallocatedAmount();

            $apply = round(min($amount ?? $available, $available, $invoiceBalance), 2);

            if ($apply <= 0) {
                return null;
            }

            $allocation = new InvoicePayment;
            $allocation->customer_id = $invoice->customer_id;
            $allocation->invoice_id = $invoice->id;
            $allocation->debtor_journal_id = $journal->id;
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

    /** Total debtors-journal credits applied to an invoice. */
    public function allocatedToInvoice(Invoice $invoice): float
    {
        return round((float) InvoicePayment::where('invoice_id', $invoice->id)
            ->where('source', self::SOURCE)
            ->sum(DB::raw('COALESCE(amount+0,0)')), 2);
    }

    private function setInvoiceBalance(Invoice $invoice, float $balance): void
    {
        $balance = round(max(0, min($balance, (float) $invoice->total)), 2);

        $invoice->balance = $balance;
        $invoice->status = $balance <= 0 ? 'Paid' : ($balance < round((float) $invoice->total, 2) ? 'Partial' : 'Unpaid');
        $invoice->save();
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
        $next = (int) DebtorJournal::withTrashed()->max('id') + 1;

        return 'DJ' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }

    private function journalNumber(): string
    {
        $last = JournalEntry::orderByDesc('id')->value('journal_number');
        $next = $last ? ((int) substr($last, 4)) + 1 : 1;

        return 'JNL-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }
}
