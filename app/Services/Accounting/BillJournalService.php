<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\Bill;
use App\Models\Container;
use App\Models\Fuel;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BillJournalService
{
    // Per-line rounding to 2dp can drift a few cents from the rounded total.
    private const BALANCE_TOLERANCE = 0.05;

    public function post(Bill $bill): JournalEntry
    {
        return $this->postInternal($bill, $this->creditAccountFor($bill));
    }

    /**
     * The account a bill's total is credited to. Accounts Payable, except
     * bills that draw down stock already paid for rather than creating a
     * second payable: a Bulk Buy fuel order's consumption (Fuel Inventory)
     * and a stores issue (Spares Inventory). Resolved here so every generic tool that reposts a
     * bill (Resync to Ledger, Line item correction, TB Repair, Post to
     * Ledger) credits the right account, not just FuelJournalService.
     */
    public function creditAccountFor(Bill $bill): Account
    {
        // withTrashed - a station deleted since doesn't change how its fuel was bought.
        $containerId = $bill->fuel_id ? Fuel::withTrashed()->whereKey($bill->fuel_id)->value('container_id') : null;
        $purchaseType = $containerId ? Container::withTrashed()->whereKey($containerId)->value('purchase_type') : null;

        if (! $bill->top_up_id && $purchaseType === 'Bulk Buy') {
            return Account::where('name', 'Fuel Inventory')->firstOrFail();
        }

        // Stores issue (Dispatches/Pending) - draws down spares already
        // bought, see InventoryJournalService::postDispatchBill().
        if ($bill->dispatch_id) {
            return Account::where('name', 'Spares Inventory')->firstOrFail();
        }

        return Account::where('name', 'Accounts Payable')->firstOrFail();
    }

    /**
     * Same as post() but credits $creditAccount instead of Accounts Payable -
     * for a bill that draws down a liability already recognized elsewhere
     * (e.g. Bulk Buy fuel consumption drawing down the Fuel Inventory asset
     * that was booked to AP at top-up time), rather than a genuine new
     * payable. Crediting AP again in that case would fabricate a liability
     * with nothing left to ever clear it.
     */
    public function postWithCreditAccount(Bill $bill, Account $creditAccount): JournalEntry
    {
        return $this->postInternal($bill, $creditAccount);
    }

    private function postInternal(Bill $bill, Account $creditAccount): JournalEntry
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

        // load(), not loadMissing() - a bill_expenses relation touched before
        // its lines were saved would otherwise be reused here, still empty.
        $bill->load(['bill_expenses.account', 'vendor']);

        $debits = $this->postableDebitTotal($bill);
        $total = is_numeric($bill->total) ? round((float) $bill->total, 2) : 0.0;
        if (abs($debits - $total) > self::BALANCE_TOLERANCE) {
            throw new \RuntimeException(
                "Bill {$bill->bill_number} can't be posted: its expense lines (with an account) plus VAT come to "
                . number_format($debits, 2) . " but the bill total is " . number_format($total, 2)
                . " - posting it would put the Trial Balance out of balance."
            );
        }

        $rate = $bill->exchange_rate ?? 1;

        $vatAccount = Account::where('name', 'Value Added Tax')->firstOrFail();

        return DB::transaction(function () use ($bill, $creditAccount, $vatAccount, $rate) {

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
                    'container_id'    => $bill->container_id,
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

            // ── CR $creditAccount (full bill total) - Accounts Payable for a
            // genuine new payable, or another account (e.g. Fuel Inventory)
            // when this bill draws down a liability already recognized
            // elsewhere - see postWithCreditAccount(). ────────────────────
            $total = is_numeric($bill->total) ? (float) $bill->total : 0;

            $entry->journal_entry_lines()->create([
                'account_id'      => $creditAccount->id,
                'vendor_id'       => $bill->vendor_id,
                'container_id'    => $bill->container_id,
                'debit'           => 0,
                'credit'          => $total,
                'exchange_debit'  => 0,
                'exchange_credit' => $total * (is_numeric($rate) ? (float) $rate : 0),
                'currency_id'     => $bill->currency_id,
                'exchange_rate'   => $rate,
                'description'     => "{$creditAccount->name} - Bill {$bill->bill_number}",
            ]);

            return $entry;
        });
    }

    /**
     * Whether post() would produce a balanced entry right now - i.e. the
     * bill's expense lines are all in place. Used by BillExpenseObserver to
     * post a bill only once its last line has been saved.
     */
    public function isPostable(Bill $bill): bool
    {
        $bill->load('bill_expenses');
        $total = is_numeric($bill->total) ? round((float) $bill->total, 2) : 0.0;

        return $total > 0 && abs($this->postableDebitTotal($bill) - $total) <= self::BALANCE_TOLERANCE;
    }

    /** Sum of the debit legs postInternal() would write (expense lines with an account, plus VAT). */
    private function postableDebitTotal(Bill $bill): float
    {
        $lines = $bill->bill_expenses
            ->filter(fn ($expense) => $expense->account_id)
            ->sum(fn ($expense) => is_numeric($expense->subtotal) ? round((float) $expense->subtotal, 2) : 0);

        $tax = is_numeric($bill->tax_amount) && $bill->tax_amount > 0 ? round((float) $bill->tax_amount, 2) : 0;

        return round($lines + $tax, 2);
    }

    protected function generateNumber(): string
    {
        $last = JournalEntry::orderByDesc('id')->value('journal_number');
        $next = $last ? ((int) substr($last, 4)) + 1 : 1;
        return 'JNL-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }
}