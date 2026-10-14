<?php

use App\Models\Account;
use App\Models\Bill;
use App\Services\Accounting\LedgerResyncService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * TripExpenseJournalService used to debit every generic trip expense bill
 * to the hardcoded "Trip Expense" account, ignoring the account set on the
 * Expense (e.g. M.V Parking Fees -> Cost Of Goods Sold) or Allowance.
 * Re-points each such bill + line to that account and reposts its journal
 * entry (forced - isUnchanged() only compares the AP leg).
 *
 * Fuel/transporter-linked bills are skipped (they have their own services).
 * Idempotent: a re-run finds no bill still on "Trip Expense".
 */
class RepointTripExpenseBillsToExpenseAccount extends Migration
{
    public function up()
    {
        $tripExpenseAccount = Account::where('name', 'Trip Expense')->first();
        if (! $tripExpenseAccount) {
            return;
        }

        $resync = app(LedgerResyncService::class);
        $fixed = 0;

        $bills = Bill::with('trip_expense.expense.account', 'trip_expense.allowance.account', 'bill_expenses')
            ->where('category', 'Trip Expense')
            ->whereNotNull('trip_expense_id')
            ->whereHas('bill_expenses', fn ($q) => $q->where('account_id', $tripExpenseAccount->id))
            ->get();

        foreach ($bills as $bill) {
            $tripExpense = $bill->trip_expense;
            if (! $tripExpense || $tripExpense->fuel_id || $tripExpense->transporter_id) {
                continue;
            }

            $account = optional($tripExpense->expense)->account ?: optional($tripExpense->allowance)->account;
            if (! $account || $account->id == $tripExpenseAccount->id) {
                continue;
            }

            try {
                DB::transaction(function () use ($bill, $account, $resync) {
                    $bill->bill_expenses()->update([
                        'account_id' => $account->id,
                        'account_type_id' => $account->account_type_id,
                    ]);
                    $bill->account_id = $account->id;
                    $bill->account_type_id = $account->account_type_id;
                    $bill->saveQuietly();

                    if ($bill->authorization === 'approved') {
                        $resync->resyncBill($bill->fresh(), "Bill {$bill->bill_number} re-pointed from Trip Expense to {$account->name}", true);
                    }
                });
                $fixed++;
            } catch (\Throwable $e) {
                echo "  failed: bill #{$bill->id} {$bill->bill_number} - {$e->getMessage()}\n";
            }
        }

        echo "  Re-pointed trip expense bills: {$fixed}\n";
    }

    public function down()
    {
        // Data repair - nothing to roll back.
    }
}
