<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDispatchItemIdToTicketInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ticket_inventories', function (Blueprint $table) {
            $table->bigInteger('dispatch_id')->nullable();
            $table->bigInteger('dispatch_item_id')->nullable();
          
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ticket_inventories', function (Blueprint $table) {
              $table->dropColumn('dispatch_id');
              $table->dropColumn('dispatch_item_id');
        });
    }
}
