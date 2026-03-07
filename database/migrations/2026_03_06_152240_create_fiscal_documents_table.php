<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFiscalDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fiscal_documents', function (Blueprint $table) {
            $table->id();
            $table->string('document_type')->default('invoice'); // invoice|credit_note
            $table->unsignedBigInteger('source_id')->nullable(); // your local invoice/credit note id
            $table->uuid('external_document_id')->unique(); // InvoiceId / CreditNoteId sent to Fiscal Harmony
            $table->string('document_number')->nullable();
            $table->string('request_id')->nullable()->index(); // returned by /invoice or /creditnote
            $table->boolean('success')->nullable();
            $table->boolean('is_actionable')->nullable();
            $table->text('error_message')->nullable();
            $table->string('fiscal_invoice_pdf')->nullable();
            $table->string('verification_code')->nullable();
            $table->string('qr_code_url')->nullable();
            $table->integer('fiscal_day')->nullable();
            $table->integer('device_id')->nullable();
            $table->string('ra_invoice_number')->nullable();
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->timestamp('fiscalized_at')->nullable();
            $table->timestamps();

            $table->index(['document_type', 'source_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fiscal_documents');
    }
}
