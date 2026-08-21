<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddContainerIdToJournalEntryLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('journal_entry_lines', function (Blueprint $table) {
            $table->foreignId('container_id')
                ->nullable()
                ->after('trailer_id')
                ->constrained()
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('journal_entry_lines', function (Blueprint $table) {
            $table->dropConstrainedForeignId('container_id');
        });
    }
}
