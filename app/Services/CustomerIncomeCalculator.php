<?php

namespace App\Services;

use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\Auth;

/**
 * Income grouped by customer, for the Income by Customer report. Built on
 * the ledger exactly like IncomeStatementCalculator::incomeAmounts() - every
 * Income-group account, credits - debits, same filters and currency
 * conversion - just grouped by the line's customer instead of its account,
 * so the report's total equals Total Income on the P&L and the Trial
 * Balance. (It used to sum invoice totals, VAT included, and missed credit
 * notes / the Customer Advances deferral.) Lines with no customer are
 * grouped under 0.
 */
class CustomerIncomeCalculator
{
    public function __construct(
        private string $from,
        private string $to,
        private bool $cashBasis,
        private $defaultCurrencyId,
        private ?int $companyId = null
    ) {
        $this->companyId = $companyId ?? (Auth::user()?->employee?->company_id ?? Auth::user()?->company_id);
    }

    /**
     * @return array{0: float, 1: array<int, float>} [total, [customer_id => amount]]
     */
    public function incomeByCustomer(): array
    {
        if (! $this->companyId) {
            return [0.0, []];
        }

        $base = (int) $this->defaultCurrencyId;
        $debit = "CASE WHEN journal_entry_lines.currency_id IS NULL OR journal_entry_lines.currency_id = {$base} THEN journal_entry_lines.debit ELSE journal_entry_lines.exchange_debit END";
        $credit = "CASE WHEN journal_entry_lines.currency_id IS NULL OR journal_entry_lines.currency_id = {$base} THEN journal_entry_lines.credit ELSE journal_entry_lines.exchange_credit END";

        $rows = JournalEntryLine::query()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->join('accounts', 'accounts.id', '=', 'journal_entry_lines.account_id')
            ->join('account_types', 'account_types.id', '=', 'accounts.account_type_id')
            ->join('account_type_groups', 'account_type_groups.id', '=', 'account_types.account_type_group_id')
            ->where('account_type_groups.name', 'Income')
            ->where('journal_entries.company_id', $this->companyId)
            ->where('journal_entries.status', '!=', 'draft')
            ->whereDate('journal_entries.date', '>=', $this->from)
            ->whereDate('journal_entries.date', '<=', $this->to)
            ->when($this->cashBasis, fn ($q) => $q->where(fn ($x) => $x->whereNull('journal_entries.invoice_id')
                ->orWhereExists(fn ($s) => $s->selectRaw('1')->from('invoices')
                    ->whereColumn('invoices.id', 'journal_entries.invoice_id')->where('invoices.status', 'Paid'))))
            ->groupBy('journal_entry_lines.customer_id')
            ->selectRaw("COALESCE(journal_entry_lines.customer_id, 0) as customer_id, SUM({$credit}) - SUM({$debit}) as amount")
            ->get();

        $byCustomer = [];
        foreach ($rows as $row) {
            $byCustomer[(int) $row->customer_id] = ($byCustomer[(int) $row->customer_id] ?? 0) + (float) $row->amount;
        }

        return [array_sum($byCustomer), $byCustomer];
    }
}
