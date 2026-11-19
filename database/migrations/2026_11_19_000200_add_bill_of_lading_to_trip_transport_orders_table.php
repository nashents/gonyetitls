<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bill of Lading # captured on Trip Create/Edit. Stored on the trip's
 * trip_transport_orders rows because the trips table is at MySQL's row-size limit.
 */
class AddBillOfLadingToTripTransportOrdersTable extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('trip_transport_orders', 'bill_of_lading')) {
            Schema::table('trip_transport_orders', function (Blueprint $table) {
                $table->string('bill_of_lading')->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('trip_transport_orders', 'bill_of_lading')) {
            Schema::table('trip_transport_orders', function (Blueprint $table) {
                $table->dropColumn('bill_of_lading');
            });
        }
    }
}
