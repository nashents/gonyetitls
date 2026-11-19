<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A vendor prepayment can be matched to a CREDIT suppliers journal (e.g. an
 * opening balance owed to the vendor) the same way it is matched to a bill:
 * a bill_payments row (source 'drawdown', bill_id null) pointing at the
 * journal it settles. Not supplier_journal_id - that column already means
 * "this debit journal is the money source".
 */
class AddSettledSupplierJournalIdToBillPaymentsTable extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('bill_payments', 'settled_supplier_journal_id')) {
            Schema::table('bill_payments', function (Blueprint $table) {
                $table->foreignId('settled_supplier_journal_id')->nullable()->after('bill_id')
                    ->constrained('supplier_journals')->nullOnDelete();
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('bill_payments', 'settled_supplier_journal_id')) {
            Schema::table('bill_payments', function (Blueprint $table) {
                $table->dropConstrainedForeignId('settled_supplier_journal_id');
            });
        }
    }
}
