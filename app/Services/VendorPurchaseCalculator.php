<?php

namespace App\Services;

use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\Auth;

/**
 * Purchases grouped by vendor, for the Purchase by Vendors report: what each
 * vendor's bills put on Accounts Payable in the period, read from the ledger
 * (AP credits - debits of bill postings, same filters and currency
 * conversion as the Trial Balance), so it ties to Accounts Payable instead
 * of summing bill documents (whose stored totals/exchange amounts could
 * disagree with what was actually posted, and included bills that were
 * never posted). Vendor = the AP line's vendor, else the bill's; none -> 0.
 * Bulk Buy fuel consumption credits Fuel Inventory, not AP, so it isn't a
 * purchase here (the top-up that filled the tank is).
 */
class VendorPurchaseCalculator
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
     * @return array{0: float, 1: array<int, float>} [total, [vendor_id => amount]]
     */
    public function purchasesByVendor(): array
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
            ->join('bills', 'bills.id', '=', 'journal_entries.bill_id')
            ->where('accounts.name', 'Accounts Payable')
            ->where('journal_entries.company_id', $this->companyId)
            ->where('journal_entries.status', '!=', 'draft')
            ->whereDate('journal_entries.date', '>=', $this->from)
            ->whereDate('journal_entries.date', '<=', $this->to)
            ->when($this->cashBasis, fn ($q) => $q->where('bills.status', 'Paid'))
            ->groupByRaw('COALESCE(journal_entry_lines.vendor_id, bills.vendor_id, 0)')
            ->selectRaw("COALESCE(journal_entry_lines.vendor_id, bills.vendor_id, 0) as vendor_id, SUM({$credit}) - SUM({$debit}) as amount")
            ->get();

        $byVendor = [];
        foreach ($rows as $row) {
            if (abs((float) $row->amount) < 0.005) {
                continue; // fully reversed (e.g. deleted bills)
            }
            $byVendor[(int) $row->vendor_id] = (float) $row->amount;
        }

        return [array_sum($byVendor), $byVendor];
    }
}
