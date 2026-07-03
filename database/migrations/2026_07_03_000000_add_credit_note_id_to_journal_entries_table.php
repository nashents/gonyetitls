<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreditNoteIdToJournalEntriesTable extends Migration
{
    public function up()
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->foreignId('credit_note_id')->nullable()->after('payroll_run_id')->constrained()->nullOnDelete();
            $table->index('credit_note_id');
        });
    }

    public function down()
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('credit_note_id');
        });
    }
}
