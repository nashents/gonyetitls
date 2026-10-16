<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Freight invoice number(s) travelling with the cargo, typed by hand on the
 * transport order and shown in the manifest's "Invoice #(s)" column.
 *
 * Only transport_orders gets the column: trips is at MySQL's 65,535-byte row
 * limit (even a TEXT column is refused), and a trip without an attached order
 * always gets its own auto-created transport order, which stores the value.
 */
class AddInvoiceNumbersToTransportOrdersAndTrips extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('transport_orders', 'invoice_numbers')) {
            Schema::table('transport_orders', function (Blueprint $table) {
                $table->text('invoice_numbers')->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('transport_orders', 'invoice_numbers')) {
            Schema::table('transport_orders', function (Blueprint $table) {
                $table->dropColumn('invoice_numbers');
            });
        }
    }
}
