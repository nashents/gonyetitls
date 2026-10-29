<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Deposits/withdrawals from the Transactions screen stored their contra
 * account by NAME (payments.transaction_category), so two accounts sharing a
 * name could post to the wrong one. contra_account_id pins the exact account.
 * Existing rows are backfilled from the non-cash line their live journal
 * actually posted to.
 */
class AddContraAccountIdToPayments extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('payments', 'contra_account_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->unsignedBigInteger('contra_account_id')->nullable()->after('to_account_id');
                $table->index('contra_account_id');
            });
        }

        $rows = DB::table('payments')
            ->join('journal_entries', 'journal_entries.payment_id', '=', 'payments.id')
            ->join('journal_entry_lines', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->whereIn('payments.category', ['deposit', 'withdrawal'])
            ->whereNull('payments.contra_account_id')
            ->where('journal_entries.status', '!=', 'reversed')
            ->where(fn ($q) => $q->whereNull('journal_entries.reference')->orWhere('journal_entries.reference', 'not like', 'REV-%'))
            ->whereColumn('journal_entry_lines.account_id', '!=', 'payments.account_id')
            ->select('payments.id', 'journal_entry_lines.account_id')
            ->get();

        foreach ($rows->groupBy('id') as $paymentId => $lines) {
            DB::table('payments')->where('id', $paymentId)->update(['contra_account_id' => $lines->first()->account_id]);
        }
    }

    public function down()
    {
        if (Schema::hasColumn('payments', 'contra_account_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropIndex(['contra_account_id']);
                $table->dropColumn('contra_account_id');
            });
        }
    }
}
