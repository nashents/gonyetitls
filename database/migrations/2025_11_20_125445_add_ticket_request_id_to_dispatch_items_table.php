<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTicketRequestIdToDispatchItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dispatch_items', function (Blueprint $table) {
              $table->bigInteger('ticket_request_id')->nullable()->unsigned();
              $table->string('qty')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dispatch_items', function (Blueprint $table) {
                $table->dropColumn('ticket_request_id');
                $table->dropColumn('qty');
        });
    }
}
