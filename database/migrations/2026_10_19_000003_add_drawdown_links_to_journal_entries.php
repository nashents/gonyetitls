<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

/**
 * Applying a customer deposit to an invoice (invoice_payments source
 * 'drawdown') or a vendor prepayment to a bill (bill_payments source
 * 'drawdown') posted nothing: Customer Deposits / Vendor Prepayments never
 * cleared and AR / AP stayed at the full invoice/bill. DrawdownJournalService
 * now posts DR Customer Deposits / CR AR and DR AP / CR Vendor Prepayments,
 * linked to the allocation row so removing it reverses the entry. Backfills
 * any existing allocations (none on COMDELTIC at the time).
 */
class AddDrawdownLinksToJournalEntries extends Migration
{
    public function up()
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            if (! Schema::hasColumn('journal_entries', 'invoice_payment_id')) {
                $table->unsignedBigInteger('invoice_payment_id')->nullable()->index()->after('payment_id');
            }
            if (! Schema::hasColumn('journal_entries', 'bill_payment_id')) {
                $table->unsignedBigInteger('bill_payment_id')->nullable()->index()->after('invoice_payment_id');
            }
        });

        Artisan::call('ledger:post-drawdowns');
        echo Artisan::output();
    }

    public function down()
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropColumn(['invoice_payment_id', 'bill_payment_id']);
        });
    }
}
