<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\Bill;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BillJournalService
{
    public function post(Bill $bill): JournalEntry
    {
        // Prevent duplicate - a reversed entry (see LedgerResyncService)
        // doesn't count, so a resync can post a fresh one afterward. The
        // reversal record itself must also be excluded here: it carries the
        // same bill_id with status 'posted', so without this it gets
        // mistaken for "already posted" and handed back instead of a
        // genuinely fresh entry.
        $existing = JournalEntry::where('bill_id', $bill->id)
            ->where('status', '!=', 'reversed')
            ->where(fn ($q) => $q->whereNull('reference')->orWhere('reference', 'not like', 'REV-%'))
            ->first();
        if ($existing) {
            return $existing;
        }

        $bill->loadMissing(['bill_expenses.account', 'vendor']);

        $rate = $bill->exchange_rate ?? 1;

        $apAccount  = Account::where('name', 'Accounts Payable')->firstOrFail();
        $vatAccount = Account::where('name', 'Value Added Tax')->firstOrFail();

        return DB::transaction(function () use ($bill, $apAccount, $vatAccount, $rate) {

            $entry = JournalEntry::create([
                'company_id'     => $bill->company_id ? $bill->company_id : Auth::user()->employee->company_id,
                'bill_id'        => $bill->id,
                'journal_number' => $this->generateNumber(),
                'date'           => $bill->bill_date,
                'reference'      => $bill->bill_number,
                'description'    => "Bill {$bill->bill_number} - {$bill->vendor?->name}",
                'is_manual'      => false,
                'status'         => 'posted',
                'created_by_id'  => Auth::id(),
                'posted_by_id'   => Auth::id(),
                'posted_at'      => now(),
            ]);

            // ── DR each expense line against its account ─────────────────
            foreach ($bill->bill_expenses as $expense) {

                if (!$expense->account_id) continue;

                $subtotal = is_numeric($expense->subtotal) ? (float) $expense->subtotal : 0;

                $entry->journal_entry_lines()->create([
                    'account_id'      => $expense->account_id,
                    'vendor_id'       => $bill->vendor_id,
                    // Dimensions from bill_for
                    'horse_id'        => $bill->horse_id,
                    'vehicle_id'      => $bill->vehicle_id,
                    'trailer_id'      => $bill->trailer_id,
                    'driver_id'       => $bill->driver_id,
                    'transporter_id'  => $bill->transporter_id,
                    'debit'           => $subtotal,
                    'credit'          => 0,
                    'exchange_debit'  => $subtotal * (is_numeric($rate) ? (float) $rate : 0),
                    'exchange_credit' => 0,
                    'currency_id'     => $bill->currency_id,
                    'exchange_rate'   => $rate,
                    'description'     => $expense->description ?? "Bill {$bill->bill_number} - {$expense->account?->name}",
                ]);
            }

            // ── DR VAT (input tax — recoverable) ─────────────────────────
            if ($bill->tax_amount > 0) {
                $taxAmount = is_numeric($bill->tax_amount) ? (float) $bill->tax_amount : 0;

                $entry->journal_entry_lines()->create([
                    'account_id'      => $vatAccount->id,
                    'vendor_id'       => $bill->vendor_id,
                    'debit'           => $taxAmount,
                    'credit'          => 0,
                    'exchange_debit'  => $taxAmount * (is_numeric($rate) ? (float) $rate : 0),
                    'exchange_credit' => 0,
                    'currency_id'     => $bill->currency_id,
                    'exchange_rate'   => $rate,
                    'description'     => "VAT - Bill {$bill->bill_number}",
                ]);
            }

            // ── CR Accounts Payable (full bill total) ────────────────────
            $total = is_numeric($bill->total) ? (float) $bill->total : 0;

            $entry->journal_entry_lines()->create([
                'account_id'      => $apAccount->id,
                'vendor_id'       => $bill->vendor_id,
                'debit'           => 0,
                'credit'          => $total,
                'exchange_debit'  => 0,
                'exchange_credit' => $total * (is_numeric($rate) ? (float) $rate : 0),
                'currency_id'     => $bill->currency_id,
                'exchange_rate'   => $rate,
                'description'     => "AP - Bill {$bill->bill_number}",
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