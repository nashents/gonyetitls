<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAuthorizationToGoodsReceivedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('goods_receiveds', function (Blueprint $table) {
            $table->bigInteger('authorized_by_id')->nullable();
            $table->string('authorization')->default('approved');
            $table->text('authorization_comments')->nullable();
            $table->string('authorization_date')->nullable();
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
            $table->dropColumn(['authorized_by_id']);
            $table->dropColumn('authorization');
            $table->dropColumn('authorization_comments');
            $table->dropColumn('authorization_date');
        });
    }
}
