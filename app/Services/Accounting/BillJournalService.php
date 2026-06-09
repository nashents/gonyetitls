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
        // Prevent duplicate
        if (JournalEntry::where('bill_id', $bill->id)->exists()) {
            return JournalEntry::where('bill_id', $bill->id)->first();
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

                $entry->journal_entry_lines()->create([
                    'account_id'      => $expense->account_id,
                    'vendor_id'       => $bill->vendor_id,
                    // Dimensions from bill_for
                    'horse_id'        => $bill->horse_id,
                    'vehicle_id'      => $bill->vehicle_id,
                    'trailer_id'      => $bill->trailer_id,
                    'driver_id'       => $bill->driver_id,
                    'transporter_id'  => $bill->transporter_id,
                    'debit'           => $expense->subtotal,
                    'credit'          => 0,
                    'exchange_debit'  => $expense->subtotal * $rate,
                    'exchange_credit' => 0,
                    'currency_id'     => $bill->currency_id,
                    'exchange_rate'   => $rate,
                    'description'     => $expense->description ?? "Bill {$bill->bill_number} - {$expense->account?->name}",
                ]);
            }

            // ── DR VAT (input tax — recoverable) ─────────────────────────
            if ($bill->tax_amount > 0) {
                $entry->journal_entry_lines()->create([
                    'account_id'      => $vatAccount->id,
                    'vendor_id'       => $bill->vendor_id,
                    'debit'           => $bill->tax_amount,
                    'credit'          => 0,
                    'exchange_debit'  => $bill->tax_amount * $rate,
                    'exchange_credit' => 0,
                    'currency_id'     => $bill->currency_id,
                    'exchange_rate'   => $rate,
                    'description'     => "VAT - Bill {$bill->bill_number}",
                ]);
            }

            // ── CR Accounts Payable (full bill total) ────────────────────
            $entry->journal_entry_lines()->create([
                'account_id'      => $apAccount->id,
                'vendor_id'       => $bill->vendor_id,
                'debit'           => 0,
                'credit'          => $bill->total,
                'exchange_debit'  => 0,
                'exchange_credit' => $bill->total * $rate,
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