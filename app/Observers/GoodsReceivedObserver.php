<?php

namespace App\Observers;

use App\Models\GoodsReceived;
use App\Services\Sage\SageSyncService;
use Illuminate\Support\Facades\Log;

/**
 * Pushes an approved GoodsReceived to Sage as a "Receipt" (Shipping Receipt),
 * created by converting the linked PO. Fires when the goods received becomes
 * approved. Guarded + idempotent.
 */
class GoodsReceivedObserver
{
    public function created(GoodsReceived $goodsReceived): void
    {
        if ($goodsReceived->authorization === 'approved') {
            $this->sync($goodsReceived);
        }
    }

    public function updated(GoodsReceived $goodsReceived): void
    {
        if ($goodsReceived->isDirty('authorization') && $goodsReceived->authorization === 'approved') {
            $this->sync($goodsReceived);
        }
    }

    protected function sync(GoodsReceived $goodsReceived): void
    {
        try {
            app(SageSyncService::class)->syncGoodsReceived($goodsReceived);
        } catch (\Throwable $e) {
            Log::warning('Sage goods-receipt sync failed: ' . $e->getMessage());
        }
    }
}
