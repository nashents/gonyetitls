<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CleanStaleTripExpenseExchangeRates extends Migration
{
    /**
     * Nulls out exchange_rate/exchange_amount on trip_expenses whose currency
     * matches their trip's company currency. These rows were left over from
     * the UI briefly showing a Conversion Rate field for a foreign currency
     * that was then switched back to the company currency, without the
     * stale rate/amount being cleared (fixed in App\Http\Livewire\Trips\Expenses).
     *
     * @return void
     */
    public function up()
    {
        DB::table('trip_expenses as te')
            ->join('trips as t', 't.id', '=', 'te.trip_id')
            ->join('companies as c', 'c.id', '=', 't.company_id')
            ->whereNull('te.deleted_at')
            ->whereNotNull('te.exchange_rate')
            ->whereColumn('te.currency_id', 'c.currency_id')
            ->update([
                'te.exchange_rate' => null,
                'te.exchange_amount' => null,
            ]);
    }

    /**
     * This is a data cleanup for corrupt values; there is nothing to revert to.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
