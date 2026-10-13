<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\Company;
use App\Models\Inventory;
use App\Models\JournalEntry;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Books the value of opening stock (Inventories > Import) into the ledger:
 * DR Spares Inventory / CR Opening Balance Equity. Stock already on hand when
 * the business started on the system was never bought through it, so it isn't
 * owed to any vendor - posting it via a GRV would fabricate an Accounts
 * Payable balance nobody can clear.
 *
 * Posted in company currency (each row's exchange_amount when it's held in a
 * foreign currency) - the same value Dispatches credits back out of Spares
 * Inventory when the stock is issued. Marked manual: it has no source document
 * for Resync to Ledger to rebuild it from.
 */
class OpeningStockJournalService
{
    /** @param Collection<int, Inventory> $inventories */
    public function post(Collection $inventories, string $date, string $reference): ?JournalEntry
    {
        $companyId = Auth::user()->employee->company_id;
        $companyCurrencyId = Company::whereKey($companyId)->value('currency_id');

        $total = round($inventories->sum(fn (Inventory $inventory) => $this->companyValue($inventory, $companyCurrencyId)), 2);

        if ($total <= 0) {
            return null;
        }

        $spares = Account::where('name', 'Spares Inventory')->first();
        $equity = Account::where('name', 'Opening Balance Equity')->first();

        if (! $spares || ! $equity) {
            throw new \RuntimeException("Required GL account '" . (! $spares ? 'Spares Inventory' : 'Opening Balance Equity') . "' was not found. Run the migrations (or create it) before importing opening stock.");
        }

        return DB::transaction(function () use ($inventories, $date, $reference, $total, $companyId, $companyCurrencyId, $spares, $equity) {
            $description = "Opening stock import {$reference} - {$inventories->count()} item(s)";

            $entry = JournalEntry::create([
                'company_id'     => $companyId,
                'journal_number' => $this->generateNumber(),
                'date'           => $date,
                'reference'      => $reference,
                'description'    => $description,
                'is_manual'      => true,
                'status'         => 'posted',
                'created_by_id'  => Auth::id(),
                'posted_by_id'   => Auth::id(),
                'posted_at'      => now(),
            ]);

            foreach ([[$spares, $total, 0], [$equity, 0, $total]] as [$account, $debit, $credit]) {
                $entry->journal_entry_lines()->create([
                    'account_id'      => $account->id,
                    'debit'           => $debit,
                    'credit'          => $credit,
                    'exchange_debit'  => $debit,
                    'exchange_credit' => $credit,
                    'currency_id'     => $companyCurrencyId,
                    'exchange_rate'   => 1,
                    'description'     => $description,
                ]);
            }

            $entry->assertBalanced();

            return $entry;
        });
    }

    private function companyValue(Inventory $inventory, $companyCurrencyId): float
    {
        $value = (int) $inventory->currency_id === (int) $companyCurrencyId
            ? $inventory->total
            : $inventory->exchange_amount;

        return is_numeric($value) ? (float) $value : 0;
    }

    protected function generateNumber(): string
    {
        $last = JournalEntry::orderByDesc('id')->value('journal_number');
        $next = $last ? ((int) substr($last, 4)) + 1 : 1;
        return 'JNL-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }
}
