<?php

namespace App\Services\Accounting;

use Illuminate\Support\Facades\DB;

/**
 * Identifies journal_entries that form a fully-cancelled reversal pair - a
 * reversed original plus the reversal that superseded it (see
 * JournalReversalService) - where BOTH sides of the pair fall inside a given
 * date range. Period reports can exclude these ids from their gross
 * Debit/Credit totals: the pair always nets to zero, so dropping both sides
 * never changes a net balance, only the noise in the gross columns - as long
 * as each entry is dropped with exactly one partner (see the chain note in
 * cancelledEntryIds()).
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
            ->orderBy('orig.id')
            ->orderBy('rev.id')
            ->get();

        // Each entry may cancel at most one other. In a chain X -> REV-X ->
        // REV-REV-X (a reversal itself reversed, e.g. unreverse() restoring a
        // deleted document) the middle entry matches two pairs; dropping all
        // three would also drop REV-REV-X's genuine effect and understate the
        // net balance. Walking in id order pairs X with REV-X and leaves
        // REV-REV-X counted (or pairs it with its own reversal further on).
        $excluded = [];
        foreach ($pairs as $pair) {
            if (isset($excluded[$pair->orig_id]) || isset($excluded[$pair->rev_id])) {
                continue;
            }
            $excluded[$pair->orig_id] = true;
            $excluded[$pair->rev_id] = true;
        }

        return array_keys($excluded);
    }
}
