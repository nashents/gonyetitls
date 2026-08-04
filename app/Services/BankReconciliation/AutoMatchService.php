<?php

namespace App\Services\BankReconciliation;

use App\Models\BankStatementLine;
use App\Models\JournalEntryLine;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Matches unmatched bank statement lines to uncleared ledger lines on the
 * same GL account by exact amount and a tolerance window on the date -
 * mirrors how accountants eyeball a statement against the cash book before
 * resorting to manual matching for anything ambiguous.
 */
class AutoMatchService
{
    public function __construct(private int $dateToleranceDays = 5)
    {
    }

    /** @return array{matched: int, statement_lines: int} */
    public function match(int $bankAccountId, int $accountId): array
    {
        $statementLines = BankStatementLine::where('bank_account_id', $bankAccountId)
            ->where('status', 'unmatched')
            ->orderBy('transaction_date')
            ->get();

        $candidates = JournalEntryLine::query()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('journal_entry_lines.account_id', $accountId)
            ->where('journal_entries.status', 'posted')
            ->whereNull('journal_entry_lines.cleared_at')
            ->select('journal_entry_lines.*', 'journal_entries.date as entry_date')
            ->get()
            ->keyBy('id');

        $matched = 0;

        foreach ($statementLines as $line) {
            $amount = (float) $line->credit - (float) $line->debit;

            $best = null;
            $bestDiffDays = null;

            foreach ($candidates as $candidate) {
                $candidateAmount = (float) $candidate->debit - (float) $candidate->credit;
                if (abs($candidateAmount - $amount) > 0.005) {
                    continue;
                }

                $diffDays = abs(Carbon::parse($candidate->entry_date)->diffInDays(Carbon::parse($line->transaction_date)));
                if ($diffDays > $this->dateToleranceDays) {
                    continue;
                }

                if ($best === null || $diffDays < $bestDiffDays) {
                    $best = $candidate;
                    $bestDiffDays = $diffDays;
                }
            }

            if ($best) {
                DB::transaction(function () use ($line, $best) {
                    $line->status = 'matched';
                    $line->matched_journal_entry_line_id = $best->id;
                    $line->matched_at = now();
                    $line->matched_by_id = Auth::id();
                    $line->save();

                    $best->cleared_at = now();
                    $best->save();
                });

                $candidates->forget($best->id);
                $matched++;
            }
        }

        return ['matched' => $matched, 'statement_lines' => $statementLines->count()];
    }
}
