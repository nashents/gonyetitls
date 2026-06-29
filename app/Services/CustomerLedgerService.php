<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

/**
 * Single source of truth for a customer's accrual (running) balance.
 *
 * The balance is never stored on invoices/payments — it is always derived by
 * replaying the customer's transaction history (approved invoices increase
 * the balance, payments and approved credit notes reduce it) in chronological
 * order. This is the standard "open item" statement method: there is nothing
 * to keep in sync, nothing to reverse on deletion, and no risk of two writers
 * racing on a shared snapshot column.
 */
class CustomerLedgerService
{
    /**
     * The full signed transaction history for a customer + currency,
     * ordered chronologically. Each row carries enough detail to render a
     * statement line plus a running `accrual_balance`.
     */
    public function ledger(int $customerId, int $currencyId): Collection
    {
        $invoices = DB::table('invoices')
            ->select([
                'id',
                DB::raw("'invoice' as transaction_type"),
                'invoice_number as number',
                'currency_id',
                'date',
                'date as transaction_date',
                'created_at',
                DB::raw('CAST(total AS DECIMAL(20,2)) as amount'),
                DB::raw('CAST(total AS DECIMAL(20,2)) as balance'),
                DB::raw('CAST(total AS DECIMAL(20,2)) as signed_amount'),
                DB::raw('0 as type_priority'),
            ])
            ->where('customer_id', $customerId)
            ->where('currency_id', $currencyId)
            ->where('authorization', 'approved')
            ->whereNull('deleted_at');

        $payments = DB::table('payments')
            ->select([
                'id',
                DB::raw("'payment' as transaction_type"),
                'payment_number as number',
                'currency_id',
                'date',
                'date as transaction_date',
                'created_at',
                DB::raw('CAST(amount AS DECIMAL(20,2)) as amount'),
                DB::raw('CAST(balance AS DECIMAL(20,2)) as balance'),
                DB::raw('CAST(amount AS DECIMAL(20,2)) * -1 as signed_amount'),
                DB::raw('1 as type_priority'),
            ])
            ->where('customer_id', $customerId)
            ->where('currency_id', $currencyId)
            ->whereNull('deleted_at');

        $creditNotes = DB::table('credit_notes')
            ->select([
                'id',
                DB::raw("'credit_note' as transaction_type"),
                'credit_note_number as number',
                'currency_id',
                'date',
                'date as transaction_date',
                'created_at',
                DB::raw('CAST(total AS DECIMAL(20,2)) as amount'),
                DB::raw('CAST(total AS DECIMAL(20,2)) as balance'),
                DB::raw('CAST(total AS DECIMAL(20,2)) * -1 as signed_amount'),
                DB::raw('1 as type_priority'),
            ])
            ->where('customer_id', $customerId)
            ->where('currency_id', $currencyId)
            ->where('authorization', 'approved')
            ->whereNull('deleted_at');

        $rows = DB::query()
            ->fromSub($invoices->unionAll($payments)->unionAll($creditNotes), 'ledger')
            ->orderBy('date')
            ->orderBy('created_at')
            ->orderBy('type_priority') // payments/credit notes settle before invoices on an exact tie
            ->orderBy('id')
            ->get();

        $balance = 0.0;

        return $rows->map(function ($row) use (&$balance) {
            $balance = round($balance + (float) $row->signed_amount, 2);
            $row->accrual_balance = $balance;

            return $row;
        });
    }

    /**
     * Running balance as of the last transaction strictly before $date.
     */
    public function openingBalance(int $customerId, int $currencyId, string $date): float
    {
        $last = $this->ledger($customerId, $currencyId)
            ->filter(fn ($row) => $row->date < $date)
            ->last();

        return $last ? (float) $last->accrual_balance : 0.00;
    }

    /**
     * Running balance as of the last transaction on or before $date.
     */
    public function closingBalance(int $customerId, int $currencyId, string $date): float
    {
        $last = $this->ledger($customerId, $currencyId)
            ->filter(fn ($row) => $row->date <= $date)
            ->last();

        return $last ? (float) $last->accrual_balance : 0.00;
    }

    /**
     * Statement activity rows between $from and $to (inclusive), each
     * carrying the running accrual_balance computed over the full history
     * (so it continues seamlessly from the opening balance).
     */
    public function activity(int $customerId, int $currencyId, string $from, string $to): Collection
    {
        return $this->ledger($customerId, $currencyId)
            ->filter(fn ($row) => $row->date >= $from && $row->date <= $to)
            ->values();
    }
}
