<?php

namespace App\Console\Commands;

use App\Models\Bill;
use App\Models\Trip;
use App\Models\TripExpense;
use App\Models\User;
use App\Services\Accounting\TransporterPaymentBillService;
use App\Services\Accounting\TripExpenseJournalService;
use App\Services\Accounting\TripExpenseRecoveryService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Backfill for TripExpenseRecoveryService (2026-10-16): existing Transporter /
 * Customer category trip-expense bills were posted to expense accounts.
 * Re-points them to Transporter / Customer Recoverable (reverse + repost via
 * TripExpenseJournalService) and re-syncs approved trips' transporter payment
 * bills so deduct mode takes their deductions.
 */
class ApplyTripExpenseRecoverables extends Command
{
    protected $signature = 'trip-expenses:apply-recoverables
                            {--dry-run : Report what would change without posting}
                            {--user= : User id recorded as poster (default: first user with an employee)}';

    protected $description = 'Move Transporter/Customer category trip expense bills to the Recoverable accounts and re-sync transporter payment deductions';

    public function handle(TripExpenseRecoveryService $recovery, TripExpenseJournalService $journal, TransporterPaymentBillService $transporterBills): int
    {
        $user = $this->option('user') ? User::find($this->option('user')) : User::whereHas('employee')->orderBy('id')->first();
        if (!$user) {
            $this->error('No user to post as.');
            return self::FAILURE;
        }
        Auth::setUser($user);
        $dryRun = (bool) $this->option('dry-run');

        $repointed = 0;
        $rows = [];
        TripExpense::whereIn('category', ['Transporter', 'Customer'])->whereNull('fuel_id')->whereNull('transporter_id')
            ->orderBy('id')->chunkById(200, function ($expenses) use ($recovery, $journal, $dryRun, &$repointed, &$rows) {
                foreach ($expenses as $te) {
                    $target = $recovery->recoverableAccountFor($te);
                    $bill = Bill::where('trip_expense_id', $te->id)->first();
                    if (!$target || !$bill) {
                        continue;
                    }
                    $current = $bill->bill_expenses()->value('account_id');
                    if ((int) $current === (int) $target->id) {
                        continue;
                    }
                    $repointed++;
                    $rows[] = [$te->id, optional($te->trip)->trip_number, $te->category, $bill->bill_number, (float) $te->amount, $target->name];
                    if (!$dryRun) {
                        try {
                            DB::transaction(fn () => $journal->postExpense($te->fresh()));
                        } catch (\Throwable $e) {
                            $this->warn("Trip expense #{$te->id}: " . $e->getMessage());
                        }
                    }
                }
            });

        // Customer-funded Transporter lines post DR <account> / CR AR through
        // CustomerFuelSupply - re-point those to Transporter Recoverable too.
        $recoverableId = $recovery->transporterRecoverable()->id;
        \App\Models\CustomerFuelSupply::whereNotNull('trip_expense_id')
            ->where('account_id', '!=', $recoverableId)
            ->whereHas('trip_expense', fn ($q) => $q->where('category', 'Transporter')->where('supplied_by_customer', true)->whereNull('fuel_id')->whereNull('transporter_id'))
            ->orderBy('id')->get()
            ->each(function ($supply) use ($journal, $dryRun, &$repointed, &$rows) {
                $te = $supply->trip_expense;
                $repointed++;
                $rows[] = [$te->id, optional($te->trip)->trip_number, 'Transporter (customer funded)', $supply->supply_number, (float) $te->amount, 'Transporter Recoverable'];
                if (!$dryRun) {
                    try {
                        DB::transaction(fn () => $journal->postExpense($te->fresh()));
                    } catch (\Throwable $e) {
                        $this->warn("Trip expense #{$te->id}: " . $e->getMessage());
                    }
                }
            });

        // Transporter fuel orders posted to Fuel - COGS: repost to Transporter
        // Recoverable (with the Business Setting's markup, if any).
        \App\Models\Fuel::where('authorization', 'approved')->whereNotNull('trip_id')
            ->whereHas('trip_expense', fn ($q) => $q->where('category', 'Transporter'))
            ->orderBy('id')->get()
            ->filter(fn ($fuel) => $recovery->isTransporterFuel($fuel))
            ->each(function ($fuel) use ($recoverableId, $dryRun, &$repointed, &$rows) {
                $bill = Bill::where('fuel_id', $fuel->id)->first();
                if (!$bill || $bill->bill_expenses()->where('account_id', $recoverableId)->exists()) {
                    return;
                }
                $repointed++;
                $rows[] = [optional($fuel->trip_expense)->id, optional($fuel->trip)->trip_number, 'Transporter (fuel)', $bill->bill_number, (float) $fuel->amount, 'Transporter Recoverable'];
                if (!$dryRun) {
                    try {
                        app(\App\Services\Accounting\FuelJournalService::class)->postConsumption($fuel);
                    } catch (\Throwable $e) {
                        $this->warn("Fuel order {$fuel->order_number}: " . $e->getMessage());
                    }
                }
            });

        if ($rows) {
            $this->table(['Trip expense', 'Trip', 'Category', 'Bill / Supply', 'Amount', 'Move to'], array_slice($rows, 0, 200));
        }
        $this->info(($dryRun ? 'Would re-point: ' : 'Re-pointed: ') . "{$repointed} bill(s).");

        // Transporter payment bills: pick up deductions (deduct mode) / drop them (invoice mode)
        $trips = Trip::where('authorization', 'approved')
            ->whereHas('trip_expenses', fn ($q) => $q->whereNotNull('transporter_id'))
            ->whereHas('trip_expenses', fn ($q) => $q->where('category', 'Transporter')->whereNull('transporter_id'))
            ->get();
        $this->info(($dryRun ? 'Would re-sync ' : 'Re-syncing ') . $trips->count() . ' transporter payment bill trip(s).');
        if (!$dryRun) {
            foreach ($trips as $trip) {
                $warning = null;
                $transporterBills->syncTrip($trip, $warning);
                if ($warning) {
                    $this->warn($warning);
                }
            }
        }

        return self::SUCCESS;
    }
}
