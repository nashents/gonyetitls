<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBankReconciliationItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank_reconciliation_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('bank_reconciliation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bank_statement_line_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('journal_entry_line_id')->nullable()->constrained()->nullOnDelete();

            // matched: cleared on both sides. outstanding_book_item: posted in the ledger,
            // not yet on the bank statement (e.g. outstanding cheque/deposit in transit).
            // uncleared_bank_item: on the statement, not yet posted to the ledger (e.g. bank
            // charges/interest) at the time the reconciliation was completed. adjustment: the
            // journal entry created to record an uncleared_bank_item.
            $table->enum('item_type', ['matched', 'outstanding_book_item', 'uncleared_bank_item', 'adjustment']);

            $table->decimal('amount', 18, 2);
            $table->text('description')->nullable();
            $table->timestamp('cleared_at')->nullable();

            $table->timestamps();

            $table->index(['bank_reconciliation_id', 'item_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bank_reconciliation_items');
    }
}
