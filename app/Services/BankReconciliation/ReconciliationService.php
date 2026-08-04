<?php

namespace App\Services\BankReconciliation;

use App\Models\Account;
use App\Models\BankAccount;
use App\Models\BankReconciliation;
use App\Models\BankReconciliationItem;
use App\Models\BankStatementLine;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Drives a bank reconciliation the standard way: open a period against a
 * statement closing balance, auto-match what can be matched, let the user
 * manually match or record adjustment entries (bank charges, interest, etc.)
 * for the rest, then complete once:
 *
 *   Adjusted bank balance = statement balance + outstanding book items
 *   Adjusted book balance = book balance (once every bank-only item has
 *                            been journalized via createAdjustmentEntry)
 *
 * and the two agree.
 */
class ReconciliationService
{
    public function __construct(
        private BookBalanceCalculator $bookBalance,
        private AutoMatchService $autoMatch,
    ) {
    }

    public function start(BankAccount $bankAccount, string $periodStart, string $periodEnd, float $statementClosingBalance): BankReconciliation
    {
        $account = Account::where('bank_account_id', $bankAccount->id)->first();
        if (!$account) {
            throw new \RuntimeException('This bank account has no linked GL account yet - run bank-accounts:link-gl-accounts or edit and re-save it.');
        }

        $reconciliation = BankReconciliation::create([
            'company_id'                => $bankAccount->company_id,
            'bank_account_id'           => $bankAccount->id,
            'account_id'                => $account->id,
            'period_start'              => $periodStart,
            'period_end'                => $periodEnd,
            'statement_closing_balance' => $statementClosingBalance,
            'book_closing_balance'      => $this->bookBalance->balanceAsOf($account->id, $periodEnd),
            'status'                    => 'in_progress',
            'prepared_by_id'            => Auth::id(),
        ]);

        $this->autoMatch->match($bankAccount->id, $account->id);

        return $reconciliation;
    }

    public function matchLine(BankReconciliation $reconciliation, BankStatementLine $statementLine, JournalEntryLine $entryLine): void
    {
        if ($reconciliation->isCompleted()) {
            throw new \RuntimeException('This reconciliation is already completed - reopen it first.');
        }

        if ($entryLine->account_id !== $reconciliation->account_id) {
            throw new \RuntimeException('That ledger line does not belong to this bank account\'s GL account.');
        }

        if ($entryLine->cleared_at !== null) {
            throw new \RuntimeException('That ledger line is already cleared against another statement line.');
        }

        DB::transaction(function () use ($statementLine, $entryLine) {
            $statementLine->status = 'matched';
            $statementLine->matched_journal_entry_line_id = $entryLine->id;
            $statementLine->matched_at = now();
            $statementLine->matched_by_id = Auth::id();
            $statementLine->save();

            $entryLine->cleared_at = now();
            $entryLine->save();
        });
    }

    public function unmatch(BankStatementLine $statementLine): void
    {
        if ($statementLine->status === 'reconciled') {
            throw new \RuntimeException('This line belongs to a completed reconciliation - reopen it first.');
        }

        DB::transaction(function () use ($statementLine) {
            $entryLine = $statementLine->matched_journal_entry_line;
            if ($entryLine) {
                $entryLine->cleared_at = null;
                $entryLine->save();
            }

            $statementLine->status = 'unmatched';
            $statementLine->matched_journal_entry_line_id = null;
            $statementLine->matched_at = null;
            $statementLine->matched_by_id = null;
            $statementLine->save();
        });
    }

    /**
     * Records a bank-only item (bank charges, interest, direct debit, etc.)
     * that appears on the statement but was never posted to the books, then
     * matches the new cash leg to the statement line.
     */
    public function createAdjustmentEntry(BankReconciliation $reconciliation, BankStatementLine $statementLine, int $contraAccountId, ?string $description = null): JournalEntry
    {
        if ($reconciliation->isCompleted()) {
            throw new \RuntimeException('This reconciliation is already completed - reopen it first.');
        }

        $amount = $statementLine->amount;
        $description = $description ?: ($statementLine->description ?: 'Bank statement adjustment');

        return DB::transaction(function () use ($reconciliation, $statementLine, $contraAccountId, $amount, $description) {
            $entry = JournalEntry::create([
                'company_id'     => $reconciliation->company_id,
                'is_manual'      => true,
                'journal_number' => $this->generateNumber(),
                'date'           => $statementLine->transaction_date,
                'reference'      => "BANKREC-{$reconciliation->id}",
                'description'    => $description,
                'status'         => 'posted',
                'created_by_id'  => Auth::id(),
                'posted_by_id'   => Auth::id(),
                'posted_at'      => now(),
            ]);

            $cashLine = $entry->journal_entry_lines()->create([
                'account_id' => $reconciliation->account_id,
                'debit'      => $amount > 0 ? $amount : 0,
                'credit'     => $amount < 0 ? abs($amount) : 0,
                'description' => $description,
            ]);

            $entry->journal_entry_lines()->create([
                'account_id' => $contraAccountId,
                'debit'      => $amount < 0 ? abs($amount) : 0,
                'credit'     => $amount > 0 ? $amount : 0,
                'description' => $description,
            ]);

            $this->matchLine($reconciliation, $statementLine, $cashLine);

            BankReconciliationItem::create([
                'bank_reconciliation_id' => $reconciliation->id,
                'bank_statement_line_id' => $statementLine->id,
                'journal_entry_line_id'  => $cashLine->id,
                'item_type'              => 'adjustment',
                'amount'                 => $amount,
                'description'            => $description,
                'cleared_at'             => now(),
            ]);

            return $entry;
        });
    }

