<?php

namespace App\Console\Commands;

use App\Services\Fleet\TyreDuplicateDispatchRepairService;
use Illuminate\Console\Command;

/**
 * Moves the 2nd, 3rd... approved dispatch of the same tyre onto the next in-store tyre of the
 * same product. See TyreDuplicateDispatchRepairService.
 *
 * php artisan tyres:repair-duplicate-dispatches --dry-run
 * php artisan tyres:repair-duplicate-dispatches --force
 */
class RepairDuplicateTyreDispatches extends Command
{
    protected $signature = 'tyres:repair-duplicate-dispatches
        {--dry-run : Preview only, fix nothing}
        {--force : Skip the confirmation prompt}';

    protected $description = 'Re-issue tyres dispatched more than once from the next in-store tyre of the same product';

    public function handle(TyreDuplicateDispatchRepairService $service)
    {
        $rows = $service->run(false);

        if ($rows->isEmpty()) {
            $this->info('No tyre is on more than one approved dispatch.');
            return 0;
        }

        $this->table(['Dispatch', 'Line', 'Tyre now', 'Re-issue as', 'Product', 'Cost diff'], $rows->map(fn ($r) => [
            $r['dispatch'], $r['item_id'], $r['old'], $r['new'] ?? 'SHORTFALL - none in store', $r['product'], $r['cost_diff'],
        ]));

        $short = $rows->whereNull('new');
        $this->line("{$rows->count()} duplicate line(s): ".($rows->count() - $short->count())." can be re-issued, {$short->count()} shortfall.");
        foreach ($short->groupBy('product') as $product => $group) {
            $this->warn("  Shortfall {$product}: {$group->count()} tyre(s) issued that stores doesn't hold.");
        }
        $diff = $rows->whereNotNull('new')->sum('cost_diff');
        if (abs($diff) >= 0.01) {
            $this->warn("Replacement tyres cost {$diff} more than the posted dispatch lines in total (not posted).");
        }

        if ($this->option('dry-run') || (!$this->option('force') && !$this->confirm('Apply?'))) {
            return 0;
        }

        $service->run(true);
        $this->info('Done.');

        return 0;
    }
}
