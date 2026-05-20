<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJournalEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            // Source Documents
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('bill_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();

            // Optional manual journals
            $table->boolean('is_manual')->default(false);

            // Journal Info
            $table->string('journal_number')->unique();
            $table->date('date');

            $table->string('reference')->nullable();
            $table->text('description')->nullable();

            // Status Control
            $table->enum('status', [
                'draft',
                'posted',
                'reversed',
            ])->default('posted');

            // Audit Trail
            $table->foreignId('created_by_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('posted_by_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('posted_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
            $table->index('date');
            
            $table->index('status');
            $table->index('invoice_id');
            $table->index('bill_id');
            $table->index('payment_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('journal_entries');
    }
}
