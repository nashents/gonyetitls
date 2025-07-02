<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCurrencyIdToDispatchItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dispatch_items', function (Blueprint $table) {
               $table->bigInteger('currency_id')->nullable();
               $table->string('amount')->nullable();
               $table->string('exchange_amount')->nullable();
               $table->string('exchange_rate')->nullable();
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
            $table->dropColumn('currency_id');
            $table->dropColumn('amount');
            $table->dropColumn('exchange_amount');
            $table->dropColumn('exchange_rate');
        });
    }
}
