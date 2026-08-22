<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTransferFieldsToPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            // The destination leg of an inter-account transfer (category =
            // 'transfer'). account_id/amount/currency_id/exchange_rate carry
            // the source leg (unchanged, same columns every other payment
            // category already uses); these carry the destination leg,
            // which may be a different account in a different currency.
            $table->bigInteger('to_account_id')->unsigned()->nullable();
            $table->string('to_amount')->nullable();
            $table->bigInteger('to_currency_id')->unsigned()->nullable();
            $table->string('to_exchange_rate')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['to_account_id', 'to_amount', 'to_currency_id', 'to_exchange_rate']);
        });
    }
}
