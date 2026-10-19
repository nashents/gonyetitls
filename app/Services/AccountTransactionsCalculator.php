<?php

namespace App\Services;

use App\Models\Company;
use App\Models\JournalEntryLine;

/**
 * Account Transactions (General Ledger): the full transaction history for
 * a single account - a classic T-account listing with a running balance -
 * built on the same posted ledger as Trial Balance/Balance Sheet/Account
 * Balances. Unlike those reports, this one deliberately isn't an aggregate:
 * it lists every individual JournalEntryLine for the selected account.
 *
 * Amounts are in the company's reporting currency by default. Pass
 * $displayCurrencyId (a bank/cash account's own currency) for a bank
 * statement in that currency, like the customer statement: lines posted in
 * it show their original amounts; the odd line posted in another currency
 * (e.g. a USD journal against a ZAR bank) is converted at the account's
 * nearest own-currency rate and flagged.
 */
class AccountTransactionsCalculator
{
    /** @var array<int, array{date: string, rate: float}>|null */
    private ?array $rates = null;

    public function __construct(
        private int $companyId,
        private int $accountId,
        private string $from,
        private string $to,
        private bool $creditNormal,
        private ?int $displayCurrencyId = null
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

    /** See TrialBalanceCalculator::reportingCurrencyId() for the rule this applies. */
    private function reportingCurrencyId(): ?int
    {
        return Company::find($this->companyId)?->currency_id;
    }

    /** Statement in the account's own currency (not the reporting one)? */
    private function inDisplayCurrency(): bool
    {
        return $this->displayCurrencyId && $this->displayCurrencyId !== (int) $this->reportingCurrencyId();
    }

    /**
     * Net balance of every posted line for this account strictly before
     * the report's start date.
     */
    public function openingBalance(): float
    {
        if ($this->inDisplayCurrency()) {
            $raw = 0.0;
            foreach ($this->lines(before: true) as $line) {
                [$debit, $credit] = $this->amounts($line);
                $raw += $debit - $credit;
            }

            return $this->creditNormal ? -1 * $raw : $raw;
        }

        $base = (int) $this->reportingCurrencyId();

        $raw = (float) $this->baseQuery()
            ->where('journal_entries.date', '<', $this->from)
            ->selectRaw("COALESCE(SUM(CASE WHEN journal_entry_lines.currency_id IS NULL OR journal_entry_lines.currency_id = {$base} THEN journal_entry_lines.debit ELSE journal_entry_lines.exchange_debit END)
                - SUM(CASE WHEN journal_entry_lines.currency_id IS NULL OR journal_entry_lines.currency_id = {$base} THEN journal_entry_lines.credit ELSE journal_entry_lines.exchange_credit END), 0) as balance")
            ->value('balance');

        return $this->creditNormal ? -1 * $raw : $raw;
    }

    /**
     * @return array<int, array{date: string, journal_number: string, description: string, debit: float, credit: float, running_balance: float, converted: bool}>
     */
    public function transactions(float $openingBalance): array
    {
        $running = $openingBalance;
        $rows = [];

        foreach ($this->lines(before: false) as $line) {
            [$debit, $credit, $converted] = $this->amounts($line);
            $delta = $this->creditNormal ? ($credit - $debit) : ($debit - $credit);
            $running += $delta;

            $description = $line->line_description ?: ($line->entry_description ?: $line->reference);
            if ($converted) {
                $description .= ' (converted from ' . (optional(\App\Models\Currency::find($line->currency_id))->name ?? 'another currency') . ')';
            }

            $rows[] = [
                'date' => $line->date,
                'journal_number' => $line->journal_number,
                'description' => $description,
                'debit' => $debit,
                'credit' => $credit,
                'running_balance' => $running,
                'converted' => $converted,
            ];
        }

        return $rows;
    }

    private function lines(bool $before)
    {
        return $this->baseQuery()
            ->when($before,
                fn ($q) => $q->where('journal_entries.date', '<', $this->from),
                fn ($q) => $q->whereDate('journal_entries.date', '>=', $this->from)->whereDate('journal_entries.date', '<=', $this->to))
            ->orderBy('journal_entries.date')
            ->orderBy('journal_entry_lines.id')
            ->get([
                'journal_entries.date',
                'journal_entries.journal_number',
                'journal_entries.reference',
                'journal_entries.description as entry_description',
                'journal_entry_lines.description as line_description',
                'journal_entry_lines.currency_id',
                'journal_entry_lines.debit',
                'journal_entry_lines.credit',
                'journal_entry_lines.exchange_debit',
                'journal_entry_lines.exchange_credit',
            ]);
    }

    /** @return array{0: float, 1: float, 2: bool} debit, credit, converted */
    private function amounts($line): array
    {
        $base = (int) $this->reportingCurrencyId();
        $isBase = is_null($line->currency_id) || (int) $line->currency_id === $base;
        $baseDebit = $isBase ? (float) $line->debit : (float) $line->exchange_debit;
        $baseCredit = $isBase ? (float) $line->credit : (float) $line->exchange_credit;

        if (! $this->inDisplayCurrency()) {
            return [$baseDebit, $baseCredit, false];
        }

        if ((int) $line->currency_id === $this->displayCurrencyId) {
            return [(float) $line->debit, (float) $line->credit, false];
        }

        // Posted in another currency - back to the account's currency at its
        // nearest own-currency rate (reporting units per account unit).
        $rate = $this->nearestRate((string) $line->date);

        return $rate > 0
            ? [round($baseDebit / $rate, 2), round($baseCredit / $rate, 2), true]
            : [$baseDebit, $baseCredit, true];
    }

    private function nearestRate(string $date): float
    {
        if ($this->rates === null) {
            $this->rates = $this->baseQuery()
                ->where('journal_entry_lines.currency_id', $this->displayCurrencyId)
                ->where('journal_entry_lines.exchange_rate', '>', 0)
                ->orderBy('journal_entries.date')
                ->get(['journal_entries.date', 'journal_entry_lines.exchange_rate'])
                ->map(fn ($l) => ['date' => substr((string) $l->date, 0, 10), 'rate' => (float) $l->exchange_rate])
                ->all();
        }

        $day = substr($date, 0, 10);
        $best = null;
        foreach ($this->rates as $r) {
            if ($best === null || abs(strtotime($r['date']) - strtotime($day)) < abs(strtotime($best['date']) - strtotime($day))) {
                $best = $r;
            }
        }

        return $best['rate'] ?? 0.0;
    }
}
