<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJournalEntryLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('journal_entry_lines', function (Blueprint $table) {
            $table->id();

            $table->foreignId('journal_entry_id')
                ->constrained()
                ->cascadeOnDelete();

            // Chart of account
            $table->foreignId('account_id')
                ->constrained()
                ->cascadeOnDelete();

            // Optional dimensions
            $table->foreignId('branch_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('customer_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('vendor_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('horse_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('employee_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('driver_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('transporter_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('vehicle_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('trailer_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // Entry Details
            $table->text('description')->nullable();

            $table->decimal('debit', 18, 2)->default(0);
            $table->decimal('exchange_debit', 18, 2)->default(0);
            $table->decimal('credit', 18, 2)->default(0);
            $table->decimal('exchange_credit', 18, 2)->default(0);

            // Currency Support
            $table->foreignId('currency_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->decimal('exchange_rate', 18, 6)
                ->default(1);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('journal_entry_lines');
    }
}
