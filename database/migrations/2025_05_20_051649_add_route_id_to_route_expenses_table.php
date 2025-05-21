<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRouteIdToRouteExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('route_expenses', function (Blueprint $table) {
             $table->bigInteger('route_id')->unsigned()->nullable();
             $table->foreign('route_id')->references('id')->on('routes')->onDelete('cascade');
             $table->boolean('status')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('route_expenses', function (Blueprint $table) {
            $table->dropForeign(['route_id']); // drops the foreign key constraint
            $table->dropColumn('route_id');
            $table->dropColumn('status');
        });
    }
}
