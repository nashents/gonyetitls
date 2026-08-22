<?php

namespace App\Services;

use App\Models\Deal;
use App\Models\Trip;
use App\Models\TransportOrder;
use App\Models\TripTransportOrder;
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

                $orderTtos = TripTransportOrder::where('transport_order_id', $transportOrder->id)->get();
                $transportOrder->completed = $orderTtos->isNotEmpty() && $orderTtos->every(fn ($t) => (bool) $t->completed);
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

                $orders = TransportOrder::where('deal_id', $deal->id)->get();
                $deal->completed = $orders->isNotEmpty() && $orders->every(fn ($o) => (bool) $o->completed);
                $deal->save();
            }
        });
    }
}
