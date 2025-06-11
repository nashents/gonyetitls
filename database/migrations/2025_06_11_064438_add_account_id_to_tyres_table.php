<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccountIdToTyresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tyres', function (Blueprint $table) {
            $table->bigInteger('account_id')->unsigned()->nullable();
            $table->bigInteger('account_type_id')->unsigned()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tyres', function (Blueprint $table) {
            $table->dropColumn('account_id');
            $table->dropColumn('account_type_id');
        });
    }
}
