<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDriverIdToRentalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rentals', function (Blueprint $table) {
            $table->bigInteger('driver_id')->nullable()->unsigned();
            $table->foreignId('currency_id')->constrained('currencies')->cascadeOnDelete();
            $table->bigInteger('account_id')->unsigned()->nullable();
            $table->bigInteger('tax_id')->unsigned()->nullable();
            $table->string('tax_rate')->nullable();
            $table->string('tax_amount')->nullable();
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
        Schema::table('rentals', function (Blueprint $table) {
            $table->dropColumn('driver_id');
            $table->dropForeign(['currency_id']);
            $table->dropColumn('currency_id');
            $table->dropColumn('account_id');
            $table->dropColumn('tax_id');
            $table->dropColumn('tax_rate');
            $table->dropColumn('tax_amount');
            $table->dropColumn('exchange_rate');
            $table->dropColumn('exchange_amount');
        });
    }
}
