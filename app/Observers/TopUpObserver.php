<?php

namespace App\Observers;

use App\Models\TopUp;
use App\Services\Fuel\TopUpStockService;

/**
 * Keeps the tank balance in step with the top-up on every change - approval,
 * rejection, edit, delete, restore - see TopUpStockService. The screens no
 * longer add to container.balance themselves.
 */
class TopUpObserver
{
    public function saved(TopUp $topUp): void
    {
        app(TopUpStockService::class)->sync($topUp);
    }

    public function deleted(TopUp $topUp): void
    {
        app(TopUpStockService::class)->sync($topUp);
    }

    public function restored(TopUp $topUp): void
    {
        app(TopUpStockService::class)->sync($topUp);
    }
}
