<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDepartmentToGoodsReturnedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('goods_returneds', function (Blueprint $table) {
            $table->string('department')->nullable();
            $table->string('goods_returned_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('goods_returneds', function (Blueprint $table) {
           $table->dropColumn('department');
           $table->dropColumn('goods_returned_number');
        });
    }
}
