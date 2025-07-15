<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCurrencyIdToRequisitionItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('requisition_items', function (Blueprint $table) {
            $table->bigInteger('currency_id')->unsigned()->nullable();
            $table->string('exchange_rate')->nullable();
            $table->string('exchange_amount')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('requisition_items', function (Blueprint $table) {
            $table->dropColumn('currency_id');
            $table->dropColumn('exchange_rate');
            $table->dropColumn('exchange_amount');
        });
    }
}
