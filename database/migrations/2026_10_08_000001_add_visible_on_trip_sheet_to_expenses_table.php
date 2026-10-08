<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Trip sheet visibility moves from each trip expense row to the Expense
 * itself, so it is set once and applies to every trip sheet. Backfill: an
 * expense whose existing trip expense rows were all hidden starts hidden.
 */
class AddVisibleOnTripSheetToExpensesTable extends Migration
{
    public function up()
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->boolean('visible_on_trip_sheet')->default(true)->after('status');
        });

        $hidden = DB::table('trip_expenses')
            ->whereNotNull('expense_id')
            ->whereNull('deleted_at')
            ->groupBy('expense_id')
            ->havingRaw('SUM(CASE WHEN visible_on_trip_sheet = 1 THEN 1 ELSE 0 END) = 0')
            ->pluck('expense_id');

        if ($hidden->isNotEmpty()) {
            DB::table('expenses')->whereIn('id', $hidden)->update(['visible_on_trip_sheet' => false]);
        }
    }

    public function down()
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('visible_on_trip_sheet');
        });
    }
}
