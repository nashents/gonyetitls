<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Approved invoices edited after approval kept their old posting (COMDELTIC:
 * advance invoices CEI00001-4 left AR / Customer Advances 15-20k off each,
 * 38k stranded in Customer Advances after release; CEI00020, CEI00035).
 * InvoiceObserver now reposts on edit; this reposts the existing ones and
 * redoes advance releases on their original dates. Idempotent.
 */
class ResyncStaleInvoices extends Migration
{
    public function up()
    {
        Artisan::call('ledger:resync-stale-invoices');
        echo Artisan::output();
    }

    public function down()
    {
        // Data repair - nothing to roll back.
    }
}
