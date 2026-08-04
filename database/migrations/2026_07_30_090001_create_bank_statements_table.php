<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBankStatementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank_statements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bank_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('currency_id')->nullable()->constrained()->nullOnDelete();

            $table->string('file_name')->nullable();
            $table->enum('file_format', ['csv', 'ofx', 'mt940'])->default('csv');

            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();

            $table->decimal('opening_balance', 18, 2)->nullable();
            $table->decimal('closing_balance', 18, 2)->nullable();

            $table->foreignId('imported_by_id')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('status', ['processing', 'completed', 'failed'])->default('processing');
            $table->unsignedInteger('imported_count')->default(0);
            $table->unsignedInteger('duplicate_count')->default(0);
            $table->text('error_message')->nullable();

            $table->timestamps();
            $table->softDeletes();

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
        Schema::dropIfExists('bank_statements');
    }
}
