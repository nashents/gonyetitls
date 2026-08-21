<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\Invoice;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceJournalService
{
    public function post(Invoice $invoice): JournalEntry
    {
        // Prevent duplicate journal entries - a reversed entry (see
        // LedgerResyncService) doesn't count, so a resync can post a fresh
        // one afterward. The reversal record itself must also be excluded
        // here: JournalReversalService copies invoice_id onto it, and its
        // status is 'posted' (only the original it reversed flips to
        // 'reversed'), so without this it gets mistaken for "already
        // posted" and handed back in place of a genuinely fresh entry.
        $existing = JournalEntry::where('invoice_id', $invoice->id)
            ->where('status', '!=', 'reversed')
            ->where(fn ($q) => $q->whereNull('reference')->orWhere('reference', 'not like', 'REV-%'))
            ->first();
        if ($existing) {
            return $existing;
        }

        // Resolve control accounts
        $arAccount       = Account::where('name', 'Accounts Receivable')->firstOrFail();
        $salesAccount    = Account::where('name', 'Sales')->firstOrFail();
        $discountAccount = Account::where('name', 'Sales Discounts')->firstOrFail();
        $vatAccount      = Account::where('name', 'Value Added Tax')->firstOrFail(); // adjust if multiple VAT rates

        $rate = $invoice->exchange_rate ?? 1;

        return DB::transaction(function () use ($invoice, $arAccount, $salesAccount, $discountAccount, $vatAccount, $rate) {

            $entry = JournalEntry::create([
                'company_id'     => $invoice->company_id ? $invoice->company_id : Auth::user()->employee->company_id,
                'invoice_id'     => $invoice->id,
                'journal_number' => $this->generateNumber(),
                'date'           => $invoice->date,
                'reference'      => $invoice->invoice_number,
                'description'    => "Invoice {$invoice->invoice_number} - {$invoice->customer?->name}",
                'is_manual'      => false,
                'status'         => 'posted',
                'created_by_id'  => Auth::id(),
                'posted_by_id'   => Auth::id(),
                'posted_at'      => now(),
            ]);

            // ── DR Accounts Receivable (full invoice total) ──────────────
            $entry->journal_entry_lines()->create([
                'account_id'      => $arAccount->id,
                'customer_id'     => $invoice->customer_id,
                'debit'           => $invoice->total,
                'credit'          => 0,
                'exchange_debit'  => $invoice->total * $rate,
                'exchange_credit' => 0,
                'currency_id'     => $invoice->currency_id,
                'exchange_rate'   => $rate,
                'description'     => "AR - Invoice {$invoice->invoice_number}",
            ]);

            // ── CR Sales (subtotal before tax and discounts) ─────────────
            $entry->journal_entry_lines()->create([
                'account_id'      => $salesAccount->id,
                'customer_id'     => $invoice->customer_id,
                'debit'           => 0,
                'credit'          => $invoice->subtotal,
                'exchange_debit'  => 0,
                'exchange_credit' => $invoice->subtotal * $rate,
                'currency_id'     => $invoice->currency_id,
                'exchange_rate'   => $rate,
                'description'     => "Sales - Invoice {$invoice->invoice_number}",
            ]);

            // ── CR VAT Payable (tax_amount) ──────────────────────────────
            if ($invoice->tax_amount > 0) {
                $entry->journal_entry_lines()->create([
                    'account_id'      => $vatAccount->id,
                    'customer_id'     => $invoice->customer_id,
                    'debit'           => 0,
                    'credit'          => $invoice->tax_amount,
                    'exchange_debit'  => 0,
                    'exchange_credit' => $invoice->tax_amount * $rate,
                    'currency_id'     => $invoice->currency_id,
                    'exchange_rate'   => $rate,
                    'description'     => "VAT - Invoice {$invoice->invoice_number}",
                ]);
            }

            // ── DR Sales Discounts (if any discount applied) ─────────────
            if (!empty($invoice->discount_amount) && $invoice->discount_amount > 0) {
                $entry->journal_entry_lines()->create([
                    'account_id'      => $discountAccount->id,
                    'customer_id'     => $invoice->customer_id,
                    'debit'           => $invoice->discount_amount,
                    'credit'          => 0,
                    'exchange_debit'  => $invoice->discount_amount * $rate,
                    'exchange_credit' => 0,
                    'currency_id'     => $invoice->currency_id,
                    'exchange_rate'   => $rate,
                    'description'     => "Discount - Invoice {$invoice->invoice_number}",
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