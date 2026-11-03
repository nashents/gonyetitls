<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * E-signatures captured on generated trip documents (first: the Delivery Note).
 * One row per trip + document + signing role; the signature is the PNG data URL
 * drawn on screen, so the printed/PDF copy carries it without any file storage.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('trip_document_signatures')) {
            return;
        }

        Schema::create('trip_document_signatures', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('trip_id')->unsigned();
            $table->foreign('trip_id')->references('id')->on('trips')->onDelete('cascade');
            $table->string('document', 50);
            $table->string('role', 50);
            $table->string('signer_name');
            $table->string('signer_designation')->nullable();
            $table->longText('signature');
            $table->timestamp('signed_at')->nullable();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->unique(['trip_id', 'document', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_document_signatures');
    }
};
