<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TripDestination extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function transport_order(){
        return $this->belongsTo('App\Models\TransportOrder');
    }
    public function trip_transport_order(){
        return $this->belongsTo('App\Models\TripTransportOrder');
    }
    public function trip(){
        return $this->belongsTo('App\Models\Trip');
    }
    
    public function destination(){
        return $this->belongsTo('App\Models\Destination');
    }

    public function measurement(){
        return $this->belongsTo('App\Models\Measurement');
    }

    public function units_of_measure(){
        return $this->belongsTo('App\Models\UnitsOfMeasure');
    }

    public function offloading_point(){
        return $this->belongsTo('App\Models\OffloadingPoint');
    }

     public function user(){
        return $this->belongsTo('App\Models\User');
    }

    protected $fillable = [
        'user_id',
        'trip_id',
        'destination_id',
        'offloading_date',
        'offloading_point_id',
        'weight',
        'quantity',
        'units_of_measure_id',
        'litreage',
        'litreage_at_20',
        'rate',
        'freight',
    ];
}
