<?php

namespace App\Console\Commands;

use App\Services\Inventory\OverDispatchRepairService;
use Illuminate\Console\Command;

/**
 * Takes stock that was dispatched but never deducted (batch issued for more than it received)
 * off the remaining batches of the same product and store. See OverDispatchRepairService.
 *
 * php artisan stock:repair-over-dispatches --dry-run
 * php artisan stock:repair-over-dispatches --force
 */
class RepairOverDispatchedStock extends Command
{
    protected $signature = 'stock:repair-over-dispatches
        {--dry-run : Preview only, fix nothing}
        {--force : Skip the confirmation prompt}';

    protected $description = 'Deduct stock that approved dispatches issued beyond what their batch received';

    public function handle(OverDispatchRepairService $service)
    {
        $rows = $service->run(false);

        if ($rows->isEmpty()) {
            $this->info('No batch has been dispatched for more than it received.');
            return 0;
        }

        $this->table(['Type', 'Batch', 'Product', 'Store', 'Received', 'Dispatched', 'Over', 'Deduct from (batch: qty)', 'Shortfall'], $rows->map(fn ($r) => [
            $r['type'], $r['batch_id'], $r['product'], $r['store_id'], $r['received'], $r['dispatched'], $r['over'],
            collect($r['takes'])->map(fn ($qty, $id) => "{$id}: {$qty}")->implode(', ') ?: '-',
            $r['shortfall'] ?: '',
        ]));

        $this->line("{$rows->count()} batch(es) over-dispatched by {$rows->sum('over')}: {$rows->sum('taken')} can be deducted, {$rows->sum('shortfall')} shortfall.");
        foreach ($rows->where('shortfall', '>', 0)->groupBy('product') as $product => $group) {
            $this->warn("  Shortfall {$product}: {$group->sum('shortfall')} issued that the store has no stock left to cover.");
        }
        if ($reserved = $rows->sum('reserved_used')) {
            $this->warn("{$reserved} comes out of stock that pending dispatches have reserved.");
        }

        if ($this->option('dry-run') || (!$this->option('force') && !$this->confirm('Apply?'))) {
            return 0;
        }

        $service->run(true);
        $this->info('Done.');

        return 0;
    }
}
