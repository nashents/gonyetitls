<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A customer deposit can be matched to a DEBIT debtors journal (e.g. an
 * opening balance) the same way it is matched to an invoice: an
 * invoice_payments row (source 'drawdown', invoice_id null) pointing at the
 * journal it settles. Not debtor_journal_id - that column already means
 * "this credit journal is the money source".
 */
class AddSettledDebtorJournalIdToInvoicePaymentsTable extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('invoice_payments', 'settled_debtor_journal_id')) {
            Schema::table('invoice_payments', function (Blueprint $table) {
                $table->foreignId('settled_debtor_journal_id')->nullable()->after('invoice_id')
                    ->constrained('debtor_journals')->nullOnDelete();
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('invoice_payments', 'settled_debtor_journal_id')) {
            Schema::table('invoice_payments', function (Blueprint $table) {
                $table->dropConstrainedForeignId('settled_debtor_journal_id');
            });
        }
    }
}
