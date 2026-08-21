<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\DebitNote;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DebitNoteJournalService
{
    public function post(DebitNote $debitNote): JournalEntry
    {
        // Prevent duplicate journal entries - a reversed entry (see
        // LedgerResyncService) doesn't count, so a resync can post a fresh
        // one afterward. The reversal record itself must also be excluded
        // here: it carries the same debit_note_id with status 'posted', so
        // without this it gets mistaken for "already posted" and handed
        // back instead of a genuinely fresh entry.
        $existing = JournalEntry::where('debit_note_id', $debitNote->id)
            ->where('status', '!=', 'reversed')
            ->where(fn ($q) => $q->whereNull('reference')->orWhere('reference', 'not like', 'REV-%'))
            ->first();
        if ($existing) {
            return $existing;
        }

        $debitNote->loadMissing(['debit_note_items.bill_expense', 'vendor']);

        // Resolve control accounts
        $apAccount        = Account::where('name', 'Accounts Payable')->firstOrFail();
        $vatAccount       = Account::where('name', 'Value Added Tax')->firstOrFail();
        $fallbackAccount  = Account::where('name', 'Uncategorized Expense')->firstOrFail();

        $rate = is_numeric($debitNote->exchange_rate) ? (float) $debitNote->exchange_rate : 1;

        $taxAmount = is_numeric($debitNote->tax_amount) ? (float) $debitNote->tax_amount : 0;
        $subtotal  = is_numeric($debitNote->subtotal) ? (float) $debitNote->subtotal : 0;
        $total     = is_numeric($debitNote->total) ? (float) $debitNote->total : ($subtotal + $taxAmount);

        return DB::transaction(function () use ($debitNote, $apAccount, $vatAccount, $fallbackAccount, $rate, $taxAmount, $subtotal, $total) {

            $entry = JournalEntry::create([
                'company_id'     => $debitNote->company_id ? $debitNote->company_id : Auth::user()->employee->company_id,
                'debit_note_id'  => $debitNote->id,
                'journal_number' => $this->generateNumber(),
                'date'           => $debitNote->date,
                'reference'      => $debitNote->debit_note_number,
                'description'    => "Debit Note {$debitNote->debit_note_number} - {$debitNote->vendor?->name}",
                'is_manual'      => false,
                'status'         => 'posted',
                'created_by_id'  => Auth::id(),
                'posted_by_id'   => Auth::id(),
                'posted_at'      => now(),
            ]);

            // ── DR Accounts Payable (reduces amount owed to vendor) ───────
            $entry->journal_entry_lines()->create([
                'account_id'      => $apAccount->id,
                'vendor_id'       => $debitNote->vendor_id,
                'debit'           => $total,
                'credit'          => 0,
                'exchange_debit'  => $total * $rate,
                'exchange_credit' => 0,
                'currency_id'     => $debitNote->currency_id,
                'exchange_rate'   => $rate,
                'description'     => "AP - Debit Note {$debitNote->debit_note_number}",
            ]);

            // ── CR each expense line against the account it was originally
            //    billed to (falls back to Uncategorized Expense for lines
            //    not tied to a specific bill expense) ─────────────────────
            foreach ($debitNote->debit_note_items as $item) {
                $lineAmount = is_numeric($item->subtotal) ? (float) $item->subtotal : (is_numeric($item->amount) ? (float) $item->amount : 0);
                if ($lineAmount <= 0) continue;

                $account = $item->bill_expense?->account ?? $fallbackAccount;

                $entry->journal_entry_lines()->create([
                    'account_id'      => $account->id,
                    'vendor_id'       => $debitNote->vendor_id,
                    'debit'           => 0,
                    'credit'          => $lineAmount,
                    'exchange_debit'  => 0,
                    'exchange_credit' => $lineAmount * $rate,
                    'currency_id'     => $debitNote->currency_id,
                    'exchange_rate'   => $rate,
                    'description'     => $item->description ?: "Debit Note {$debitNote->debit_note_number} - {$account->name}",
                ]);
            }

            // ── CR VAT (reverses input tax reclaimed on the bill) ─────────
            if ($taxAmount > 0) {
                $entry->journal_entry_lines()->create([
                    'account_id'      => $vatAccount->id,
                    'vendor_id'       => $debitNote->vendor_id,
                    'debit'           => 0,
                    'credit'          => $taxAmount,
                    'exchange_debit'  => 0,
                    'exchange_credit' => $taxAmount * $rate,
                    'currency_id'     => $debitNote->currency_id,
                    'exchange_rate'   => $rate,
                    'description'     => "VAT reversal - Debit Note {$debitNote->debit_note_number}",
                ]);
            }

            return $entry;
        });
    }

    protected function generateNumber(): string
    {
        $last = JournalEntry::orderByDesc('id')->value('journal_number');
        $next = $last ? ((int) substr($last, 4)) + 1 : 1;
        return 'JNL-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }
}
