<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Links an invoice line to a whole Transport Order (the "Transport Order" invoice
 * source produces a single freight line). Lets a trip be recognised as invoiced
 * when the transport order it belongs to has been invoiced.
 */
class AddTransportOrderIdToInvoiceItemsTable extends Migration
{
    public function up()
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            if (! Schema::hasColumn('invoice_items', 'transport_order_id')) {
                $table->unsignedBigInteger('transport_order_id')->nullable()->after('trip_transport_order_id');
            }
        });
    }

    public function down()
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            if (Schema::hasColumn('invoice_items', 'transport_order_id')) {
                $table->dropColumn('transport_order_id');
            }
        });
    }
}
