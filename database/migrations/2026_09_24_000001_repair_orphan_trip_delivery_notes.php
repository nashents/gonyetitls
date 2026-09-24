<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Trips\Create::createDeliveryNotes() wrote each TTO's delivery note without a trip_id. The
 * status modal (keyed on trip_id) never found it and created a second note, while
 * TripTransportOrder::delivery_note() kept returning the blank orphan - so the trips list's
 * Est/Offloaded column never saw the saved offloaded date and fell back to the trip end date.
 *
 * Blank orphans that have a trip-linked sibling are soft-deleted; the rest are linked to
 * their TTO's trip.
 */
return new class extends Migration
{
    public function up()
    {
        $orphans = DB::table('delivery_notes')
            ->whereNull('deleted_at')
            ->whereNull('trip_id')
            ->whereNotNull('trip_transport_order_id')
            ->get();

        foreach ($orphans as $note) {
            $hasSibling = DB::table('delivery_notes')
                ->whereNull('deleted_at')
                ->whereNotNull('trip_id')
                ->where('trip_transport_order_id', $note->trip_transport_order_id)
                ->exists();

            if ($hasSibling) {
                if (! $note->status && ! $note->offloaded_date) {
                    DB::table('delivery_notes')->where('id', $note->id)->update(['deleted_at' => now()]);
                }
                continue;
            }

            $tripId = DB::table('trip_transport_orders')->where('id', $note->trip_transport_order_id)->value('trip_id');
            if ($tripId) {
                DB::table('delivery_notes')->where('id', $note->id)->update(['trip_id' => $tripId]);
            }
        }
    }

    public function down()
    {
        //
    }
};
