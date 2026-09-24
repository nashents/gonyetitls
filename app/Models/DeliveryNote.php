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
