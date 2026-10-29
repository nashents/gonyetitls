<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A fuel order can be split across trips: each fuel-linked trip expense is one
 * trip's allocation, and trip_expenses.quantity holds its litres. Existing
 * fuel trip expenses carried the whole order, so they get the order's quantity.
 */
class AddQuantityToTripExpenses extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('trip_expenses', 'quantity')) {
            Schema::table('trip_expenses', function (Blueprint $table) {
                $table->decimal('quantity', 14, 2)->nullable()->after('fuel_id');
            });
        }

        DB::table('trip_expenses')
            ->join('fuels', 'fuels.id', '=', 'trip_expenses.fuel_id')
            ->whereNull('trip_expenses.quantity')
            ->whereRaw("fuels.quantity REGEXP '^[0-9]+(\\.[0-9]+)?$'")
            ->update(['trip_expenses.quantity' => DB::raw('fuels.quantity')]);
    }

    public function down()
    {
        if (Schema::hasColumn('trip_expenses', 'quantity')) {
            Schema::table('trip_expenses', function (Blueprint $table) {
                $table->dropColumn('quantity');
            });
        }
    }
}
