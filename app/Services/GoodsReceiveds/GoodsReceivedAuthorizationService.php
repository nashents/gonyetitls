<?php

namespace App\Services\GoodsReceiveds;

use App\Models\GoodsReceived;
use App\Services\Accounting\InventoryJournalService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GoodsReceivedAuthorizationService
{
    public function __construct(private InventoryJournalService $inventoryJournal)
    {
    }

    /**
     * Authorize (approve/reject) a pending GRV. Approving flips the suspended
     * inventory/tyre/asset rows it created to status=1 — the moment they actually
     * become available stock — and posts the receipt to the GL (debit Spares
     * Inventory, credit Accounts Payable; see InventoryJournalService).
     * Rejecting only touches the GRV header; the items stay suspended
     * (status=0) rather than being deleted, and nothing is posted.
     */
    public function authorize(GoodsReceived $goodsReceived, string $decision, ?string $comments, int $userId): GoodsReceived
    {
        return DB::transaction(function () use ($goodsReceived, $decision, $comments, $userId) {

            $goodsReceived = GoodsReceived::lockForUpdate()->find($goodsReceived->id);

            if (! $goodsReceived) {
                throw ValidationException::withMessages([
                    'goods_received' => 'This GRV could not be found.',
                ]);
            }

            if ($goodsReceived->authorization !== 'pending') {
                throw ValidationException::withMessages([
                    'goods_received' => 'Only pending GRVs can be authorized.',
                ]);
            }

            $goodsReceived->authorized_by_id = $userId;
            $goodsReceived->authorization = $decision;
            $goodsReceived->authorization_date = now();
            $goodsReceived->authorization_comments = $comments;
            $goodsReceived->save();

            if ($decision === 'approved') {
                foreach ($goodsReceived->inventories as $inventory) {
                    $inventory->status = 1;
                    $inventory->save();
                }
                foreach ($goodsReceived->tyres as $tyre) {
                    $tyre->status = 1;
                    $tyre->save();
                }
                foreach ($goodsReceived->assets as $asset) {
                    $asset->status = 1;
                    $asset->save();
                }

                $this->inventoryJournal->postReceipt($goodsReceived->fresh());
            }

            return $goodsReceived;
        });
    }
}
