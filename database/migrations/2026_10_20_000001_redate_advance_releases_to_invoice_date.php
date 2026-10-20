<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Advance invoice releases to Sales were dated the day they ran (trip
 * Offloaded / "Post to Sales"), not the invoice date - revenue in the wrong
 * month. AdvanceInvoiceReclassService now dates them on the invoice; this
 * re-dates the existing ones and their reversals. See RedateAdvanceReleases.
 */
class RedateAdvanceReleasesToInvoiceDate extends Migration
{
    public function up()
    {
        Artisan::call('ledger:redate-advance-releases');
        echo Artisan::output();
    }

    public function down()
    {
        // Data repair - nothing to roll back.
    }
}
