<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaidCurrencyFieldsToPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            // An invoice settled in a currency other than its own (category =
            // 'invoice'). currency_id/amount keep carrying what was applied
            // to the invoice, in the invoice's currency - so statements,
            // invoice balances and every other receivables report stay in
            // that currency untouched. These carry what actually landed in
            // the cash/bank account; paid_exchange_rate converts paid_amount
            // to the company's reporting currency. All null for an ordinary
            // same-currency payment.
            $table->bigInteger('paid_currency_id')->unsigned()->nullable();
            $table->string('paid_amount')->nullable();
            $table->string('paid_exchange_rate')->nullable();
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
            $table->dropColumn(['paid_currency_id', 'paid_amount', 'paid_exchange_rate']);
        });
    }
}
