<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAllocatedFreightToTripTransportOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trip_transport_orders', function (Blueprint $table) {
           $table->string('allocated_rate')->nullable();
           $table->string('allocated_freight')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trip_transport_orders', function (Blueprint $table) {
            $table->dropColumn('allocated_rate');
            $table->dropColumn('allocated_freight');
        });
    }
}
