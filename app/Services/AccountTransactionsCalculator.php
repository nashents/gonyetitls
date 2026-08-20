<?php

namespace App\Services;

use App\Models\JournalEntryLine;

/**
 * Account Transactions (General Ledger): the full transaction history for
 * a single account - a classic T-account listing with a running balance -
 * built on the same posted ledger as Trial Balance/Balance Sheet/Account
 * Balances. Unlike those reports, this one deliberately isn't an aggregate:
 * it lists every individual JournalEntryLine for the selected account.
 */
class AccountTransactionsCalculator
{
    public function __construct(
        private int $companyId,
        private int $accountId,
        private string $from,
        private string $to,
        private bool $creditNormal
    ) {
    }

    private function baseQuery()
    {
        return JournalEntryLine::query()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('journal_entry_lines.account_id', $this->accountId)
            ->where('journal_entries.company_id', $this->companyId)
            ->where('journal_entries.status', '!=', 'draft');
    }

    /**
     * Net balance of every posted line for this account strictly before
     * the report's start date.
     */
    public function openingBalance(): float
    {
        $raw = (float) $this->baseQuery()
            ->where('journal_entries.date', '<', $this->from)
            ->selectRaw('COALESCE(SUM(journal_entry_lines.debit) - SUM(journal_entry_lines.credit), 0) as balance')
            ->value('balance');

        return $this->creditNormal ? -1 * $raw : $raw;
    }

    /**
     * @return array<int, array{date: string, journal_number: string, description: string, debit: float, credit: float, running_balance: float}>
     */
    public function transactions(float $openingBalance): array
    {
        $lines = $this->baseQuery()
            ->whereDate('journal_entries.date', '>=', $this->from)
            ->whereDate('journal_entries.date', '<=', $this->to)
            ->orderBy('journal_entries.date')
            ->orderBy('journal_entry_lines.id')
            ->get([
                'journal_entries.date',
                'journal_entries.journal_number',
                'journal_entries.reference',
                'journal_entries.description as entry_description',
                'journal_entry_lines.description as line_description',
                'journal_entry_lines.debit',
                'journal_entry_lines.credit',
            ]);

        $running = $openingBalance;
        $rows = [];

        foreach ($lines as $line) {
            $debit = (float) $line->debit;
            $credit = (float) $line->credit;
            $delta = $this->creditNormal ? ($credit - $debit) : ($debit - $credit);
            $running += $delta;

            $rows[] = [
                'date' => $line->date,
                'journal_number' => $line->journal_number,
                'description' => $line->line_description ?: ($line->entry_description ?: $line->reference),
                'debit' => $debit,
                'credit' => $credit,
                'running_balance' => $running,
            ];
        }

        return $rows;
    }
}
