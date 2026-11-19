<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Chain of custody for uploaded documents (permits etc.) - the digital
 * replacement for the paper receipt book. One row per hand-over: the giver
 * signs it out (condition, optional scan), the recipient confirms receipt
 * (condition, optional new scan carrying the stamps added at their desk).
 * Every scan uploaded along the way is a version of the document.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('document_movements')) {
            return;
        }

        Schema::create('document_movements', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('document_id')->unsigned();
            $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');

            // pending (in transit) | received | rejected
            $table->string('status', 20)->default('pending');

            // Hand-over side (giver signs out)
            $table->bigInteger('from_user_id')->unsigned()->nullable();
            $table->string('from_name')->nullable();
            $table->timestamp('handed_over_at')->nullable();
            $table->string('handover_condition', 50)->nullable();
            $table->text('handover_notes')->nullable();
            $table->string('handover_file')->nullable();
            $table->longText('handover_signature')->nullable();

            // Recipient
            $table->bigInteger('to_user_id')->unsigned()->nullable();
            $table->string('to_name')->nullable();
            $table->string('location')->nullable();
            $table->string('purpose')->nullable();

            // Receipt side (recipient signs in)
            $table->timestamp('received_at')->nullable();
            $table->bigInteger('received_by')->unsigned()->nullable();
            $table->string('receipt_condition', 50)->nullable();
            $table->text('receipt_notes')->nullable();
            $table->string('receipt_file')->nullable();
            $table->longText('receipt_signature')->nullable();
            // documents.filename before this receipt's scan replaced it
            $table->string('replaced_filename')->nullable();

            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->timestamps();

            $table->index(['document_id', 'status']);
            $table->index(['to_user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_movements');
    }
};
