<?php

namespace App\Console\Commands;

use App\Services\Accounting\FuelLedgerRepairService;
use Illuminate\Console\Command;

class RepairFuelLedgerMismatches extends Command
{
    /**
     * php artisan fuel:repair-ledger-mismatches --dry-run
     * php artisan fuel:repair-ledger-mismatches
     * php artisan fuel:repair-ledger-mismatches --fuel=1234 --fuel=1240 --force
     */
    protected $signature = 'fuel:repair-ledger-mismatches
        {--dry-run : Preview only, fix nothing}
        {--force : Skip the confirmation prompt}
        {--fuel=* : Limit to these fuel order IDs (repeatable)}';

    protected $description = "Resync approved fuel orders whose Bill/JournalEntry fell out of sync with the fuel order's own current exchange_rate/amount/currency (e.g. a wrong exchange rate was corrected after approval)";

    public function handle(FuelLedgerRepairService $service)
    {
        $fuelIds = $this->option('fuel') ? array_map('intval', $this->option('fuel')) : null;

        $mismatches = $service->mismatches($fuelIds);

        if ($mismatches->isEmpty()) {
            $this->info('No fuel order / bill mismatches found.');

            return self::SUCCESS;
        }

        $this->table(
            ['Fuel #', 'Order #', 'Bill #', 'Bill Total', 'Fuel Amount', 'Bill Rate', 'Fuel Rate', 'Currency (Bill/Fuel)'],
            $mismatches->map(fn ($m) => [
                $m['fuel_id'],
                $m['order_number'],
                $m['bill_number'],
                number_format($m['bill_total'], 2),
                number_format($m['fuel_amount'], 2),
                $m['bill_exchange_rate'],
                $m['fuel_exchange_rate'],
                $m['bill_currency_id'] . ' / ' . $m['fuel_currency_id'],
            ])->all()
        );

        if ($this->option('dry-run')) {
            $this->warn(
                count($mismatches) . ' fuel order(s) would be resynced - Bill/BillExpense updated to the fuel '
                . "order's current figures, any existing JournalEntry reversed, and a fresh correct one posted. "
                . 'Nothing has changed. Re-run without --dry-run to apply.'
            );

            return self::SUCCESS;
        }

        if (!$this->option('force') && !$this->confirm(
            'Apply the fix to ' . count($mismatches) . ' fuel order(s) above? '
            . 'This reverses each existing JournalEntry and posts a fresh one.'
        )) {
            $this->line('Aborted - nothing changed.');

            return self::SUCCESS;
        }

        $result = $service->repairAll($fuelIds);

        $this->info('Fixed ' . count($result['fixed']) . " / {$result['total']} fuel order(s).");

        foreach ($result['fixed'] as $fixed) {
            $this->line("  #{$fixed['fuel_id']}: posted {$fixed['journal_number']}");
        }

        foreach ($result['errors'] as $error) {
            $this->error("  #{$error['fuel_id']}: {$error['message']}");
        }

        return self::SUCCESS;
    }
}
