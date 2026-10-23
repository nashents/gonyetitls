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
 * payments and approved debit notes reduce it) in chronological order.
 *
 * $party switches the counterparty: "vendor" (default) keys bills/payments
 * on vendor_id; "transporter" keys them on transporter_id (subcontracted
 * transporter bills/payments). Debit notes and supplier journals carry no
 * transporter_id, so they only apply to vendors.
 */
class VendorLedgerService
{
    /**
     * The full signed transaction history for a vendor + currency, ordered
     * chronologically. Each row carries enough detail to render a statement
     * line plus a running `accrual_balance`.
     */
    public const PARTY_VENDOR = 'vendor';
    public const PARTY_TRANSPORTER = 'transporter';

    public static function partyColumn(?string $party): string
    {
        return $party === self::PARTY_TRANSPORTER ? 'transporter_id' : 'vendor_id';
    }

    public function ledger(int $vendorId, int $currencyId, string $party = self::PARTY_VENDOR): Collection
    {
        $column = self::partyColumn($party);

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
            ->where($column, $vendorId)
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
            ->where($column, $vendorId)
            ->where('currency_id', $currencyId)
            ->whereNull('deleted_at');

        $debitNotes = DB::table('debit_notes')
            ->select([
                'id',
                DB::raw("'debit_note' as transaction_type"),
                'debit_note_number as number',
                'currency_id',
                'date',
                'date as transaction_date',
                'created_at',
                DB::raw('CAST(total AS DECIMAL(20,2)) as amount'),
                DB::raw('CAST(total AS DECIMAL(20,2)) as balance'),
                DB::raw('CAST(total AS DECIMAL(20,2)) * -1 as signed_amount'),
                DB::raw('1 as type_priority'),
            ])
            ->where('vendor_id', $vendorId)
            ->where('currency_id', $currencyId)
            ->where('authorization', 'approved')
            ->whereNull('deleted_at');

        // Suppliers journal adjustments (see SupplierJournalService) - a
        // credit raises what we owe the vendor, a debit reduces it. Voided
        // ones are excluded; their journal entry has been reversed.
        $supplierJournals = DB::table('supplier_journals')
            ->select([
                'id',
                DB::raw("'supplier_journal' as transaction_type"),
                'journal_number as number',
                'currency_id',
                'date',
                'date as transaction_date',
                'created_at',
                DB::raw('CAST(amount AS DECIMAL(20,2)) as amount'),
                DB::raw('CAST(amount AS DECIMAL(20,2)) as balance'),
                DB::raw("CASE WHEN type = 'debit' THEN CAST(amount AS DECIMAL(20,2)) * -1 ELSE CAST(amount AS DECIMAL(20,2)) END as signed_amount"),
                DB::raw("CASE WHEN type = 'debit' THEN 1 ELSE 0 END as type_priority"),
            ])
            ->where('vendor_id', $vendorId)
            ->where('currency_id', $currencyId)
            ->where('status', 'posted')
            ->whereNull('deleted_at');

        // Bills the customer settled for us (customer funded fuel / trip
        // expenses - CustomerFuelSupplyService): no cash, but they clear
        // what we owe the vendor like a payment does.
        $customerSettlements = DB::table('bill_payments')
            ->join('bills', 'bills.id', '=', 'bill_payments.bill_id')
            ->join('customer_fuel_supplies', 'customer_fuel_supplies.id', '=', 'bill_payments.customer_fuel_supply_id')
            ->select([
                'bill_payments.id',
                DB::raw("'customer_settlement' as transaction_type"),
                'customer_fuel_supplies.supply_number as number',
                'bills.currency_id',
                DB::raw('customer_fuel_supplies.date as date'),
                DB::raw('customer_fuel_supplies.date as transaction_date'),
                'bill_payments.created_at',
                DB::raw('CAST(bill_payments.amount AS DECIMAL(20,2)) as amount'),
                DB::raw('CAST(bill_payments.amount AS DECIMAL(20,2)) as balance'),
                DB::raw('CAST(bill_payments.amount AS DECIMAL(20,2)) * -1 as signed_amount'),
                DB::raw('1 as type_priority'),
            ])
            ->where('bill_payments.source', \App\Services\Accounting\CustomerFuelSupplyService::SOURCE)
            ->where("bills.{$column}", $vendorId)
            ->where('bills.currency_id', $currencyId)
            ->where('bills.authorization', 'approved')
            ->whereNull('bills.deleted_at')
            ->whereNull('bill_payments.deleted_at')
            ->whereNull('customer_fuel_supplies.deleted_at');

        $union = $bills->unionAll($payments)->unionAll($customerSettlements);
        if ($party !== self::PARTY_TRANSPORTER) {
            $union = $union->unionAll($debitNotes)->unionAll($supplierJournals);
        }

        $rows = DB::query()
            ->fromSub($union, 'ledger')
            ->orderBy('date')
            ->orderBy('created_at')
            ->orderBy('type_priority') // bills settle before payments/debit notes on an exact tie
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
    public function openingBalance(int $vendorId, int $currencyId, string $date, string $party = self::PARTY_VENDOR): float
    {
        $last = $this->ledger($vendorId, $currencyId, $party)
            ->filter(fn ($row) => $row->date < $date)
            ->last();

        return $last ? (float) $last->accrual_balance : 0.00;
    }

    /**
     * Running balance as of the last transaction on or before $date.
     */
    public function closingBalance(int $vendorId, int $currencyId, string $date, string $party = self::PARTY_VENDOR): float
    {
        $last = $this->ledger($vendorId, $currencyId, $party)
            ->filter(fn ($row) => $row->date <= $date)
            ->last();

        return $last ? (float) $last->accrual_balance : 0.00;
    }

    /**
     * Statement activity rows between $from and $to (inclusive), each
     * carrying the running accrual_balance computed over the full history
     * (so it continues seamlessly from the opening balance).
     */
    public function activity(int $vendorId, int $currencyId, string $from, string $to, string $party = self::PARTY_VENDOR): Collection
    {
        return $this->ledger($vendorId, $currencyId, $party)
            ->filter(fn ($row) => $row->date >= $from && $row->date <= $to)
            ->values();
    }
}
