<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Account;
use App\Models\JournalEntryLine;

/**
 * Account Balances: Opening / Activity / Closing per account over a date
 * range, across every account in the chart of accounts (not just Assets/
 * Liabilities/Equity like the Balance Sheet) - the standard "how did each
 * account get to its current balance" reference report. Built on the same
 * posted ledger as Trial Balance/Balance Sheet.
 */
class AccountBalanceCalculator
{
    /** Account type groups whose normal balance is a credit (displayed as positive). */
    const CREDIT_NORMAL_GROUPS = ['Liabilities & Credit Cards', 'Equity', 'Income'];

    public function __construct(
        private int $companyId,
        private string $from,
        private string $to
    ) {
    }

    private function balancesAsOf(string $date): array
    {
        return JournalEntryLine::query()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('journal_entries.company_id', $this->companyId)
            ->where('journal_entries.status', '!=', 'draft')
            ->where('journal_entries.date', '<=', $date)
            ->selectRaw('journal_entry_lines.account_id, SUM(journal_entry_lines.debit) - SUM(journal_entry_lines.credit) as balance')
            ->groupBy('journal_entry_lines.account_id')
            ->pluck('balance', 'account_id')
            ->map(fn ($v) => (float) $v)
            ->all();
    }

    /**
     * @return array<string, array{
     *     rows: array<int, array{label: string, buckets: array{'Opening Balance': float, 'Activity': float}, total: float}>,
     *     grandTotals: array{buckets: array{'Opening Balance': float, 'Activity': float}, total: float}
     * }> grouped by account_type_group_name
     */
    public function report(): array
    {
        $openingDate = Carbon::parse($this->from)->subDay()->format('Y-m-d');

        $opening = $this->balancesAsOf($openingDate);
        $closing = $this->balancesAsOf($this->to);

        $accounts = Account::with('account_type.account_type_group')
            ->orderBy('name')
            ->get();

        $groups = [];

        foreach ($accounts as $account) {
            $groupName = $account->account_type?->account_type_group?->name ?? 'Other';
            $creditNormal = in_array($groupName, self::CREDIT_NORMAL_GROUPS, true);

            $openingBalance = $opening[$account->id] ?? 0.0;
            $closingBalance = $closing[$account->id] ?? 0.0;

            if ($creditNormal) {
                $openingBalance *= -1;
                $closingBalance *= -1;
            }

            if (abs($openingBalance) < 0.005 && abs($closingBalance) < 0.005) {
                continue;
            }

            $activity = $closingBalance - $openingBalance;

            $groups[$groupName]['rows'][] = [
                'label' => $account->name,
                'buckets' => ['Opening Balance' => $openingBalance, 'Activity' => $activity],
                'total' => $closingBalance,
            ];
        }

        foreach ($groups as $groupName => $group) {
            $opening = array_sum(array_column(array_column($group['rows'], 'buckets'), 'Opening Balance'));
            $activity = array_sum(array_column(array_column($group['rows'], 'buckets'), 'Activity'));
            $closing = array_sum(array_column($group['rows'], 'total'));

            $groups[$groupName]['grandTotals'] = [
                'buckets' => ['Opening Balance' => $opening, 'Activity' => $activity],
                'total' => $closing,
            ];
        }

        return $groups;
    }
}
