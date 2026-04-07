<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDispatchIdToMovementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('movements', function (Blueprint $table) {
            $table->bigInteger('ticket_id')->unsigned()->nullable();
            $table->bigInteger('dispatch_id')->unsigned()->nullable();
            $table->bigInteger('dispatch_item_id')->unsigned()->nullable();
            $table->bigInteger('product_id')->unsigned()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('movements', function (Blueprint $table) {
             $table->dropColumn([
                'ticket_id',
                'dispatch_id',
                'dispatch_item_id',
                'product_id',
            ]);
        });
    }
}
