<?php

namespace App\Services\Accounting;

use Illuminate\Support\Facades\DB;

/**
 * Identifies journal_entries that form a fully-cancelled reversal pair - a
 * reversed original plus the reversal that superseded it (see
 * JournalReversalService) - where BOTH sides of the pair fall inside a given
 * date range. Period reports can exclude these ids from their gross
 * Debit/Credit totals: the pair always nets to zero, so dropping both sides
 * never changes a net balance, only the noise in the gross columns.
 *
 * Deliberately scoped to pairs where both sides are within the range: if a
 * reversal falls outside the queried period (e.g. a correction made this
 * month for an entry originally posted last month), the reversed original
 * is left counted as-is for its own period - dropping only one side of a
 * pair would strand a one-sided balance, which is the exact bug the
 * `status != 'draft'` filter (see TrialBalanceCalculator and siblings) was
 * introduced to fix.
 */
class JournalEntryPairFilter
{
    public static function cancelledEntryIds(int $companyId, string $dateFrom, string $dateTo): array
    {
        $pairs = DB::table('journal_entries as orig')
            ->join('journal_entries as rev', function ($join) {
                $join->whereRaw("rev.reference = CONCAT('REV-', orig.journal_number)");
            })
            ->where('orig.company_id', $companyId)
            ->where('rev.company_id', $companyId)
            ->where('orig.status', 'reversed')
            ->where('rev.status', '!=', 'draft')
            ->whereBetween('orig.date', [$dateFrom, $dateTo])
            ->whereBetween('rev.date', [$dateFrom, $dateTo])
            ->select('orig.id as orig_id', 'rev.id as rev_id')
            ->get();

        return $pairs
            ->flatMap(fn ($pair) => [$pair->orig_id, $pair->rev_id])
            ->unique()
            ->values()
            ->all();
    }
}
