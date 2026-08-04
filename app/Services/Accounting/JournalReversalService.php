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
     */
    public function reverse(JournalEntry $entry, ?string $reason = null): ?JournalEntry
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

        return DB::transaction(function () use ($entry, $reason) {

            $entry->loadMissing('journal_entry_lines');

            $reversal = JournalEntry::create([
                'company_id'     => $entry->company_id,
                'invoice_id'     => $entry->invoice_id,
                'bill_id'        => $entry->bill_id,
                'payment_id'     => $entry->payment_id,
                'credit_note_id' => $entry->credit_note_id,
                'payroll_run_id' => $entry->payroll_run_id,
                'is_manual'      => false,
                'journal_number' => $this->generateNumber(),
                'date'           => now()->toDateString(),
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

    protected function generateNumber(): string
    {
        $last = JournalEntry::orderByDesc('id')->value('journal_number');
        $next = $last ? ((int) substr($last, 4)) + 1 : 1;
        return 'JNL-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }
}
