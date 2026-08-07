<?php

namespace App\Observers;

use App\Models\Fuel;
use App\Services\Sage\SageSyncService;
use Illuminate\Support\Facades\Log;

/**
 * Pushes an approved Fuel order to Sage as a "PO - Diesel" document. Fires when
 * a fuel order becomes approved (created already-approved, or transitions to it).
 * Idempotent + guarded: a missing/inactive Sage integration no-ops, and a Sage
 * error never blocks the approval.
 */
class FuelObserver
{
    public function created(Fuel $fuel): void
    {
        if ($fuel->authorization === 'approved') {
            $this->sync($fuel);
        }
    }

    public function updated(Fuel $fuel): void
    {
        if ($fuel->isDirty('authorization') && $fuel->authorization === 'approved') {
            $this->sync($fuel);
        }
    }

    protected function sync(Fuel $fuel): void
    {
        try {
            app(SageSyncService::class)->syncFuel($fuel);
        } catch (\Throwable $e) {
            Log::warning('Sage fuel PO - Diesel sync failed: ' . $e->getMessage());
        }
    }
}
