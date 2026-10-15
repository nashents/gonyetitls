<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Tyres fitted straight from stores under the old rules were set to balance 0 by
 * 2026_10_14_000001, but they were never issued: no approved dispatch, cost still in inventory.
 * Once unassigned, a pending dispatch for them can't be approved ("not in stock").
 * Put those tyres back in stock so the dispatch can issue them.
 *
 * Only tyres that are: on a pending dispatch, never on an approved dispatch, fitted before
 * (have an assignment) but not fitted now, not disposed, and not imported with qty 0.
 */
return new class extends Migration
{
    public function up()
    {
        $ids = DB::table('tyres')
            ->whereNull('tyres.deleted_at')
            ->where(fn ($q) => $q->whereNull('balance')->orWhere('balance', '<=', 0))
            ->where(fn ($q) => $q->whereNull('disposed')->orWhere('disposed', 0))
            ->where(fn ($q) => $q->whereNull('qty')->orWhere('qty', '!=', 0))
            ->whereExists(fn ($q) => $q->from('dispatch_items as di')
                ->join('dispatches as d', 'd.id', '=', 'di.dispatch_id')
                ->whereColumn('di.tyre_id', 'tyres.id')
                ->whereNull('di.deleted_at')->whereNull('d.deleted_at')
                ->where('d.authorization', 'pending'))
            ->whereNotExists(fn ($q) => $q->from('dispatch_items as di')
                ->join('dispatches as d', 'd.id', '=', 'di.dispatch_id')
                ->whereColumn('di.tyre_id', 'tyres.id')
                ->whereNull('di.deleted_at')->whereNull('d.deleted_at')
                ->where('d.authorization', 'approved'))
            ->whereExists(fn ($q) => $q->from('tyre_assignments as ta')
                ->whereColumn('ta.tyre_id', 'tyres.id')
                ->whereNull('ta.deleted_at'))
            ->whereNotExists(fn ($q) => $q->from('tyre_assignments as ta')
                ->whereColumn('ta.tyre_id', 'tyres.id')
                ->whereNull('ta.deleted_at')
                ->where('ta.status', 1))
            ->pluck('id');

        if ($ids->isEmpty()) {
            return;
        }

        DB::table('tyres')->whereIn('id', $ids)->update(['balance' => 1, 'status' => 1]);

        Log::info('Restocked undispatched tyres on pending dispatches: '.
            DB::table('tyres')->whereIn('id', $ids)->pluck('tyre_number')->implode(', '));
    }

    public function down()
    {
        // Data fix; not reversed.
    }
};
