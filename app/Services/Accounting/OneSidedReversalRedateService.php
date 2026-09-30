<?php

namespace App\Services\Accounting;

use App\Models\Company;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Makes the Trial Balance balance for date ranges that end in the past.
 *
 * The one-sided bill postings cleaned up by LedgerDoublePostingRepairService
 * (an Accounts Payable credit with no debit leg) were cancelled by REV-
 * reversals - but JournalReversalService dates a reversal the day it runs.
 * So a one-sided entry dated in August whose reversal landed in September
 * cancels out in a range that covers both, and throws the Trial Balance /
 * Balance Sheet out in any range that ends between the two.
 *
 * Fix: every reversal in such a chain (the one-sided original, its REV-, the
 * REV- of that, ...) is moved to the original's date, so the whole chain is
 * either inside a date range or outside it. Those entries should never have
 * been in the ledger, so there's no period they legitimately belong to.
 *
 * Only chains that fully cancel are touched. A chain that still nets to
 * something has a live one-sided entry - that's a real imbalance for
 * LedgerDoublePostingRepairService, not a dating problem. Reversals of
 * balanced entries are never touched: those stay dated the day they ran.
 *
 * Idempotent: a chain already on one date has nothing to move.
 */
class OneSidedReversalRedateService
{
    private const TOLERANCE = 0.005;

    public function run(bool $dryRun = false): array
    {
        $result = ['redated' => [], 'skipped' => []];

        foreach (Company::pluck('currency_id', 'id') as $companyId => $currencyId) {
            $entries = $this->entries((int) $companyId, (int) $currencyId);
            $byNumber = $entries->groupBy('journal_number');

            foreach ($this->chains($entries, $byNumber, $result) as $chain) {
                $root = $chain['root'];
                $rootDate = substr((string) $root->date, 0, 10);
                $move = $chain['members']->filter(fn ($e) => $e->id !== $root->id && substr((string) $e->date, 0, 10) !== $rootDate);

                if ($move->isEmpty()) {
                    continue;
                }

                $row = [
                    'company_id' => (int) $companyId,
                    'root' => $root->journal_number,
                    'reference' => $root->reference,
                    'date' => $rootDate,
                    'entries' => $move->pluck('journal_number')->all(),
                ];

                $net = $chain['members']->sum(fn ($e) => (float) $e->diff);

                if (abs($net) > self::TOLERANCE) {
                    $result['skipped'][] = $row + ['reason' => 'chain still nets to ' . number_format($net, 2) . ' - a one-sided entry is live'];
                    continue;
                }

                if (! $dryRun) {
                    DB::transaction(function () use ($move, $root, $rootDate) {
                        foreach ($move as $entry) {
                            $was = substr((string) $entry->date, 0, 10);

                            DB::table('journal_entries')->where('id', $entry->id)->update([
                                'date' => $rootDate,
                                'description' => trim($entry->description . " [re-dated from {$was} to the date of {$root->journal_number}, the one-sided entry it cancels]"),
                                'updated_at' => now(),
                            ]);
                        }
                    });
                }

                $result['redated'][] = $row;
            }
        }

        return $result;
    }

    /**
     * Every non-draft entry with its debit-minus-credit in the company's
     * reporting currency - the same amounts TrialBalanceCalculator sums.
     */
    private function entries(int $companyId, int $currencyId): Collection
    {
        $debit = "case when l.currency_id is null or l.currency_id = {$currencyId} then l.debit else l.exchange_debit end";
        $credit = "case when l.currency_id is null or l.currency_id = {$currencyId} then l.credit else l.exchange_credit end";

        return DB::table('journal_entries as je')
            ->join('journal_entry_lines as l', 'l.journal_entry_id', '=', 'je.id')
            ->where('je.company_id', $companyId)
            ->where('je.status', '!=', 'draft')
            ->groupBy('je.id', 'je.journal_number', 'je.date', 'je.reference', 'je.description')
            ->selectRaw("je.id, je.journal_number, je.date, je.reference, je.description, sum({$debit}) - sum({$credit}) as diff")
            ->get();
    }

    /**
     * Groups the one-sided entries by the original they trace back to
     * through their REV- references.
     */
    private function chains(Collection $entries, Collection $byNumber, array &$result): array
    {
        $chains = [];

        foreach ($entries as $entry) {
            if (abs((float) $entry->diff) <= self::TOLERANCE) {
                continue;
            }

            $root = $this->root($entry, $byNumber);

            if (! $root) {
                $result['skipped'][] = [
                    'root' => $entry->journal_number,
                    'reference' => $entry->reference,
                    'entries' => [$entry->journal_number],
                    'reason' => "couldn't trace its REV- chain to a single original",
                ];
                continue;
            }

            $chains[$root->id] ??= ['root' => $root, 'members' => collect()];
            $chains[$root->id]['members']->push($entry);
        }

        return $chains;
    }

    private function root(object $entry, Collection $byNumber): ?object
    {
        $hops = 0;

        while (str_starts_with((string) $entry->reference, 'REV-')) {
            $parents = $byNumber->get(substr($entry->reference, 4));

            if (! $parents || $parents->count() !== 1 || ++$hops > 50) {
                return null;
            }

            $entry = $parents->first();
        }

        return $entry;
    }
}
