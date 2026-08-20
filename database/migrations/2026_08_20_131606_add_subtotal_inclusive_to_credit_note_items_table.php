<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSubtotalInclusiveToCreditNoteItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // The original create_credit_note_items_table migration has declared
        // this column since the first commit in this repo, but some deployed
        // databases never actually got it (their `migrations` table marks
        // that migration as already run without the column existing). Guard
        // with hasColumn so this is a safe no-op wherever it already exists.
        if (!Schema::hasColumn('credit_note_items', 'subtotal_inclusive')) {
            Schema::table('credit_note_items', function (Blueprint $table) {
                $table->string('subtotal_inclusive')->nullable()->after('subtotal');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
