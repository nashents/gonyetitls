<?php

namespace App\Services\Fuel;

use App\Models\Container;
use App\Models\TopUp;
use Illuminate\Support\Facades\DB;

/**
 * Keeps a Bulk Buy tank's balances in step with its top-ups. A top-up adds
 * its litres (quantity -> container.balance) and prepaid money
 * (account_amount -> container.account_balance) while it is approved and
 * not deleted; otherwise it adds nothing.
 *
 * What it has added so far is recorded on the top-up itself
 * (stock_container_id / stock_quantity / stock_amount), so sync() only ever
 * moves the tank by the difference: approving twice adds once, an edit
 * adds/removes the change, rejecting or deleting takes it all back out, and
 * moving it to another tank shifts it across. Runs from TopUpObserver on
 * every save/delete/restore, so no screen has to remember to do it.
 */
class TopUpStockService
{
    public function sync(TopUp $topUp): void
    {
        $active = $topUp->authorization === 'approved' && ! $topUp->trashed() && $topUp->container_id;

        $target = [
            'container_id' => $active ? (int) $topUp->container_id : null,
            'quantity' => $active ? $this->number($topUp->quantity) : 0.0,
            'amount' => $active ? $this->number($topUp->account_amount) : 0.0,
        ];

        $applied = [
            'container_id' => $topUp->stock_container_id ? (int) $topUp->stock_container_id : null,
            'quantity' => $this->number($topUp->stock_quantity),
            'amount' => $this->number($topUp->stock_amount),
        ];

        if ($target === $applied) {
            return;
        }

        DB::transaction(function () use ($topUp, $target, $applied) {
            if ($applied['container_id']) {
                $this->move($applied['container_id'], -$applied['quantity'], -$applied['amount']);
            }
            if ($target['container_id']) {
                $this->move($target['container_id'], $target['quantity'], $target['amount']);
            }

            // Query update, not save() - this runs inside TopUpObserver::saved().
            TopUp::withTrashed()->whereKey($topUp->id)->update([
                'stock_container_id' => $target['container_id'],
                'stock_quantity' => $target['quantity'],
                'stock_amount' => $target['amount'],
            ]);

            $topUp->stock_container_id = $target['container_id'];
            $topUp->stock_quantity = $target['quantity'];
            $topUp->stock_amount = $target['amount'];
            $topUp->syncOriginalAttributes(['stock_container_id', 'stock_quantity', 'stock_amount']);
        });
    }

    private function move(int $containerId, float $quantity, float $amount): void
    {
        if ($quantity == 0 && $amount == 0) {
            return;
        }

        // withTrashed - a deleted station's figures still have to net out.
        $container = Container::withTrashed()->lockForUpdate()->find($containerId);
        if (! $container) {
            return;
        }

        if ($quantity != 0) {
            $container->balance = round($this->number($container->balance) + $quantity, 2);
        }
        if ($amount != 0) {
            $container->account_balance = round($this->number($container->account_balance) + $amount, 2);
        }

        $container->save();
    }

    private function number($value): float
    {
        return is_numeric($value) ? round((float) $value, 2) : 0.0;
    }
}
