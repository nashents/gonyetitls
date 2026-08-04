<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBankReconciliationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank_reconciliations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bank_account_id')->constrained()->cascadeOnDelete();
            // The Chart of Accounts "Cash & Bank" account this bank account is posted to.
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();

            $table->date('period_start');
            $table->date('period_end');

            $table->decimal('statement_closing_balance', 18, 2);
            $table->decimal('book_closing_balance', 18, 2)->nullable();
            $table->decimal('adjusted_bank_balance', 18, 2)->nullable();
            $table->decimal('adjusted_book_balance', 18, 2)->nullable();
            $table->decimal('difference', 18, 2)->default(0);

            $table->enum('status', ['in_progress', 'completed'])->default('in_progress');

            $table->foreignId('prepared_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['bank_account_id', 'period_start', 'period_end'], 'bank_recon_account_period_unique');
            $table->index(['bank_account_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bank_reconciliations');
    }
}
