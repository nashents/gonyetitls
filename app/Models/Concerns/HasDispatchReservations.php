<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Stock held by dispatches still awaiting approval. A dispatch only takes
 * balance down when it's approved (Dispatches\Pending), so until then the
 * qty on its pending lines is "reserved": it's still physically counted in
 * balance, but FIFO/new dispatches must not pick it again.
 *
 *   available_balance = balance - reserved_qty
 *
 * Used by Inventory and Asset. Tyres are whole units and are already held by
 * Tyre::scopeDispatchable() (excluded while on a pending dispatch).
 */
trait HasDispatchReservations
{
    public function pendingDispatchItems()
    {
        return $this->dispatch_items()
            ->whereHas('dispatch', fn ($d) => $d->where('authorization', 'pending'));
    }

    // Eager-loads reserved_qty as a column (avoids a query per row in lists).
    public function scopeWithReservedQty(Builder $query): Builder
    {
        return $query->withSum([
            'dispatch_items as reserved_qty' => fn ($q) => $q->whereHas('dispatch', fn ($d) => $d->where('authorization', 'pending')),
        ], 'qty');
    }

    // Rows with something left after pending dispatches are taken out.
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('balance', '>', 0)
            ->whereRaw($this->getTable().'.balance > '.$this->reservedQtySql());
    }

    public function getReservedQtyAttribute($value): float
    {
        // Loaded by withReservedQty() - null there just means nothing reserved.
        if (array_key_exists('reserved_qty', $this->attributes)) {
            return (float) $value;
        }

        return (float) $this->pendingDispatchItems()->sum('qty');
    }

    public function getAvailableBalanceAttribute(): float
    {
        return max(0, (float) $this->balance - $this->reserved_qty);
    }

    private function reservedQtySql(): string
    {
        $table = $this->getTable();
        $foreignKey = $this->dispatch_items()->getForeignKeyName();

        return "COALESCE((SELECT SUM(di.qty) FROM dispatch_items di
            INNER JOIN dispatches d ON d.id = di.dispatch_id
            WHERE di.{$foreignKey} = {$table}.id
              AND d.authorization = 'pending'
              AND di.deleted_at IS NULL
              AND d.deleted_at IS NULL), 0)";
    }
}
