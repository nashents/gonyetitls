<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Billable flag on the Expense: a non-billable expense captured on a trip is
 * kept as a trip cost only - no supplier Bill (and no ledger entry) is raised
 * for it. E.g. a share of fuel already billed via the truck's fuel order.
 * Defaults to billable so existing behaviour is unchanged.
 */
class AddBillableToExpensesTable extends Migration
{
    public function up()
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->boolean('billable')->default(true)->after('visible_on_trip_sheet');
        });
    }

    public function down()
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('billable');
        });
    }
}
