<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDebitNoteIdToJournalEntriesTable extends Migration
{
    public function up()
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->foreignId('debit_note_id')->nullable()->after('credit_note_id')->constrained()->nullOnDelete();
            $table->index('debit_note_id');
        });
    }

    public function down()
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('debit_note_id');
        });
    }
}
