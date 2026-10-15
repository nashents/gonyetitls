<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\BillExpense;
use App\Models\User;
use App\Services\Accounting\FuelJournalService;
use App\Services\Accounting\LedgerResyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Puts every bill raised from a trip back on its correct debit account.
 * Older code debited the hardcoded "Trip Expense" account instead:
 *  - fuel order bills -> FuelJournalService::postConsumption(), which owns
 *    the account (Fuel - COGS on a trip, Fuel - Ops off one) and the credit
 *    side (AP for Once Off Buy, Fuel Inventory for Bulk Buy);
 *  - every other trip bill line (trip expenses, allowances, transporter
 *    payments) -> its own Expense/Allowance account, bill force-reposted
 *    (isUnchanged() only compares the AP leg).
 * Works off the bill line's own expense_id - bills.trip_expense_id is often a
 * soft-deleted/recreated TripExpense. Top-up bills (not trip bills) and lines
 * with no account at all (legacy, never posted) are left alone. Idempotent.
 *
 * php artisan bills:repoint-trip-expense-accounts --dry-run
 * php artisan bills:repoint-trip-expense-accounts --bill=TEB00350 --dry-run
 * php artisan bills:repoint-trip-expense-accounts --force
 */
class RepointTripExpenseBillAccounts extends Command
{
    protected $signature = 'bills:repoint-trip-expense-accounts
        {--dry-run : Preview only, fix nothing}
        {--force : Skip the confirmation prompt}
        {--bill=* : Limit to these bill numbers or IDs (repeatable); also explains lines left alone}';

    protected $description = 'Move trip bill lines (trip expenses, fuel orders, transporter payments) onto their correct expense account and repost';

    public function handle(LedgerResyncService $resync, FuelJournalService $fuelJournal)
    {
        $fuelCogs = Account::where('name', 'Fuel - COGS')->first();
        $fuelOps = Account::where('name', 'Fuel - Ops')->first();
        $only = $this->option('bill');

        $query = BillExpense::with('bill.fuel', 'account', 'expense.account', 'allowance.account')
            ->whereHas('bill', function ($q) use ($only) {
                if ($only) {
                    $q->where(fn ($q) => $q->whereIn('bill_number', $only)->orWhereIn('id', array_filter($only, 'is_numeric')));
                } else {
                    $q->where(fn ($q) => $q->whereNotNull('trip_id')->orWhereNotNull('trip_expense_id')
                        ->orWhereNotNull('fuel_id')->orWhere('category', 'like', 'Trip Expense%'));
                }
            });

        $rows = [];
        $fuels = [];
        $lines = [];
        foreach ($query->get() as $line) {
            $bill = $line->bill;
            $isFuel = (bool) $bill->fuel_id;
            $target = $isFuel
                ? ($bill->fuel ? ($bill->fuel->trip_id ? $fuelCogs : $fuelOps) : null)
                : (optional($line->expense)->account ?: optional($line->allowance)->account);

            $reason = match (true) {
                (bool) $bill->top_up_id => 'top-up bill (not a trip bill)',
                $isFuel && ! $bill->fuel => 'fuel order deleted',
                $isFuel && $bill->fuel->authorization !== 'approved' => 'fuel order not approved',
                ! $isFuel && ! $line->expense && ! $line->allowance => 'line has no expense/allowance',
                // Legacy duplicate bills (2024, never posted) - giving them an
                // account now would only post old figures, not fix any.
                ! $line->account_id => 'line has no account (legacy, never posted)',
                ! $target => $isFuel ? 'Fuel - COGS/Ops account missing' : 'expense has no account',
                (int) $line->account_id === (int) $target->id => 'already correct',
                default => null,
            };

            if (! $reason) {
                if ($isFuel) {
                    $fuels[$bill->fuel_id] = $bill->fuel;
                } else {
                    $lines[] = [$line, $target];
                }
            }
            if (! $reason || $only) {
                $rows[] = [
                    $bill->bill_number,
                    $line->id,
                    trim(optional($line->expense)->name . ' #' . $line->expense_id),
                    optional($line->account)->name,
                    optional($target)->name,
                    $reason ?? ($isFuel ? 'WILL REPOST FUEL ORDER' : 'WILL RE-POINT'),
                ];
            }
        }

        if ($rows) {
            $this->table(['Bill', 'Line', 'Expense #id', 'Line account', 'Correct account', 'Action'], $rows);
        }

        $count = count($lines) + count($fuels);
        if (! $count) {
            $this->info('Nothing to re-point.');
            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->warn(count($lines) . ' line(s) would be re-pointed and ' . count($fuels) . ' fuel order(s) reposted. Nothing changed.');
            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm("Fix {$count} item(s) above and repost their bills?")) {
            return self::SUCCESS;
        }

        $bills = [];
        foreach ($lines as [$line, $target]) {
            $bills[$line->bill_id][] = [$line, $target];
        }

        $fixed = 0;
        foreach ($bills as $billLines) {
            $bill = $billLines[0][0]->bill;
            $target = $billLines[0][1];
            try {
                $this->actAs($bill->user_id);
                // Lines + repost together: a failed repost must leave the line
                // on its old account so a re-run still picks the bill up.
                DB::transaction(function () use ($bill, $billLines, $target, $resync) {
                    foreach ($billLines as [$line, $lineTarget]) {
                        $line->account_id = $lineTarget->id;
                        $line->account_type_id = $lineTarget->account_type_id;
                        $line->saveQuietly();
                    }
                    if ((int) $bill->account_id !== (int) $target->id && $bill->bill_expenses()->count() === 1) {
                        $bill->account_id = $target->id;
                        $bill->account_type_id = $target->account_type_id;
                        $bill->saveQuietly();
                    }
                    if ($bill->authorization === 'approved') {
                        $resync->resyncBill($bill->fresh(), "Bill {$bill->bill_number} re-pointed to {$target->name}", true);
                    }
                });
                $fixed++;
            } catch (\Throwable $e) {
                $this->error("Bill #{$bill->id} {$bill->bill_number}: {$e->getMessage()}");
            }
        }

        foreach ($fuels as $fuel) {
            try {
                // Re-points the line and force-reposts when the account moved.
                $this->actAs(optional($fuel->bill)->user_id ?: $fuel->user_id);
                $fuelJournal->postConsumption($fuel->fresh());
                $fixed++;
            } catch (\Throwable $e) {
                $this->error("Fuel order #{$fuel->id} {$fuel->order_number}: {$e->getMessage()}");
            }
        }

        $this->info("Fixed {$fixed} bill(s).");
        return self::SUCCESS;
    }

    /**
     * Journal entries take their company from Auth::user()->employee (bills
     * have no company_id) - on the CLI, act as the bill's creator, else any
     * user with an employee record (same pattern as FuelLedgerRepairService).
     */
    private function actAs(?int $userId): void
    {
        $user = ($userId ? User::whereHas('employee')->find($userId) : null)
            ?: (Auth::user() && Auth::user()->employee ? Auth::user() : User::whereHas('employee')->first());

        if ($user) {
            Auth::setUser($user);
        }
    }
}
