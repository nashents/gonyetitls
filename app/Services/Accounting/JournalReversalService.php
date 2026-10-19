<?php

namespace App\Services\Accounting;

use App\Models\JournalEntry;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JournalReversalService
{
    /**
     * Post an offsetting entry for $entry (debit/credit flipped on every line),
     * mark the original as 'reversed', and return the new reversing entry.
     * Returns null if $entry is already reversed.
     *
     * Dated like $entry itself (so it cancels in the same period) unless
     * $date is given.
     */
    public function reverse(JournalEntry $entry, ?string $reason = null, ?string $date = null): ?JournalEntry
    {
        if ($entry->status === 'reversed') {
            return null;
        }

        $entry->loadMissing('journal_entry_lines');

        if ($entry->journal_entry_lines->contains(fn ($line) => $line->cleared_at !== null)) {
            throw new \RuntimeException(
                "Journal entry {$entry->journal_number} has a line cleared against a bank statement - unmatch it from that bank statement line (reopening its bank reconciliation first, if already completed) before reversing it."
            );
        }

        return DB::transaction(function () use ($entry, $reason, $date) {

            $entry->loadMissing('journal_entry_lines');

            $reversal = JournalEntry::create([
                'company_id'     => $entry->company_id,
                'invoice_id'     => $entry->invoice_id,
                'bill_id'        => $entry->bill_id,
                'payment_id'     => $entry->payment_id,
                'invoice_payment_id' => $entry->invoice_payment_id,
                'bill_payment_id' => $entry->bill_payment_id,
                'credit_note_id' => $entry->credit_note_id,
                'payroll_run_id' => $entry->payroll_run_id,
                'customer_fuel_supply_id' => $entry->customer_fuel_supply_id,
                'debtor_journal_id' => $entry->debtor_journal_id,
                'supplier_journal_id' => $entry->supplier_journal_id,
                'is_manual'      => false,
                'journal_number' => $this->generateNumber(),
                // Same period as the entry it cancels, whatever the reason
                // (correction, removal, deletion) - accountant's rule; a
                // today-dated reversal left the old amount in its month.
                'date'           => $date ?? optional($entry->date)->toDateString() ?? now()->toDateString(),
                'reference'      => "REV-{$entry->journal_number}",
                'description'    => "Reversal of {$entry->journal_number}" . ($reason ? " - {$reason}" : ''),
                'status'         => 'posted',
                'created_by_id'  => Auth::id(),
                'posted_by_id'   => Auth::id(),
                'posted_at'      => now(),
            ]);

            foreach ($entry->journal_entry_lines as $line) {
                $reversal->journal_entry_lines()->create([
                    'account_id'      => $line->account_id,
                    'branch_id'       => $line->branch_id,
                    'customer_id'     => $line->customer_id,
                    'vendor_id'       => $line->vendor_id,
                    'horse_id'        => $line->horse_id,
                    'employee_id'     => $line->employee_id,
                    'driver_id'       => $line->driver_id,
                    'transporter_id'  => $line->transporter_id,
                    'vehicle_id'      => $line->vehicle_id,
                    'trailer_id'      => $line->trailer_id,
                    'currency_id'     => $line->currency_id,
                    'exchange_rate'   => $line->exchange_rate,
                    // debit/credit intentionally swapped to offset the original line
                    'debit'           => $line->credit,
                    'credit'          => $line->debit,
                    'exchange_debit'  => $line->exchange_credit,
                    'exchange_credit' => $line->exchange_debit,
                    'description'     => "Reversal - {$line->description}",
                ]);
            }

            $entry->status = 'reversed';
            $entry->save();

            return $reversal;
        });
    }

    /**
     * Un-post a document: reverse each of its entries that still carries a
     * live effect (isLiveEffect) - never a REV- entry that is only
     * cancelling an earlier posting. Reversing "everything not reversed"
     * reversed those cancellers too, re-instating the postings they had
     * cancelled (deleted bills left 3x their expense + one-sided AP credits
     * in the ledger, throwing the Trial Balance out).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $entries  e.g. JournalEntry::where('bill_id', $id)
     * @param  bool  $originalDate  date each reversal like the entry it cancels (corrections)
     */
    public function reverseLiveEffects($entries, ?string $reason = null, bool $originalDate = false): int
    {
        $count = 0;
        $entries->where('status', '!=', 'reversed')
            ->orderBy('id')
            ->get()
            ->filter(fn (JournalEntry $entry) => $this->isLiveEffect($entry))
            ->each(function (JournalEntry $entry) use ($reason, $originalDate, &$count) {
                if ($this->reverse($entry, $reason, $originalDate ? optional($entry->date)->toDateString() : null)) {
                    $count++;
                }
            });

        return $count;
    }

    /**
     * Undo a previous reverse() call: find the reversing entry this entry's
     * reversal created (matched by its "REV-{journal_number}" reference) and
     * reverse that reversal, restoring the original's net ledger effect
     * without mutating either historical entry beyond their status flags.
     * Returns null if $entry isn't reversed or its reversal can't be found.
     */
    public function unreverse(JournalEntry $entry, ?string $reason = null): ?JournalEntry
    {
        if ($entry->status !== 'reversed') {
            return null;
        }

        $reversalEntry = JournalEntry::where('reference', "REV-{$entry->journal_number}")->first();

        if (! $reversalEntry || $reversalEntry->status === 'reversed') {
            return null;
        }

        return $this->reverse($reversalEntry, $reason);
    }

    /**
     * Whether $entry currently carries a live ledger effect of the document
     * it belongs to - i.e. reversing it is what un-posts that document.
     *
     * A reversal (REV-X) keeps status 'posted' and cancels X, so it's NOT a
     * live effect: reversing it re-instates X. That's exactly what unreverse()
     * does on purpose (restoring a deleted document), which makes the
     * reversal-of-a-reversal a live effect again. So it comes down to how
     * many REV- hops separate the entry from its original: even = live
     * (0 = the original itself), odd = a canceller. Treating every posted
     * entry as live is what let resync/TB-repair runs reverse reversals and
     * double-post documents.
     */
    public function isLiveEffect(JournalEntry $entry): bool
    {
        if (in_array($entry->status, ['reversed', 'draft'], true)) {
            return false;
        }

        return $this->reversalDepth($entry) % 2 === 0;
    }

    public function reversalDepth(JournalEntry $entry): int
    {
        $depth = 0;
        $reference = (string) $entry->reference;

        while (str_starts_with($reference, 'REV-') && $depth < 50) {
            $depth++;
            $reference = (string) JournalEntry::withTrashed()
                ->where('journal_number', substr($reference, 4))
                ->value('reference');
        }

        return $depth;
    }

    protected function generateNumber(): string
    {
        $last = JournalEntry::orderByDesc('id')->value('journal_number');
        $next = $last ? ((int) substr($last, 4)) + 1 : 1;
        return 'JNL-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }
}
