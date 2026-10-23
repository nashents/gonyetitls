<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Customer-funded fuel / trip expenses posted before 2026-10-23 have no
 * supplier bill. Raises it (settled by the customer) for each existing
 * supply - net ledger unchanged, invoice allocations kept. Idempotent; a
 * supply that fails is reported and left as it was (the command can be
 * re-run by hand: php artisan customer-supplies:raise-supplier-bills).
 */
class RaiseCustomerSupplySupplierBills extends Migration
{
    public function up()
    {
        Artisan::call('customer-supplies:raise-supplier-bills');
        echo Artisan::output();
    }

    public function down()
    {
        // Data repair - nothing to roll back.
    }
}
