<?php

namespace App\Observers;

use App\Models\Purchase;
use App\Services\Sage\SageSyncService;
use Illuminate\Support\Facades\Log;

/**
 * Pushes an approved Purchase to Sage as a "Purchase order". Fires when a
 * purchase becomes approved. Guarded + idempotent: a missing/inactive Sage
 * integration no-ops, and a Sage error never blocks the approval.
 */
class PurchaseObserver
{
    public function created(Purchase $purchase): void
    {
        if ($purchase->authorization === 'approved') {
            $this->sync($purchase);
        }
    }

    public function updated(Purchase $purchase): void
    {
        if ($purchase->isDirty('authorization') && $purchase->authorization === 'approved') {
            $this->sync($purchase);
        }
    }

    protected function sync(Purchase $purchase): void
    {
        try {
            app(SageSyncService::class)->syncPurchase($purchase);
        } catch (\Throwable $e) {
            Log::warning('Sage purchase-order sync failed: ' . $e->getMessage());
        }
    }
}
