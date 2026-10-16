<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Accountant's rule: a reversal belongs in the period of the posting it
 * cancels - not only corrections (already re-dated) but removals and
 * deletions too, e.g. fuel bills CEB00291-297 removed on 8 Oct when their
 * orders became customer-supplied (the supplies posted in September) and the
 * deleted $11 bill TEB00310 of 30 Sep, all left in September's Trip Expense.
 * JournalReversalService now dates every new reversal that way; this
 * re-dates the existing ones (chain-aware). Idempotent.
 */
class RedateEveryReversalToOriginalPeriod extends Migration
{
    public function up()
    {
        Artisan::call('journals:redate-correction-reversals', ['--all' => true, '--since' => '2000-01-01']);
        echo Artisan::output();
    }

    public function down()
    {
        // Data repair - nothing to roll back.
    }
}
