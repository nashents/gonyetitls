<?php

namespace App\Services\GoodsReceiveds;

use App\Models\Bill;
use App\Models\GoodsReceived;
use App\Models\Payment;
use App\Services\Accounting\BillDeletionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

/**
 * Undoes an approved GRV: its inventory / tyre / asset rows leave stock
 * (status 0, nothing on hand), the supplier bill approval raised is removed
 * with its journal reversed (Spares Inventory / Accounts Payable), and the
 * GRV ends up rejected with who reversed it and why.
 *
 * Refused while anything has already happened to the received goods or the
 * bill - issued, transferred, sold, assigned, returned, paid - since taking
 * the stock back would then leave those records pointing at nothing.
 */
class GoodsReceivedReversalService
{
    /** Live records that mean an item from the GRV has been used. */
    protected const USAGE_TABLES = [
        'inventory_dispatches', 'tyre_dispatches', 'asset_dispatches',
        'transfer_items', 'sale_items', 'invoice_items', 'ticket_inventories',
        'inventory_assignments', 'tyre_assignments', 'asset_assignments',
        'disposes', 'breakages', 'retread_items', 'goods_returned_items',
    ];

    public function __construct(private BillDeletionService $billDeletion)
    {
    }

    /** Why this GRV can't be reversed - empty when it can. */
    public function blockers(GoodsReceived $goodsReceived): array
    {
        $reasons = [];

        if ($goodsReceived->authorization !== 'approved') {
            return ['Only approved GRVs can be reversed.'];
        }

        $items = $this->items($goodsReceived);

        foreach ($items as [$column, $model]) {
            $label = $this->label($model);

            if ($column === 'inventory_id') {
                $received = (float) $model->qty * (is_numeric($model->weight) ? (float) $model->weight : 1);
                if (is_numeric($model->balance) && (float) $model->balance < $received - 0.0001) {
                    $reasons[] = "{$label} has been partly or fully used (on hand " . (float) $model->balance . " of {$received}).";
                    continue;
                }
            }

            $liveDispatch = DB::table('dispatch_items')
                ->join('dispatches', 'dispatches.id', '=', 'dispatch_items.dispatch_id')
                ->where("dispatch_items.{$column}", $model->id)
                ->whereNull('dispatch_items.deleted_at')
                ->whereNull('dispatches.deleted_at')
                ->whereNull('dispatches.reversed_at')
                ->where(fn ($q) => $q->whereNull('dispatches.authorization')->orWhere('dispatches.authorization', '!=', 'rejected'))
                ->exists();
            if ($liveDispatch) {
                $reasons[] = "{$label} is on a dispatch.";
                continue;
            }

            foreach (self::USAGE_TABLES as $table) {
                if (!Schema::hasColumn($table, $column)) {
                    continue;
                }
                $query = DB::table($table)->where($column, $model->id);
                if (Schema::hasColumn($table, 'deleted_at')) {
                    $query->whereNull('deleted_at');
                }
                if ($query->exists()) {
                    $reasons[] = "{$label} has been used (" . str_replace('_', ' ', $table) . ").";
                    continue 2;
                }
            }
        }

        $bill = $this->bill($goodsReceived);
        if ($bill) {
            $paid = $bill->bill_payments()->exists()
                || Payment::where('bill_id', $bill->id)->exists()
                || (is_numeric($bill->balance) && is_numeric($bill->total) && (float) $bill->balance < (float) $bill->total - 0.009);
            if ($paid) {
                $reasons[] = "Supplier bill {$bill->bill_number} has payments against it - delete or reverse those first.";
            }
        }

        return $reasons;
    }

    public function reverse(GoodsReceived $goodsReceived, ?string $comments, int $userId): GoodsReceived
    {
        return DB::transaction(function () use ($goodsReceived, $comments, $userId) {
            $goodsReceived = GoodsReceived::lockForUpdate()->find($goodsReceived->id);

            if (! $goodsReceived) {
                throw ValidationException::withMessages(['goods_received' => 'This GRV could not be found.']);
            }

            $blockers = $this->blockers($goodsReceived);
            if ($blockers) {
                throw ValidationException::withMessages(['goods_received' => $blockers]);
            }

            $reason = "GRV {$goodsReceived->goods_received_number} reversed" . (filled($comments) ? ": {$comments}" : '');

            // Supplier bill + its Spares Inventory / AP journal
            if ($bill = $this->bill($goodsReceived)) {
                $this->billDeletion->delete($bill, $userId, $reason);
            }

            // Received goods leave stock
            foreach ($this->items($goodsReceived) as [$column, $model]) {
                $model->status = 0;
                if (Schema::hasColumn($model->getTable(), 'balance')) {
                    $model->balance = 0;
                }
                $model->save();
            }

            $goodsReceived->authorization = 'rejected';
            $goodsReceived->reversed_by_id = $userId;
            $goodsReceived->reversed_at = now();
            $goodsReceived->reversal_comments = $comments;
            $goodsReceived->save();

            return $goodsReceived;
        });
    }

    /** [[column, model], ...] for every item the GRV received. */
    protected function items(GoodsReceived $goodsReceived): array
    {
        $items = [];
        foreach ($goodsReceived->inventories()->get() as $inventory) {
            $items[] = ['inventory_id', $inventory];
        }
        foreach ($goodsReceived->tyres()->get() as $tyre) {
            $items[] = ['tyre_id', $tyre];
        }
        foreach ($goodsReceived->assets()->get() as $asset) {
            $items[] = ['asset_id', $asset];
        }

        return $items;
    }

    protected function bill(GoodsReceived $goodsReceived): ?Bill
    {
        return Bill::where('goods_received_id', $goodsReceived->id)->first();
    }

    protected function label($model): string
    {
        $number = $model->inventory_number ?? $model->tyre_number ?? $model->asset_number ?? ('#' . $model->id);
        $name = optional($model->product)->name;

        return trim($number . ($name ? " ({$name})" : ''));
    }
}
