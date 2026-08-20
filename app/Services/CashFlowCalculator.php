<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\AccountType;
use App\Models\JournalEntryLine;

/**
 * Direct-method Cash Flow Statement, built entirely from the posted
 * double-entry ledger (journal_entries/journal_entry_lines) - the same
 * source of truth as the Trial Balance - rather than the looser
 * string-typed Payment/CashFlow tables. Every Payment is posted to a
 * "Cash & Bank" account by PaymentJournalService, and its `category`
 * tells us whether the cash moved to/from a customer or a vendor, so
 * operating activity can be classified without guessing.
 */
class CashFlowCalculator
{
    public function __construct(
        private int $companyId,
        private string $from,
        private string $to
    ) {
    }

    private function cashAccountIds(): array
    {
        return AccountType::where('name', 'Cash & Bank')->first()?->accounts->pluck('id')->all() ?? [];
    }

    private function baseQuery(array $cashAccountIds)
    {
        return JournalEntryLine::query()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->whereIn('journal_entry_lines.account_id', $cashAccountIds)
            ->where('journal_entries.company_id', $this->companyId)
            ->where('journal_entries.status', '!=', 'draft');
    }

    /**
     * Net Cash & Bank balance as of (and including) the given date -
     * literally SUM(debit) - SUM(credit) for every posted line against a
     * Cash & Bank account up to that date.
     */
    public function cashBalanceAsOf(string $date): float
    {
        $cashAccountIds = $this->cashAccountIds();

        if (empty($cashAccountIds)) {
            return 0.0;
        }

        return (float) $this->baseQuery($cashAccountIds)
            ->where('journal_entries.date', '<=', $date)
            ->selectRaw('COALESCE(SUM(journal_entry_lines.debit) - SUM(journal_entry_lines.credit), 0) as balance')
            ->value('balance');
    }

    /**
     * Cash movements within the date range, classified via the linked
     * Payment's category: 'customer'/'invoice' payments are cash received
     * from customers, 'vendor'/'bill' payments are cash paid to vendors
     * and suppliers. Anything else (manual journal entries, or cash
     * activity not tied to a Payment at all) is reported honestly as
     * "other" rather than folded into operating activities.
     *
     * @return array{receipts: float, payments: float, other: float}
     */
    public function operatingActivities(): array
    {
        $cashAccountIds = $this->cashAccountIds();

        if (empty($cashAccountIds)) {
            return ['receipts' => 0.0, 'payments' => 0.0, 'other' => 0.0];
        }

        $rows = $this->baseQuery($cashAccountIds)
            ->leftJoin('payments', 'payments.id', '=', 'journal_entries.payment_id')
            ->whereDate('journal_entries.date', '>=', $this->from)
            ->whereDate('journal_entries.date', '<=', $this->to)
            ->selectRaw("
                CASE
                    WHEN LOWER(payments.category) IN ('customer', 'invoice') THEN 'receipts'
                    WHEN LOWER(payments.category) IN ('vendor', 'bill') THEN 'payments'
                    ELSE 'other'
                END as bucket,
                SUM(journal_entry_lines.debit) - SUM(journal_entry_lines.credit) as net
            ")
            ->groupBy('bucket')
            ->pluck('net', 'bucket');

        return [
            'receipts' => (float) ($rows['receipts'] ?? 0),
            'payments' => (float) ($rows['payments'] ?? 0),
            'other' => (float) ($rows['other'] ?? 0),
        ];
    }

    public function beginningDate(): string
    {
        return Carbon::parse($this->from)->subDay()->format('Y-m-d');
    }
}
