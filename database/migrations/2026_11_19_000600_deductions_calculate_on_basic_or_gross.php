<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Percentage deductions are now worked out on Basic or Gross. "Net" can't be
 * a base (net depends on the deductions themselves), so it - and blanks -
 * become Gross. Data only; the column is unchanged.
 */
class DeductionsCalculateOnBasicOrGross extends Migration
{
    public function up()
    {
        DB::table('deductions')
            ->where(fn ($q) => $q->whereNull('calculate_on')->orWhereNotIn('calculate_on', ['basic', 'gross']))
            ->update(['calculate_on' => 'gross']);
    }

    public function down()
    {
        // Not reversible: the old "net" setting was never used in any calculation.
    }
}
