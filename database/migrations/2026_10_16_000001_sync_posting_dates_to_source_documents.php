<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Reposts any document whose ledger posting isn't dated like its origin
 * source document (e.g. a fuel order re-dated after approval: the resync
 * compared amounts only, so the posting stayed in the old period). Going
 * forward LedgerResyncService::isUnchanged() also compares the date. See
 * SyncPostingDates. Idempotent.
 */
class SyncPostingDatesToSourceDocuments extends Migration
{
    public function up()
    {
        Artisan::call('ledger:sync-posting-dates');
        echo Artisan::output();
    }

    public function down()
    {
        // Data repair - nothing to roll back.
    }
}
