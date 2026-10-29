<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Control accounts are balanced by their own sub-ledger documents (invoices,
 * bills, GRVs, trip expenses, advance invoices...), so manual entry screens
 * must not post to them. accounts.is_control existed but was never set.
 * Idempotent - safe to re-run from Data Reset.
 */
class FlagControlAccounts extends Migration
{
    const CONTROL_ACCOUNTS = [
        'Accounts Receivable',
        'Accounts Payable',
        'Customer Deposits',
        'Customer Advances',
        'Vendor Prepayments',
        'Fuel Inventory',
        'Spares Inventory',
        'Stock Write-off',
        'Transporter Recoverable',
        'Customer Recoverable',
    ];

    public function up()
    {
        DB::table('accounts')->whereIn('name', self::CONTROL_ACCOUNTS)->update(['is_control' => 1]);
    }

    public function down()
    {
        DB::table('accounts')->whereIn('name', self::CONTROL_ACCOUNTS)->update(['is_control' => 0]);
    }
}
