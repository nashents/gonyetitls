<?php

namespace App\Models;

use App\Models\Trip;
use App\Models\TripTransportOrder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class DeliveryNote extends Model implements Auditable
{
    use HasFactory, SoftDeletes;

    use \OwenIt\Auditing\Auditable;

   
    public function units_of_measure(){
        return $this->belongsTo('App\Models\UnitsOfMeasure');
    }

    public function trip_transport_order()
    {
        return $this->belongsTo(TripTransportOrder::class, 'trip_transport_order_id');
    }
    
    public function transport_order()
    {
        return $this->belongsTo(TransportOrder::class, 'transport_order_id');
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class, 'trip_id');
    }

    protected $fillable =[
        'trip_id'
    ];

    /**
     * Liquid loss rows per reading method: meter (loaded vs offloaded meter), dips (loaded vs
     * offloaded dips) and shoretank (loaded meter vs shoretank). Allowable = the trip's
     * allowable loss % of the loaded figure; chargeable = loss above that allowance.
     */
    public function liquidLossRows(?float $allowablePct): array
    {
        $pairs = [
            ['Meter Readings @ Ambient',   'loaded_litreage',           'offloaded_litreage'],
            ['Meter Readings @ 20°C',      'loaded_litreage_at_20',     'offloaded_litreage_at_20'],
            ['Dips @ Ambient',             'loaded_dip_litreage',       'offloaded_dip_litreage'],
            ['Dips @ 20°C',                'loaded_dip_litreage_at_20', 'offloaded_dip_litreage_at_20'],
            ['Shoretank vs Meter @ Ambient', 'loaded_litreage',         'shoretank_litreage'],
            ['Shoretank vs Meter @ 20°C',  'loaded_litreage_at_20',     'shoretank_litreage_at_20'],
        ];

        $rows = [];
        foreach ($pairs as [$label, $loadedField, $offloadedField]) {
            $loaded    = is_numeric($this->$loadedField) ? (float) $this->$loadedField : null;
            $offloaded = is_numeric($this->$offloadedField) ? (float) $this->$offloadedField : null;

            $loss       = (! is_null($loaded) && ! is_null($offloaded)) ? round($loaded - $offloaded, 3) : null;
            $allowable  = ($allowablePct && $loaded > 0) ? round($loaded * $allowablePct / 100, 3) : null;
            $chargeable = (! is_null($loss) && ! is_null($allowable)) ? max(0, round($loss - $allowable, 3)) : null;

            $rows[] = compact('label', 'loaded', 'offloaded', 'loss', 'allowable', 'chargeable');
        }

        return $rows;
    }

    /**
     * The delivery note for one trip/transport-order pair. Also matches the orphan note that
     * Trips\Create used to write without a trip_id (keyed only by trip_transport_order_id) and
     * adopts it onto the trip - otherwise callers created a second note beside it, and
     * TripTransportOrder::delivery_note() kept reading the blank orphan (stale offloaded date).
     */
    public static function resolveForTripTransportOrder($trip, $tto): ?self
    {
        $dn = static::where(function ($q) use ($trip, $tto) {
                $q->where('trip_id', $trip->id)
                  ->where(function ($q2) use ($tto) {
                      $q2->where('trip_transport_order_id', $tto->id)
                         ->orWhere(function ($q3) use ($tto) {
                             $q3->whereNull('trip_transport_order_id')
                                ->where('transport_order_id', $tto->transport_order_id);
                         });
                  });
            })
            ->orWhere(function ($q) use ($tto) {
                $q->whereNull('trip_id')->where('trip_transport_order_id', $tto->id);
            })
            ->orderByRaw('trip_id IS NULL')
            ->latest()
            ->first();

        if ($dn && is_null($dn->trip_id)) {
            $dn->trip_id = $trip->id;
            $dn->save();
        }

        return $dn;
    }
}