    /** Ledger lines posted to the account, on or before the period end, not yet cleared - i.e. outstanding cheques/deposits in transit. */
    public function outstandingBookItems(BankReconciliation $reconciliation)
    {
        return JournalEntryLine::query()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('journal_entry_lines.account_id', $reconciliation->account_id)
            ->where('journal_entries.status', 'posted')
            ->where('journal_entries.date', '<=', $reconciliation->period_end)
            ->whereNull('journal_entry_lines.cleared_at')
            ->select('journal_entry_lines.*')
            ->get();
    }

    /** Statement lines dated within the period that still have no ledger counterpart. */
    public function unmatchedBankLines(BankReconciliation $reconciliation)
    {
        return BankStatementLine::where('bank_account_id', $reconciliation->bank_account_id)
            ->where('status', 'unmatched')
            ->where('transaction_date', '<=', $reconciliation->period_end)
            ->get();
    }

    public function complete(BankReconciliation $reconciliation): BankReconciliation
    {
        if ($reconciliation->isCompleted()) {
            return $reconciliation;
        }

        if ($this->unmatchedBankLines($reconciliation)->isNotEmpty()) {
            throw new \RuntimeException('There are still unmatched bank statement lines. Match them or record adjustment entries before completing.');
        }

        $outstanding = $this->outstandingBookItems($reconciliation);
        $outstandingTotal = (float) $outstanding->sum(fn ($l) => (float) $l->debit - (float) $l->credit);

        // Re-derive the book balance now rather than trusting the snapshot taken when the
        // reconciliation was started - adjustment entries created since then (bank charges,
        // interest, etc.) have moved the real balance, and the whole point of completing is
        // to confirm the ledger agrees with the bank as of right now.
        $bookClosingBalance = $this->bookBalance->balanceAsOf($reconciliation->account_id, $reconciliation->period_end);

        $adjustedBank = (float) $reconciliation->statement_closing_balance + $outstandingTotal;
        $adjustedBook = $bookClosingBalance;
        $difference = round($adjustedBank - $adjustedBook, 2);

        if (abs($difference) > 0.01) {
            throw new \RuntimeException("This reconciliation is out of balance by {$difference}. Investigate the remaining difference before completing.");
        }

        DB::transaction(function () use ($reconciliation, $outstanding, $bookClosingBalance, $adjustedBank, $adjustedBook, $difference) {
            $matchedStatementLines = BankStatementLine::where('bank_account_id', $reconciliation->bank_account_id)
                ->where('status', 'matched')
                ->with('matched_journal_entry_line')
                ->get();

            foreach ($matchedStatementLines as $line) {
                $entryLine = $line->matched_journal_entry_line;

                BankReconciliationItem::firstOrCreate(
                    ['bank_reconciliation_id' => $reconciliation->id, 'bank_statement_line_id' => $line->id],
                    [
                        'journal_entry_line_id' => $entryLine?->id,
                        'item_type'             => 'matched',
                        'amount'                => $line->amount,
                        'cleared_at'            => $line->matched_at,
                    ]
                );

                $line->status = 'reconciled';
                $line->save();

                if ($entryLine) {
                    $entryLine->bank_reconciliation_id = $reconciliation->id;
                    $entryLine->save();
                }
            }

            foreach ($outstanding as $line) {
                BankReconciliationItem::create([
                    'bank_reconciliation_id' => $reconciliation->id,
                    'journal_entry_line_id'  => $line->id,
                    'item_type'              => 'outstanding_book_item',
                    'amount'                 => (float) $line->debit - (float) $line->credit,
                ]);
            }

            $reconciliation->book_closing_balance = $bookClosingBalance;
            $reconciliation->adjusted_bank_balance = $adjustedBank;
            $reconciliation->adjusted_book_balance = $adjustedBook;
            $reconciliation->difference = $difference;
            $reconciliation->status = 'completed';
            $reconciliation->completed_at = now();
            $reconciliation->reviewed_by_id = Auth::id();
            $reconciliation->save();
        });

        return $reconciliation->fresh();
    }

    /** Unwinds a completed reconciliation so its matches can be edited again. */
    public function reopen(BankReconciliation $reconciliation): void
    {
        DB::transaction(function () use ($reconciliation) {
            JournalEntryLine::where('bank_reconciliation_id', $reconciliation->id)
                ->update(['bank_reconciliation_id' => null]);

            BankStatementLine::where('bank_account_id', $reconciliation->bank_account_id)
                ->where('status', 'reconciled')
                ->update(['status' => 'matched']);

            $reconciliation->status = 'in_progress';
            $reconciliation->completed_at = null;
            $reconciliation->save();
        });
    }

    private function generateNumber(): string
    {
        $last = JournalEntry::orderByDesc('id')->value('journal_number');
        $next = $last ? ((int) substr($last, 4)) + 1 : 1;
        return 'JNL-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }
}
