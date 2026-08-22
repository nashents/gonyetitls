<?php

namespace App\Services;

use App\Models\Deal;
use App\Models\Trip;
use App\Models\TransportOrder;
use App\Models\TripTransportOrder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class TripCompletionCascadeService
{
    /**
     * Recompute completed flags on this trip's TripTransportOrders, their
     * TransportOrders, and those orders' Deals, from current DB state.
     * Safe to call after either completing or un-completing a trip.
     */
    public function syncForTrip(Trip $trip): void
    {
        DB::transaction(function () use ($trip) {
            $ttos = TripTransportOrder::where('trip_id', $trip->id)->get();

            $transportOrderIds = [];
            foreach ($ttos as $tto) {
                $tto->completed = (bool) $trip->status;
                $tto->save();

                if ($tto->transport_order_id) {
                    $transportOrderIds[] = $tto->transport_order_id;
                }
            }

            $dealIds = [];
            foreach (array_unique($transportOrderIds) as $transportOrderId) {
                $transportOrder = TransportOrder::find($transportOrderId);
                if (!$transportOrder) {
                    continue;
                }

                $transportOrder->completed = $this->isTransportOrderFulfilled($transportOrder);
                $transportOrder->save();

                if ($transportOrder->deal_id) {
                    $dealIds[] = $transportOrder->deal_id;
                }
            }

            foreach (array_unique($dealIds) as $dealId) {
                $deal = Deal::find($dealId);
                if (!$deal) {
                    continue;
                }

                $deal->completed = $this->isDealFulfilled($deal);
                $deal->save();
            }
        });
    }

    /**
     * A TransportOrder closes only once every trip fulfilling it (via its TTOs)
     * is both marked Completed and operationally Offloaded, and the allocated
     * weight/quantity carried across those trips covers the order's targets.
     */
    private function isTransportOrderFulfilled(TransportOrder $transportOrder): bool
    {
        $ttos = TripTransportOrder::with('trip')->where('transport_order_id', $transportOrder->id)->get();

        if ($ttos->isEmpty() || !$this->allTripsCompletedAndOffloaded($ttos)) {
            return false;
        }

        return $this->allocatedTotalsMeetTarget($ttos, $transportOrder->weight, $transportOrder->quantity);
    }

    /**
     * A Deal closes only once all of its TransportOrders are closed, and the
     * allocated weight/quantity carried across every trip under the deal
     * covers the deal's own targets. TTOs are resolved via the deal's
     * TransportOrders rather than trip_transport_orders.deal_id directly,
     * since that column isn't populated by every TTO creation path.
     */
    private function isDealFulfilled(Deal $deal): bool
    {
        $orders = TransportOrder::where('deal_id', $deal->id)->get();
        if ($orders->isEmpty() || !$orders->every(fn ($o) => (bool) $o->completed)) {
            return false;
        }

        $ttos = TripTransportOrder::with('trip')
            ->whereIn('transport_order_id', $orders->pluck('id'))
            ->get();

        if ($ttos->isEmpty() || !$this->allTripsCompletedAndOffloaded($ttos)) {
            return false;
        }

        return $this->allocatedTotalsMeetTarget($ttos, $deal->weight, $deal->quantity);
    }

    private function allTripsCompletedAndOffloaded(Collection $ttos): bool
    {
        return $ttos->every(function (TripTransportOrder $tto) {
            return $tto->trip
                && (int) $tto->trip->status === 1
                && $tto->trip->trip_status === 'Offloaded';
        });
    }

    private function allocatedTotalsMeetTarget(Collection $ttos, $targetWeight, $targetQuantity): bool
    {
        $weightSum = $ttos->sum(fn (TripTransportOrder $t) => (float) $t->allocated_weight);
        $quantitySum = $ttos->sum(fn (TripTransportOrder $t) => (float) $t->allocated_quantity);

        $weightOk = !is_numeric($targetWeight) || (float) $targetWeight <= 0 || $weightSum >= (float) $targetWeight;
        $quantityOk = !is_numeric($targetQuantity) || (float) $targetQuantity <= 0 || $quantitySum >= (float) $targetQuantity;

        return $weightOk && $quantityOk;
    }
}
