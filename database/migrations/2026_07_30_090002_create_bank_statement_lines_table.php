<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBankStatementLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank_statement_lines', function (Blueprint $table) {
            $table->id();

            $table->foreignId('bank_statement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bank_account_id')->constrained()->cascadeOnDelete();

            $table->date('transaction_date');
            $table->date('value_date')->nullable();
            $table->text('description')->nullable();
            $table->string('reference')->nullable();

            // The bank's own transaction id when available (e.g. OFX FITID, MT940 reference).
            $table->string('external_ref')->nullable();

            $table->decimal('debit', 18, 2)->default(0);
            $table->decimal('credit', 18, 2)->default(0);
            $table->decimal('balance', 18, 2)->nullable();

            // sha1(bank_account_id + date + amount + description) - prevents re-importing the same line twice.
            $table->string('dedupe_hash', 64);

            $table->enum('status', ['unmatched', 'matched', 'reconciled', 'ignored'])->default('unmatched');

            $table->foreignId('matched_journal_entry_line_id')->nullable()->constrained('journal_entry_lines')->nullOnDelete();
            $table->timestamp('matched_at')->nullable();
            $table->foreignId('matched_by_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->unique(['bank_account_id', 'dedupe_hash']);
            $table->index(['bank_account_id', 'status']);
            $table->index('transaction_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bank_statement_lines');
    }
}
