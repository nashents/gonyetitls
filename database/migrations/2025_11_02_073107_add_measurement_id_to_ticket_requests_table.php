<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMeasurementIdToTicketRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ticket_requests', function (Blueprint $table) {
            $table->bigInteger('measurement_id')->nullable()->unsigned();
            $table->foreign('measurement_id')->references('id')->on('measurements');
            $table->bigInteger('product_id')->nullable()->unsigned();
            $table->foreign('product_id')->references('id')->on('products');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ticket_requests', function (Blueprint $table) {
             $table->dropForeign(['measurement_id']); // correct way
             $table->dropColumn('measurement_id');
             $table->dropForeign(['product_id']); // correct way
             $table->dropColumn('product_id');
        });
    }
}
