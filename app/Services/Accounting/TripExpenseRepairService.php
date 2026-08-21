<?php

namespace App\Services\Accounting;

use App\Models\Expense;
use App\Models\Trip;
use App\Models\TripExpense;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Repairs a trip's auto-managed TripExpense rows (the "Fuel Topup" and
 * "Transporter Payment" ones the app creates itself - identified by
 * fuel_id/transporter_id, not by anything the user picked) after the
 * Expense row they pointed at was deleted and later recreated with a new
 * id by re-running AccountSeeder/ExpenseSeeder. A recreated seeded Expense
 * gets a new id, so the old expense_id is left dangling on the trip - the
 * bill/bill_expense end up with a null account_id, and BillJournalService
 * silently skips the debit leg for that line (Trial Balance only shows the
 * Accounts Payable credit, never the expense debit).
 *
 * Deliberately does NOT touch manually-picked trip expenses (added via
 * Trips\Expenses, scoped to the "Trip Expense" account) - there's no way to
 * know what they used to point at if that row was also wiped, so guessing
 * would silently reclassify the wrong thing. Call from Trips\Edit::update()
 * so a normal trip save doubles as the repair trigger.
 */
class TripExpenseRepairService
{
    public function __construct(
        private LedgerResyncService $ledgerResync,
        private FuelJournalService $fuelJournal
    ) {
    }

    /**
     * @return int[] ids of the TripExpense rows that were relinked
     */
    public function repair(Trip $trip): array
    {
        $repaired = [];

        $tripExpenses = TripExpense::where('trip_id', $trip->id)->get();

        foreach ($tripExpenses as $tripExpense) {
            if ($tripExpense->fuel_id) {
                if ($this->repairFuel($tripExpense, $trip)) {
                    $repaired[] = $tripExpense->id;
                }
                continue;
            }

            if ($tripExpense->transporter_id) {
                if ($this->repairTransporter($tripExpense, $trip)) {
                    $repaired[] = $tripExpense->id;
                }
                continue;
            }
        }

        return $repaired;
    }

    /**
     * Fuel-linked trip expenses always belong to a trip by construction, so
     * their GL routing must resolve to Fuel - COGS, never the generic
     * Fuel Topup Expense's own .account_id (which defaults to Fuel - Ops).
     * Delegates the Bill/BillExpense/JournalEntry rebuild to
     * FuelJournalService::postConsumption() - it already resolves
     * COGS/Ops and Once-Off-Buy/Bulk-Buy correctly and is idempotent, so
     * there's no need to duplicate that logic here.
     */
    private function repairFuel(TripExpense $tripExpense, Trip $trip): bool
    {
        if ($this->isCorrectlyLinked($tripExpense, 'Fuel Topup')) {
            return false;
        }

        $correctExpense = Expense::where('name', 'Fuel Topup')->first();

        if (!$correctExpense) {
            Log::warning("TripExpenseRepairService: 'Fuel Topup' expense not found while repairing trip #{$trip->id}, trip_expense #{$tripExpense->id}");
            return false;
        }

        $fuel = $tripExpense->fuel;

        if (!$fuel) {
            Log::warning("TripExpenseRepairService: trip_expense #{$tripExpense->id} has fuel_id but no Fuel row, skipping");
            return false;
        }

        DB::transaction(function () use ($tripExpense, $correctExpense, $fuel) {
            $tripExpense->expense_id = $correctExpense->id;
            $tripExpense->save();

            $this->fuelJournal->postConsumption($fuel->fresh());
        });

        return true;
    }

    private function repairTransporter(TripExpense $tripExpense, Trip $trip): bool
    {
        if ($this->isCorrectlyLinked($tripExpense, 'Transporter Payment')) {
            return false;
        }

        $correctExpense = Expense::where('name', 'Transporter Payment')->first();

        if (!$correctExpense) {
            Log::warning("TripExpenseRepairService: 'Transporter Payment' expense not found while repairing trip #{$trip->id}, trip_expense #{$tripExpense->id}");
            return false;
        }

        DB::transaction(function () use ($tripExpense, $correctExpense, $trip) {
            $tripExpense->expense_id = $correctExpense->id;
            $tripExpense->save();

            $bill = $tripExpense->bill;

            if ($bill) {
                $billExpense = $bill->bill_expenses()->first();

                if ($billExpense) {
                    $billExpense->expense_id = $correctExpense->id;
                    $billExpense->account_id = $correctExpense->account_id;
                    $billExpense->save();
                }

                $this->ledgerResync->resyncBill(
                    $bill,
                    "Trip #{$trip->id} expense relink repair (expense_id recreated by seeder)",
                    force: true
                );
            }
        });

        return true;
    }

    private function isCorrectlyLinked(TripExpense $tripExpense, string $expectedName): bool
    {
        if (!$tripExpense->expense_id) {
            return false;
        }

        return Expense::where('id', $tripExpense->expense_id)
            ->where('name', $expectedName)
            ->exists();
    }
}
