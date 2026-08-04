<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReconciliationFieldsToJournalEntryLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('journal_entry_lines', function (Blueprint $table) {
            $table->foreignId('bank_reconciliation_id')->nullable()->after('exchange_rate')
                ->constrained()->nullOnDelete();
            // Set once this line is matched to a bank statement line; reversing/editing a
            // cleared line is blocked until the reconciliation it belongs to is reopened.
            $table->timestamp('cleared_at')->nullable()->after('bank_reconciliation_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('journal_entry_lines', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bank_reconciliation_id');
            $table->dropColumn('cleared_at');
        });
    }
}
