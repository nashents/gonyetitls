<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReversalColumnsToGoodsReceivedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('goods_receiveds', function (Blueprint $table) {
            $table->bigInteger('reversed_by_id')->unsigned()->nullable();
            $table->foreign('reversed_by_id')->references('id')->on('users')->onDelete('set null');
            $table->timestamp('reversed_at')->nullable();
            $table->text('reversal_comments')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('goods_receiveds', function (Blueprint $table) {
            $table->dropForeign(['reversed_by_id']);
            $table->dropColumn(['reversed_by_id', 'reversed_at', 'reversal_comments']);
        });
    }
}
