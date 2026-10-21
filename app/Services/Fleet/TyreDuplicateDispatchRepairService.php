<?php

namespace App\Services\Fleet;

use App\Models\Product;
use App\Models\Tyre;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Before pending dispatches reserved tyres (2026-10-15), several dispatches could pick the same
 * tyre and all be approved. The books then show one tyre issued many times while other tyres of
 * the same product sit "in store" that are not on the ground.
 *
 * For each tyre on more than one approved dispatch line, the first approval keeps it. Every later
 * line is moved to the next in-store tyre of the same product (same store first, oldest first),
 * and that tyre leaves stores. Its ticket inventory, bill line, movements and fitment follow it.
 * No journal: the dispatch already credited inventory once per line, so taking one more tyre
 * out of stock makes the tyre stock match the ledger. A cost difference between the two tyres
 * is reported, not posted.
 *
 * Lines with no spare tyre of that product are left as they are and reported as a shortfall.
 */
class TyreDuplicateDispatchRepairService
{
    /**
     * @return Collection<array> one row per duplicate line: dispatch, item, old/new tyre, cost diff or shortfall
     */
    public function run(bool $apply): Collection
    {
        $rows = collect();
        $pools = [];
        $taken = [];

        foreach ($this->duplicateLines() as $tyreId => $lines) {
            $old = Tyre::withTrashed()->find($tyreId);

            // The first approval keeps the tyre.
            foreach ($lines->slice(1) as $line) {
                $pid = (int) optional($old)->product_id;
                $pools[$pid] ??= $this->spareTyres($pid);
                $new = $this->pick($pools[$pid], $old, $taken);

                $row = [
                    'dispatch' => $line->dispatch_number,
                    'item_id' => $line->item_id,
                    'old' => optional($old)->tyre_number ?? "#{$tyreId}",
                    'new' => optional($new)->tyre_number,
                    'product' => optional(Product::withTrashed()->find($pid))->name,
                    'cost_diff' => $new ? round($this->unitCost($new) - $this->unitCost($old), 2) : null,
                ];

                if ($new) {
                    $taken[$new->id] = true;
                    if ($apply) {
                        DB::transaction(fn () => $this->repoint($line, $tyreId, $new));
                    }
                }

                $rows->push($row);
            }
        }

        return $rows;
    }

    /** Approved tyre dispatch lines grouped by tyre, only tyres on more than one, first approval first. */
    private function duplicateLines(): Collection
    {
        $dupIds = DB::table('dispatch_items as di')
            ->join('dispatches as d', 'd.id', '=', 'di.dispatch_id')
            ->whereNull('di.deleted_at')->whereNull('d.deleted_at')
            ->where('d.authorization', 'approved')
            ->whereNotNull('di.tyre_id')
            ->groupBy('di.tyre_id')
            ->havingRaw('count(*) > 1')
            ->pluck('di.tyre_id');

        return DB::table('dispatch_items as di')
            ->join('dispatches as d', 'd.id', '=', 'di.dispatch_id')
            ->whereNull('di.deleted_at')->whereNull('d.deleted_at')
            ->where('d.authorization', 'approved')
            ->whereIn('di.tyre_id', $dupIds)
            ->orderBy('d.authorization_date')->orderBy('d.id')->orderBy('di.id')
            ->get(['di.id as item_id', 'di.tyre_id', 'd.id as dispatch_id', 'd.dispatch_number', 'd.ticket_id'])
            ->groupBy('tyre_id');
    }

    private function spareTyres(int $productId): Collection
    {
        return Tyre::dispatchable()->where('product_id', $productId)->orderBy('id')->get();
    }

    private function pick(Collection $pool, ?Tyre $old, array $taken): ?Tyre
    {
        $free = $pool->reject(fn ($t) => isset($taken[$t->id]));

        return $free->firstWhere('store_id', optional($old)->store_id) ?? $free->first();
    }

    private function unitCost(?Tyre $tyre): float
    {
        if (!$tyre) {
            return 0;
        }
        $qty = (float) $tyre->qty ?: 1;

        return (float) ($tyre->exchange_amount ?: $tyre->total) / $qty;
    }

    private function repoint(object $line, int $oldId, Tyre $new): void
    {
        DB::table('dispatch_items')->where('id', $line->item_id)->update(['tyre_id' => $new->id]);

        DB::table('ticket_inventories')
            ->where('dispatch_item_id', $line->item_id)->where('tyre_id', $oldId)
            ->update(['tyre_id' => $new->id]);

        // This dispatch's bill line for the tyre (one per dispatch line).
        $billLine = DB::table('bill_expenses as be')
            ->join('bills as b', 'b.id', '=', 'be.bill_id')
            ->where('b.dispatch_id', $line->dispatch_id)->where('be.tyre_id', $oldId)
            ->whereNull('be.deleted_at')
            ->orderBy('be.id')->value('be.id');
        if ($billLine) {
            DB::table('bill_expenses')->where('id', $billLine)->update(['tyre_id' => $new->id]);
        }

        // Fitment made on approval: linked through its movement, else (older approvals) the ticket.
        $assignmentIds = DB::table('movements')
            ->where('dispatch_item_id', $line->item_id)->where('tyre_id', $oldId)
            ->whereNotNull('tyre_assignment_id')
            ->pluck('tyre_assignment_id');
        if ($assignmentIds->isEmpty() && $line->ticket_id) {
            $assignmentIds = DB::table('tyre_assignments')
                ->where('tyre_id', $oldId)->where('ticket_id', $line->ticket_id)
                ->whereNull('deleted_at')->pluck('id');
        }

        DB::table('tyre_assignments')->whereIn('id', $assignmentIds)->update(['tyre_id' => $new->id]);
        DB::table('movements')
            ->where('tyre_id', $oldId)
            ->where(fn ($q) => $q->where('dispatch_item_id', $line->item_id)->orWhereIn('tyre_assignment_id', $assignmentIds))
            ->update(['tyre_id' => $new->id]);

        DB::table('tyres')->where('id', $new->id)->update(['balance' => 0, 'status' => 0]);
    }
}
