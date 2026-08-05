<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSourceToRouteExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('route_expenses', function (Blueprint $table) {
            // null = manually added by a user, 'fuel' = auto-generated/synced from the route's fuel estimate
            $table->string('source')->nullable()->after('status');
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
            $table->dropColumn('source');
        });
    }
}
