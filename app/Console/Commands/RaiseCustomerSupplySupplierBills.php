<?php

namespace App\Console\Commands;

use App\Models\CustomerFuelSupply;
use App\Models\Fuel;
use App\Models\TopUp;
use App\Models\TripExpense;
use App\Models\User;
use App\Services\Accounting\CustomerFuelSupplyService;
use App\Services\Accounting\FuelJournalService;
use App\Services\Accounting\TripExpenseJournalService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Backfill for the customer-funded supplier bill (2026-10-23): supplies
 * posted before it went DR cost / CR AR with no bill. Re-syncs each one
 * through its source's normal posting path, which raises the supplier's
 * Bill (DR cost / CR AP vendor), reposts the supply as DR AP / CR AR and
 * settles the bill. Net ledger effect is unchanged; invoice allocations are
 * kept. Idempotent - already-billed supplies are just re-checked.
 */
class RaiseCustomerSupplySupplierBills extends Command
{
    protected $signature = 'customer-supplies:raise-supplier-bills
                            {--dry-run : List what would change without posting}
                            {--user= : User id recorded as poster (default: first user with an employee)}';

    protected $description = 'Raise the supplier bill (settled by the customer) for existing customer-funded fuel / trip expenses';

    public function handle(CustomerFuelSupplyService $customerFuel, FuelJournalService $fuelJournal, TripExpenseJournalService $expenseJournal): int
    {
        $user = $this->option('user') ? User::find($this->option('user')) : User::whereHas('employee')->orderBy('id')->first();
        if (!$user) {
            $this->error('No user to post as.');
            return self::FAILURE;
        }
        Auth::setUser($user);
        $dryRun = (bool) $this->option('dry-run');

        $rows = [];
        $done = $skipped = $failed = 0;

        CustomerFuelSupply::orderBy('id')->chunkById(200, function ($supplies) use ($customerFuel, $fuelJournal, $expenseJournal, $dryRun, &$rows, &$done, &$skipped, &$failed) {
            foreach ($supplies as $supply) {
                [$label, $run] = $this->source($supply, $customerFuel, $fuelJournal, $expenseJournal);

                if (!$run) {
                    $skipped++;
                    $rows[] = [$supply->supply_number, $label, '', '', (float) $supply->amount, 'skipped - source no longer customer funded / missing'];
                    continue;
                }

                if ($dryRun) {
                    $rows[] = [$supply->supply_number, $label, '', optional($supply->bill)->bill_number, (float) $supply->amount, $supply->bill_id ? 'has bill - recheck' : 'raise bill'];
                    continue;
                }

                try {
                    DB::transaction($run);
                    $supply = $supply->fresh(['bill.vendor']);
                    $bill = $supply ? $supply->bill : null;
                    $done++;
                    $rows[] = [
                        optional($supply)->supply_number,
                        $label,
                        $bill ? (optional($bill->vendor)->name ?? '— NO VENDOR') : '',
                        $bill ? $bill->bill_number : '',
                        (float) optional($supply)->amount,
                        $bill ? "bill {$bill->status}" : 'no bill (non-billable)',
                    ];
                } catch (\Throwable $e) {
                    $failed++;
                    $rows[] = [$supply->supply_number, $label, '', '', (float) $supply->amount, 'FAILED: ' . $e->getMessage()];
                }
            }
        });

        // Approved customer-funded fuel orders that never posted at all: the
        // trip approval screens posted their expenses before approving the
        // trip's fuel order, so its supply was skipped (FPFO00262, 2026-10-23).
        Fuel::where('authorization', 'approved')
            ->where('supplied_by_customer', true)
            ->whereNotNull('customer_id')
            ->whereNotIn('id', CustomerFuelSupply::whereNotNull('fuel_id')->select('fuel_id'))
            ->orderBy('id')
            ->get()
            ->filter(fn ($fuel) => $customerFuel->appliesToFuel($fuel))
            ->each(function ($fuel) use ($fuelJournal, $dryRun, &$rows, &$done, &$failed) {
                $label = "Fuel order {$fuel->order_number} (never posted)";
                if ($dryRun) {
                    $rows[] = ['—', $label, '', '', (float) $fuel->amount, 'post supply + raise bill'];
                    return;
                }
                try {
                    DB::transaction(fn () => $fuelJournal->postConsumption($fuel));
                    $supply = CustomerFuelSupply::with('bill.vendor')->where('fuel_id', $fuel->id)->first();
                    $done++;
                    $rows[] = [optional($supply)->supply_number, $label, optional(optional(optional($supply)->bill)->vendor)->name ?? '— NO VENDOR', optional(optional($supply)->bill)->bill_number, (float) $fuel->amount, 'posted'];
                } catch (\Throwable $e) {
                    $failed++;
                    $rows[] = ['—', $label, '', '', (float) $fuel->amount, 'FAILED: ' . $e->getMessage()];
                }
            });

        $this->table(['Supply', 'Source', 'Vendor', 'Bill', 'Amount', 'Result'], $rows);
        $this->info(($dryRun ? '[dry run] ' : '') . "Processed {$done}, skipped {$skipped}, failed {$failed}.");

        $noVendor = collect($rows)->where(2, '— NO VENDOR')->count();
        if ($noVendor) {
            $this->warn("{$noVendor} bill(s) have no vendor - link the fuel station to a vendor (or set the expense line's supplier) and re-run.");
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    /** [label, closure that re-posts the supply through its source] - closure null when it no longer applies. */
    private function source(CustomerFuelSupply $supply, CustomerFuelSupplyService $customerFuel, FuelJournalService $fuelJournal, TripExpenseJournalService $expenseJournal): array
    {
        if ($supply->fuel_id) {
            $fuel = Fuel::find($supply->fuel_id);
            $label = 'Fuel order ' . optional($fuel)->order_number;

            return [$label, $fuel && $fuel->authorization === 'approved' && $customerFuel->appliesToFuel($fuel)
                ? fn () => $fuelJournal->postConsumption($fuel)
                : null];
        }

        if ($supply->top_up_id) {
            $topUp = TopUp::find($supply->top_up_id);
            $label = 'Top up ' . optional($topUp)->order_number;

            return [$label, $topUp && $customerFuel->appliesToTopUp($topUp)
                ? fn () => $fuelJournal->postTopUp($topUp)
                : null];
        }

        if ($supply->trip_expense_id) {
            $tripExpense = TripExpense::find($supply->trip_expense_id);
            $label = 'Trip expense #' . $supply->trip_expense_id . ($tripExpense && $tripExpense->trip ? ' - Trip ' . $tripExpense->trip->trip_number : '');

            return [$label, $tripExpense && $customerFuel->appliesToTripExpense($tripExpense)
                ? fn () => $expenseJournal->postExpense($tripExpense)
                : null];
        }

        return ['Unknown source', null];
    }
}
