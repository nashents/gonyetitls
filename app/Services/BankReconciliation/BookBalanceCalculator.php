<?php

namespace App\Services\BankReconciliation;

use App\Models\JournalEntryLine;

/**
 * Book balance of a single Chart of Accounts "Cash & Bank" account as of a
 * date - the single-account counterpart to AccountBalanceCalculator/
 * CashFlowCalculator, which both aggregate across every cash account at once.
 */
class BookBalanceCalculator
{
    public function balanceAsOf(int $accountId, string $date): float
    {
        return (float) JournalEntryLine::query()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('journal_entry_lines.account_id', $accountId)
            ->where('journal_entries.status', 'posted')
            ->where('journal_entries.date', '<=', $date)
            ->selectRaw('COALESCE(SUM(journal_entry_lines.debit) - SUM(journal_entry_lines.credit), 0) as balance')
            ->value('balance');
    }
}
