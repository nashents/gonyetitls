<?php

namespace App\Services\Inventory;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Dispatch approval used to stop a batch's balance at zero: a line approved against a batch that
 * other dispatches had already emptied took nothing out of stock. The goods left the store, but
 * the product's total stayed too high by the part the batch could not supply.
 *
 * A batch is over-dispatched when its approved dispatch lines add up to more than it received
 * (qty x weight, plus any stock-count surplus). That excess is taken off the batch's own balance
 * if it still shows one, then off the other batches of the same product in the same store,
 * oldest first (StockShortfallAllocator). What no batch can cover is reported as a shortfall.
 *
 * No journal: every dispatch already posted its full value, only the quantities were wrong.
 * Each correction is logged in stock_shortfall_allocations, so a second run finds nothing.
 */
class OverDispatchRepairService
{
    public function __construct(private StockShortfallAllocator $allocator)
    {
    }

    /**
     * @return Collection<array> one row per over-dispatched batch
     */
    public function run(bool $apply): Collection
    {
        $rows = collect();

        foreach (StockShortfallAllocator::TABLES as $table => $fk) {
            $planned = [];

            foreach ($this->overDispatched($table, $fk) as $batch) {
                $plan = $apply
                    ? DB::transaction(fn () => $this->allocator->cover($table, $batch, $batch->over, 'repair', null, true))
                    : $this->allocator->plan($table, $batch, $batch->over, $planned, true);

                foreach ($plan['takes'] as $id => $take) {
                    $planned[$id] = ($planned[$id] ?? 0) + $take;
                }

                $rows->push([
                    'type' => $table === 'inventories' ? 'Inventory' : 'Asset',
                    'batch_id' => $batch->id,
                    'product_id' => $batch->product_id,
                    'product' => $batch->product,
                    'store_id' => $batch->store_id,
                    'received' => $batch->received,
                    'dispatched' => (float) $batch->dispatched,
                    'over' => $batch->over,
                    'takes' => $plan['takes'],
                    'taken' => round(array_sum($plan['takes']), 4),
                    'shortfall' => round($plan['uncovered'], 4),
                    'reserved_used' => $plan['reserved_used'],
                ]);
            }
        }

        return $rows;
    }

    /** Batches whose approved dispatches exceed what they received, less what was already corrected. */
    private function overDispatched(string $table, string $fk): Collection
    {
        // Old lines can carry the id of a batch that was only created later (and of another
        // product). Those never came out of this batch, so they are not counted against it.
        $dispatched = DB::table('dispatch_items as di')
            ->join('dispatches as d', 'd.id', '=', 'di.dispatch_id')
            ->join("{$table} as src", 'src.id', '=', "di.{$fk}")
            ->whereNull('di.deleted_at')->whereNull('d.deleted_at')
            ->where('d.authorization', 'approved')
            ->whereColumn('di.created_at', '>=', 'src.created_at')
            ->where(fn ($q) => $q->whereNull('di.product_id')->orWhereColumn('di.product_id', 'src.product_id'))
            ->groupBy("di.{$fk}")
            ->selectRaw("di.{$fk} as batch_id, SUM(di.qty) as dispatched");

        // The trail table only exists once the migration has run (a dry run may come first).
        $handled = !Schema::hasTable('stock_shortfall_allocations') ? collect() : DB::table('stock_shortfall_allocations')
            ->where('item_type', $table)
            ->groupBy('item_id')
            ->selectRaw('item_id, SUM(qty) as handled')
            ->pluck('handled', 'item_id');

        $surplus = $this->countSurplus($table);

        return DB::table("{$table} as b")
            ->joinSub($dispatched, 'x', 'x.batch_id', '=', 'b.id')
            ->leftJoin('products as p', 'p.id', '=', 'b.product_id')
            ->whereNull('b.deleted_at')
            ->orderBy('b.id')
            ->get(['b.id', 'b.product_id', 'b.store_id', 'b.qty', 'b.weight', 'x.dispatched', 'p.name as product'])
            ->map(function ($batch) use ($handled, $surplus) {
                // Without a received quantity there is nothing to measure against.
                if (!is_numeric($batch->qty)) {
                    return null;
                }

                $weight = is_numeric($batch->weight) && (float) $batch->weight > 0 ? (float) $batch->weight : 1;
                $batch->received = round((float) $batch->qty * $weight + (float) ($surplus[$batch->id] ?? 0), 4);
                $batch->over = round((float) $batch->dispatched - $batch->received - (float) ($handled[$batch->id] ?? 0), 4);

                return $batch->over > 0.0001 ? $batch : null;
            })
            ->filter()
            ->values();
    }

    /** Stock approved stock counts added to a batch (surpluses only), by inventory id. */
    private function countSurplus(string $table): Collection
    {
        if ($table !== 'inventories' || !Schema::hasTable('stock_adjustment_items')) {
            return collect();
        }

        return DB::table('stock_adjustment_items as s')
            ->join('stock_adjustments as a', 'a.id', '=', 's.stock_adjustment_id')
            ->where('a.status', 'approved')
            ->whereNull('a.deleted_at')
            ->whereNotNull('s.counted_qty')
            ->whereColumn('s.counted_qty', '>', 's.system_qty')
            ->groupBy('s.inventory_id')
            ->selectRaw('s.inventory_id, SUM(s.counted_qty - s.system_qty) as surplus')
            ->pluck('surplus', 'inventory_id');
    }
}
