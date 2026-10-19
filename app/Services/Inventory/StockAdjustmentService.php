<?php

namespace App\Services\Inventory;

use App\Models\Account;
use App\Models\Company;
use App\Models\Department;
use App\Models\DepartmentHead;
use App\Models\Inventory;
use App\Models\JournalEntry;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Stock count -> adjustment. A count sheet snapshots each in-stock inventory
 * row (balance at the time, company-currency unit cost); stores enter the
 * physical count; on approval each variance is applied to the row's balance
 * and the value is posted:
 *
 *   shortage (counted < system): DR Stock Write-off  / CR Spares Inventory
 *   surplus  (counted > system): DR Spares Inventory / CR Stock Write-off
 *
 * Variances are applied as a delta (balance += counted - system), so stock
 * that moved between the count and the approval (GRVs, approved dispatches)
 * isn't overwritten. Uncounted rows are left alone. The journal is manual
 * (like opening stock): there's no source document Resync to Ledger could
 * rebuild it from.
 */
class StockAdjustmentService
{
    public function createCount(string $date, $storeId, ?string $description): StockAdjustment
    {
        return DB::transaction(function () use ($date, $storeId, $description) {
            $adjustment = StockAdjustment::create([
                'user_id' => Auth::id(),
                'adjustment_number' => $this->adjustmentNumber(),
                'date' => $date,
                'store_id' => $storeId ?: null,
                'description' => $description,
                'status' => StockAdjustment::DRAFT,
            ]);

            $companyCurrencyId = $this->companyCurrencyId();

            Inventory::where('status', 1)
                ->where('balance', '>', 0)
                ->when($storeId, fn ($q) => $q->where('store_id', $storeId))
                ->orderBy('id')
                ->chunk(500, function ($inventories) use ($adjustment, $companyCurrencyId) {
                    $now = now();
                    StockAdjustmentItem::insert($inventories->map(fn (Inventory $inventory) => [
                        'stock_adjustment_id' => $adjustment->id,
                        'inventory_id' => $inventory->id,
                        'product_id' => $inventory->product_id,
                        'store_id' => $inventory->store_id,
                        'system_qty' => (float) $inventory->balance,
                        'counted_qty' => null,
                        'unit_cost' => $this->unitCost($inventory, $companyCurrencyId),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->all());
                });

            return $adjustment;
        });
    }

    public function submit(StockAdjustment $adjustment): void
    {
        if ($adjustment->status !== StockAdjustment::DRAFT) {
            throw ValidationException::withMessages(['adjustment' => 'Only a draft count can be submitted.']);
        }
        if (! $adjustment->items()->withVariance()->exists()) {
            throw ValidationException::withMessages(['adjustment' => 'Nothing to adjust: no counted item differs from the system quantity.']);
        }

        $adjustment->update(['status' => StockAdjustment::PENDING, 'submitted_at' => now()]);
    }

    // Back to draft so the counts can be corrected before anyone approves.
    public function reopen(StockAdjustment $adjustment): void
    {
        if ($adjustment->status !== StockAdjustment::PENDING) {
            throw ValidationException::withMessages(['adjustment' => 'Only a count awaiting approval can be reopened.']);
        }

        $adjustment->update(['status' => StockAdjustment::DRAFT, 'submitted_at' => null]);
    }

    public function reject(StockAdjustment $adjustment, ?string $comments): void
    {
        $this->assertCanApprove($adjustment);

        $adjustment->update([
            'status' => StockAdjustment::REJECTED,
            'authorized_by_id' => Auth::id(),
            'authorization_date' => now(),
            'authorization_comments' => $comments,
        ]);
    }

    public function approve(StockAdjustment $adjustment, ?string $comments): StockAdjustment
    {
        $this->assertCanApprove($adjustment);

        return DB::transaction(function () use ($adjustment, $comments) {
            $adjustment = StockAdjustment::lockForUpdate()->findOrFail($adjustment->id);

            if ($adjustment->status !== StockAdjustment::PENDING) {
                throw ValidationException::withMessages(['adjustment' => 'This count is no longer awaiting approval.']);
            }

            $items = $adjustment->items()->withVariance()->get();
            $inventories = Inventory::withTrashed()->whereIn('id', $items->pluck('inventory_id'))->lockForUpdate()->withReservedQty()->get()->keyBy('id');

            $errors = [];
            foreach ($items as $item) {
                $inventory = $inventories->get($item->inventory_id);
                $label = $inventory ? ($inventory->inventory_number ?: 'Item #'.$inventory->id) : 'Item #'.$item->inventory_id;
                $newBalance = (float) optional($inventory)->balance + $item->variance_qty;

                if (! $inventory || $newBalance < -0.00001) {
                    $errors[] = "{$label}: only ".(float) optional($inventory)->balance.' left in the system now, so '.abs($item->variance_qty).' cannot be removed.';
                } elseif ($item->variance_qty < 0 && $newBalance + 0.00001 < $inventory->reserved_qty) {
                    $errors[] = "{$label}: ".(float) $inventory->reserved_qty.' is reserved by dispatches awaiting approval. Approve or reject those dispatches first.';
                }
            }
            if ($errors) {
                throw ValidationException::withMessages(['adjustment' => $errors]);
            }

            $shortage = 0.0;
            $surplus = 0.0;
            foreach ($items as $item) {
                $inventory = $inventories->get($item->inventory_id);
                $newBalance = max(0, round((float) $inventory->balance + $item->variance_qty, 4));

                $inventory->balance = $newBalance;
                $inventory->status = $newBalance > 0 ? 1 : 0;
                $inventory->save();

                if ($item->variance_qty < 0) {
                    $shortage += abs($item->variance_value);
                } else {
                    $surplus += $item->variance_value;
                }
            }

            $entry = $this->postJournal($adjustment, round($shortage, 2), round($surplus, 2));

            $adjustment->update([
                'status' => StockAdjustment::APPROVED,
                'authorized_by_id' => Auth::id(),
                'authorization_date' => now(),
                'authorization_comments' => $comments,
                'journal_entry_id' => optional($entry)->id,
                'shortage_value' => round($shortage, 2),
                'surplus_value' => round($surplus, 2),
            ]);

            return $adjustment;
        });
    }

    /**
     * Super Admin, Management, or the Stores / Finance department head. Nobody
     * but a Super Admin approves their own count.
     */
    public function canApprove(?User $user, StockAdjustment $adjustment): bool
    {
        if (! $user) {
            return false;
        }
        if ($user->roles->pluck('name')->contains('Super Admin')) {
            return true;
        }
        if ((int) $adjustment->user_id === (int) $user->id) {
            return false;
        }

        $employee = $user->employee;
        if (! $employee) {
            return false;
        }
        if ($employee->ranks->pluck('name')->contains('Management')) {
            return true;
        }

        $departmentIds = Department::whereIn('name', ['Stores', 'Finance'])->pluck('id');

        return DepartmentHead::whereIn('department_id', $departmentIds)->where('employee_id', $employee->id)->exists();
    }

    private function assertCanApprove(StockAdjustment $adjustment): void
    {
        if ($adjustment->status !== StockAdjustment::PENDING) {
            throw ValidationException::withMessages(['adjustment' => 'This count is not awaiting approval.']);
        }
        if (! $this->canApprove(Auth::user(), $adjustment)) {
            throw ValidationException::withMessages(['adjustment' => 'Only Management or the Stores / Finance department head can approve a stock count, and not the person who captured it.']);
        }
    }

    private function postJournal(StockAdjustment $adjustment, float $shortage, float $surplus): ?JournalEntry
    {
        if ($shortage <= 0 && $surplus <= 0) {
            return null;
        }

        $spares = Account::where('name', 'Spares Inventory')->first();
        $writeOff = Account::where('name', 'Stock Write-off')->first();

        if (! $spares || ! $writeOff) {
            throw new \RuntimeException("Required GL account '".(! $spares ? 'Spares Inventory' : 'Stock Write-off')."' was not found. Run the migrations (or create it) before approving stock counts.");
        }

        $companyCurrencyId = $this->companyCurrencyId();
        $description = "Stock count {$adjustment->adjustment_number}".($adjustment->store ? " - {$adjustment->store->name}" : '');

        $entry = JournalEntry::create([
            'company_id' => $this->companyId(),
            'journal_number' => $this->journalNumber(),
            'date' => $adjustment->date->toDateString(),
            'reference' => $adjustment->adjustment_number,
            'description' => $description,
            'is_manual' => true,
            'status' => 'posted',
            'created_by_id' => Auth::id(),
            'posted_by_id' => Auth::id(),
            'posted_at' => now(),
        ]);

        $lines = [];
        if ($shortage > 0) {
            $lines[] = [$writeOff, $shortage, 0, "{$description}: stock missing"];
            $lines[] = [$spares, 0, $shortage, "{$description}: stock missing"];
        }
        if ($surplus > 0) {
            $lines[] = [$spares, $surplus, 0, "{$description}: stock found"];
            $lines[] = [$writeOff, 0, $surplus, "{$description}: stock found"];
        }

        foreach ($lines as [$account, $debit, $credit, $lineDescription]) {
            $entry->journal_entry_lines()->create([
                'account_id' => $account->id,
                'debit' => $debit,
                'credit' => $credit,
                'exchange_debit' => $debit,
                'exchange_credit' => $credit,
                'currency_id' => $companyCurrencyId,
                'exchange_rate' => 1,
                'description' => $lineDescription,
            ]);
        }

        $entry->assertBalanced();

        return $entry;
    }

    // Same basis Dispatches use: row cost in company currency over the ORIGINAL qty.
    private function unitCost(Inventory $inventory, $companyCurrencyId): float
    {
        $cost = (int) $inventory->currency_id === (int) $companyCurrencyId ? $inventory->total : $inventory->exchange_amount;
        $originalQty = (float) ($inventory->qty ?: $inventory->balance);

        return is_numeric($cost) && $originalQty > 0 ? round((float) $cost / $originalQty, 6) : 0.0;
    }

    private function companyId()
    {
        return optional(Auth::user()->employee)->company_id ?? optional(Auth::user()->company)->id;
    }

    private function companyCurrencyId()
    {
        return Company::whereKey($this->companyId())->value('currency_id');
    }

    private function adjustmentNumber(): string
    {
        $next = (StockAdjustment::withTrashed()->max('id') ?? 0) + 1;

        return 'SA-'.str_pad($next, 5, '0', STR_PAD_LEFT);
    }

    private function journalNumber(): string
    {
        $last = JournalEntry::orderByDesc('id')->value('journal_number');
        $next = $last ? ((int) substr($last, 4)) + 1 : 1;

        return 'JNL-'.str_pad($next, 5, '0', STR_PAD_LEFT);
    }
}
