<?php

namespace App\Services\GoodsReturneds;

use App\Models\Asset;
use App\Models\GoodsReturnedItem;
use App\Models\Inventory;
use App\Models\Tyre;

/**
 * How much of a given GRV line (an Inventory/Asset/Tyre row) can still be
 * returned. A line may already have been partly dispatched elsewhere, so
 * the ceiling is what was actually received AND what's still on hand -
 * never just the original received qty. Used both when a draft is being
 * built (to cap/display the input) and again, under a row lock, at
 * approval time (the real concurrency guard against two drafts returning
 * the same physical stock).
 */
class ReturnableQuantityResolver
{
    public static function forLine(Inventory|Asset|Tyre $line, ?int $excludeGoodsReturnedId = null): float
    {
        $qty = is_numeric($line->qty ?? null) ? (float) $line->qty : 0;
        $balance = is_numeric($line->balance ?? null) ? (float) $line->balance : $qty;
        $ceiling = min($qty, $balance);

        $column = match (true) {
            $line instanceof Inventory => 'inventory_id',
            $line instanceof Asset => 'asset_id',
            $line instanceof Tyre => 'tyre_id',
        };

        $alreadyReturned = GoodsReturnedItem::where($column, $line->id)
            ->whereHas('goods_returned', function ($q) use ($excludeGoodsReturnedId) {
                // Draft (authorization IS NULL) and pending/approved returns
                // all still reserve the qty; only a rejected return releases
                // it. "!=" against NULL never matches in SQL, so it must be
                // spelled out explicitly here.
                $q->where(function ($qq) {
                    $qq->whereNull('authorization')->orWhere('authorization', '!=', 'rejected');
                });
                if ($excludeGoodsReturnedId) {
                    $q->where('id', '!=', $excludeGoodsReturnedId);
                }
            })
            ->sum('qty_returned');

        return max(0, $ceiling - (float) $alreadyReturned);
    }
}
