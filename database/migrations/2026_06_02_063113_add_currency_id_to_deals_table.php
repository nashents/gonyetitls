<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCurrencyIdToDealsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('deals', function (Blueprint $table) {
             $table->bigInteger('currency_id')->unsigned()->nullable();
             $table->decimal('rate','8','2')->nullable();
             $table->decimal('freight','8','2')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('deals', function (Blueprint $table) {
             $table->dropColumn('currency_id');
             $table->dropColumn('rate');
             $table->dropColumn('freight');
        });
    }
}
