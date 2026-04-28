<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBoeToTripTransportOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trip_transport_orders', function (Blueprint $table) {
             $table->string('bill_of_entry')->nullable();
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
            $table->dropColumn('bill_of_entry');
        });
    }
}
