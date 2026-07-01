<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

/**
 * Single source of truth for a vendor's accrual (running) balance.
 *
 * Mirrors CustomerLedgerService for the vendor/bill side: the balance is
 * never stored on bills/payments — it is always derived by replaying the
 * vendor's transaction history (approved bills increase the balance,
 * payments reduce it) in chronological order.
 */
class VendorLedgerService
{
    /**
     * The full signed transaction history for a vendor + currency, ordered
     * chronologically. Each row carries enough detail to render a statement
     * line plus a running `accrual_balance`.
     */
    public function ledger(int $vendorId, int $currencyId): Collection
    {
        $bills = DB::table('bills')
            ->select([
                'id',
                DB::raw("'bill' as transaction_type"),
                'bill_number as number',
                'currency_id',
                DB::raw('bill_date as date'),
                DB::raw('bill_date as transaction_date'),
                'created_at',
                DB::raw('CAST(total AS DECIMAL(20,2)) as amount'),
                DB::raw('CAST(total AS DECIMAL(20,2)) as balance'),
                DB::raw('CAST(total AS DECIMAL(20,2)) as signed_amount'),
                DB::raw('0 as type_priority'),
            ])
            ->where('vendor_id', $vendorId)
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
            ->where('vendor_id', $vendorId)
            ->where('currency_id', $currencyId)
            ->whereNull('deleted_at');

        $rows = DB::query()
            ->fromSub($bills->unionAll($payments), 'ledger')
            ->orderBy('date')
            ->orderBy('created_at')
            ->orderBy('type_priority') // bills settle before payments on an exact tie
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
    public function openingBalance(int $vendorId, int $currencyId, string $date): float
    {
        $last = $this->ledger($vendorId, $currencyId)
            ->filter(fn ($row) => $row->date < $date)
            ->last();

        return $last ? (float) $last->accrual_balance : 0.00;
    }

    /**
     * Running balance as of the last transaction on or before $date.
     */
    public function closingBalance(int $vendorId, int $currencyId, string $date): float
    {
        $last = $this->ledger($vendorId, $currencyId)
            ->filter(fn ($row) => $row->date <= $date)
            ->last();

        return $last ? (float) $last->accrual_balance : 0.00;
    }

    /**
     * Statement activity rows between $from and $to (inclusive), each
     * carrying the running accrual_balance computed over the full history
     * (so it continues seamlessly from the opening balance).
     */
    public function activity(int $vendorId, int $currencyId, string $from, string $to): Collection
    {
        return $this->ledger($vendorId, $currencyId)
            ->filter(fn ($row) => $row->date >= $from && $row->date <= $to)
            ->values();
    }
}
