<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFuelEstimateFieldsToRoutesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->decimal('fuel_consumption_rate', 8, 2)->nullable()->after('distance');
            $table->decimal('fuel_price_per_litre', 10, 2)->nullable()->after('fuel_consumption_rate');
            $table->bigInteger('fuel_currency_id')->unsigned()->nullable()->after('fuel_price_per_litre');
            $table->foreign('fuel_currency_id')->references('id')->on('currencies')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->dropForeign(['fuel_currency_id']);
            $table->dropColumn(['fuel_consumption_rate', 'fuel_price_per_litre', 'fuel_currency_id']);
        });
    }
}
