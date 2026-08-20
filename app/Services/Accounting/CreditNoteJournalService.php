<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\CreditNote;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreditNoteJournalService
{
    public function post(CreditNote $creditNote): JournalEntry
    {
        // Prevent duplicate journal entries - a reversed entry (see
        // LedgerResyncService) doesn't count, so a resync can post a fresh
        // one afterward.
        $existing = JournalEntry::where('credit_note_id', $creditNote->id)->where('status', '!=', 'reversed')->first();
        if ($existing) {
            return $existing;
        }

        // Resolve control accounts (same accounts the invoice was originally posted to)
        $arAccount    = Account::where('name', 'Accounts Receivable')->firstOrFail();
        $salesAccount = Account::where('name', 'Sales')->firstOrFail();
        $vatAccount   = Account::where('name', 'Value Added Tax')->firstOrFail();

        $rate = is_numeric($creditNote->exchange_rate) ? (float) $creditNote->exchange_rate : 1;

        $subtotal  = is_numeric($creditNote->subtotal) ? (float) $creditNote->subtotal : 0;
        $taxAmount = is_numeric($creditNote->tax_amount) ? (float) $creditNote->tax_amount : 0;
        $total     = is_numeric($creditNote->total) ? (float) $creditNote->total : ($subtotal + $taxAmount);

        return DB::transaction(function () use ($creditNote, $arAccount, $salesAccount, $vatAccount, $rate, $subtotal, $taxAmount, $total) {

            $entry = JournalEntry::create([
                'company_id'     => $creditNote->company_id ? $creditNote->company_id : Auth::user()->employee->company_id,
                'credit_note_id' => $creditNote->id,
                'journal_number' => $this->generateNumber(),
                'date'           => $creditNote->date,
                'reference'      => $creditNote->credit_note_number,
                'description'    => "Credit Note {$creditNote->credit_note_number} - {$creditNote->customer?->name}",
                'is_manual'      => false,
                'status'         => 'posted',
                'created_by_id'  => Auth::id(),
                'posted_by_id'   => Auth::id(),
                'posted_at'      => now(),
            ]);

            // ── DR Sales (reverses revenue recognized on the invoice) ────
            $entry->journal_entry_lines()->create([
                'account_id'      => $salesAccount->id,
                'customer_id'     => $creditNote->customer_id,
                'debit'           => $subtotal,
                'credit'          => 0,
                'exchange_debit'  => $subtotal * $rate,
                'exchange_credit' => 0,
                'currency_id'     => $creditNote->currency_id,
                'exchange_rate'   => $rate,
                'description'     => "Sales reversal - Credit Note {$creditNote->credit_note_number}",
            ]);

            // ── DR VAT Payable (reverses tax charged on the invoice) ─────
            if ($taxAmount > 0) {
                $entry->journal_entry_lines()->create([
                    'account_id'      => $vatAccount->id,
                    'customer_id'     => $creditNote->customer_id,
                    'debit'           => $taxAmount,
                    'credit'          => 0,
                    'exchange_debit'  => $taxAmount * $rate,
                    'exchange_credit' => 0,
                    'currency_id'     => $creditNote->currency_id,
                    'exchange_rate'   => $rate,
                    'description'     => "VAT reversal - Credit Note {$creditNote->credit_note_number}",
                ]);
            }

            // ── CR Accounts Receivable (reduces amount owed by customer) ─
            $entry->journal_entry_lines()->create([
                'account_id'      => $arAccount->id,
                'customer_id'     => $creditNote->customer_id,
                'debit'           => 0,
                'credit'          => $total,
                'exchange_debit'  => 0,
                'exchange_credit' => $total * $rate,
                'currency_id'     => $creditNote->currency_id,
                'exchange_rate'   => $rate,
                'description'     => "AR - Credit Note {$creditNote->credit_note_number}",
            ]);

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
