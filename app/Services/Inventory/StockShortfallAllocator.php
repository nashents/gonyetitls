<?php

namespace App\Services\Inventory;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Takes stock that a batch (inventory / asset row) could not supply from the other batches of
 * the same product in the same store, oldest first, and keeps a trail of it in
 * stock_shortfall_allocations.
 *
 * Stock that pending dispatches have reserved is only touched once every unreserved unit is used.
 * A quantity no batch can cover is logged with no donor, so it is visible and never retried.
 *
 * Used by dispatch approval (a line bigger than what its batch has left), by dispatch reversal
 * (to hand the stock back to the batches it came from) and by OverDispatchRepairService.
 */
class StockShortfallAllocator
{
    /** Stock tables that hold a quantity balance => their column on dispatch_items. */
    public const TABLES = ['inventories' => 'inventory_id', 'assets' => 'asset_id'];

    private const EPSILON = 0.0001;

    /** [table.column => exists] - not every install has every column (assets.store_id is missing on some). */
    private static array $columns = [];

    public static function hasColumn(string $table, string $column): bool
    {
        return self::$columns["{$table}.{$column}"] ??= Schema::hasColumn($table, $column);
    }

    /**
     * Work out where $qty can come from. Nothing is written.
     *
     * @param  object  $source  the short batch (id, product_id, store_id)
     * @param  array  $planned  [batch id => qty] already planned in this run, not yet written
     * @param  bool  $ownFirst  also use what the short batch itself still shows (repairs only)
     * @return array{takes: array<int, float>, uncovered: float, reserved_used: float}
     */
    public function plan(string $table, object $source, float $qty, array $planned = [], bool $ownFirst = false): array
    {
        $takes = [];
        $reservedUsed = 0.0;
        $need = round($qty, 4);
        $batches = $this->batches($table, $source, $ownFirst);

        // First pass leaves reserved stock alone, the second uses it.
        foreach ([false, true] as $useReserved) {
            foreach ($batches as $batch) {
                if ($need <= self::EPSILON) {
                    break 2;
                }

                $left = (float) $batch->balance - ($planned[$batch->id] ?? 0) - ($takes[$batch->id] ?? 0);
                $free = $useReserved ? $left : $left - (float) $batch->reserved;
                $take = round(min($need, $free), 4);
                if ($take <= self::EPSILON) {
                    continue;
                }

                $takes[$batch->id] = round(($takes[$batch->id] ?? 0) + $take, 4);
                $need = round($need - $take, 4);
                if ($useReserved) {
                    $reservedUsed += $take;
                }
            }
        }

        return ['takes' => $takes, 'uncovered' => max(0, $need), 'reserved_used' => round($reservedUsed, 4)];
    }

    /**
     * Take $qty out of the other batches and log it. Call inside a transaction.
     *
     * @return array same shape as plan()
     */
    public function cover(string $table, object $source, float $qty, string $reason, ?int $dispatchItemId = null, bool $ownFirst = false): array
    {
        $plan = $this->plan($table, $source, $qty, [], $ownFirst);

        foreach ($plan['takes'] as $batchId => $take) {
            $batch = DB::table($table)->where('id', $batchId)->lockForUpdate()->first();
            $balance = max(0, round((float) $batch->balance - $take, 4));

            DB::table($table)->where('id', $batchId)->update([
                'balance' => $balance,
                'status' => $balance <= 0 ? 0 : $batch->status,
                'updated_at' => now(),
            ]);

            $this->log($table, $source, $batchId, $take, $reason, $dispatchItemId);
        }

        if ($plan['uncovered'] > self::EPSILON) {
            $this->log($table, $source, null, $plan['uncovered'], $reason, $dispatchItemId);
        }

        return $plan;
    }

    /**
     * Dispatch reversal: put back what this dispatch line took from other batches.
     *
     * @return float the part of the line's qty that never came out of its own batch
     */
    public function release(int $dispatchItemId): float
    {
        $rows = DB::table('stock_shortfall_allocations')->where('dispatch_item_id', $dispatchItemId)->get();
        $total = 0.0;

        foreach ($rows as $row) {
            $total += (float) $row->qty;

            if ($row->donor_id && isset(self::TABLES[$row->item_type])) {
                $batch = DB::table($row->item_type)->where('id', $row->donor_id)->lockForUpdate()->first();
                if ($batch) {
                    DB::table($row->item_type)->where('id', $row->donor_id)->update([
                        'balance' => round((float) $batch->balance + (float) $row->qty, 4),
                        'status' => (int) $batch->status === 0 ? 1 : $batch->status,
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        DB::table('stock_shortfall_allocations')->where('dispatch_item_id', $dispatchItemId)->delete();

        return round($total, 4);
    }

    /** Batches of the same product and store that still show stock, oldest first, with their reserved qty. */
    private function batches(string $table, object $source, bool $ownFirst): Collection
    {
        if (!$source->product_id || !self::hasColumn($table, 'balance')) {
            return collect();
        }

        // A table with no store column is treated as one store.
        $byStore = self::hasColumn($table, 'store_id');
        $storeId = $source->store_id ?? null;

        $fk = self::TABLES[$table];

        $batches = DB::table("{$table} as b")
            ->where('b.product_id', $source->product_id)
            ->when($byStore, fn ($q) => $storeId ? $q->where('b.store_id', $storeId) : $q->whereNull('b.store_id'))
            ->whereNull('b.deleted_at')
            ->whereRaw('b.balance + 0 > 0')
            ->when(!$ownFirst, fn ($q) => $q->where('b.id', '!=', $source->id))
            ->orderBy('b.id')
            ->selectRaw("b.id, b.balance + 0 as balance, COALESCE((SELECT SUM(di.qty) FROM dispatch_items di
                INNER JOIN dispatches d ON d.id = di.dispatch_id
                WHERE di.{$fk} = b.id AND d.authorization = 'pending'
                  AND di.deleted_at IS NULL AND d.deleted_at IS NULL), 0) as reserved")
            ->get();

        // The short batch's own (overstated) balance goes first.
        return $ownFirst
            ? $batches->sortBy(fn ($b) => [(int) $b->id !== (int) $source->id, $b->id])->values()
            : $batches;
    }

    private function log(string $table, object $source, ?int $donorId, float $qty, string $reason, ?int $dispatchItemId): void
    {
        DB::table('stock_shortfall_allocations')->insert([
            'item_type' => $table,
            'item_id' => $source->id,
            'donor_id' => $donorId,
            'dispatch_item_id' => $dispatchItemId,
            'product_id' => $source->product_id,
            'qty' => $qty,
            'reason' => $reason,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
