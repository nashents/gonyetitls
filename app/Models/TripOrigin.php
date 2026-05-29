<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class TripOrigin extends Model implements Auditable
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

    public function units_of_measure(){
        return $this->belongsTo('App\Models\UnitsOfMeasure');
    }

    public function loading_point(){
        return $this->belongsTo('App\Models\LoadingPoint');
    }

    public function user(){
        return $this->belongsTo('App\Models\User');
    }

    protected $fillable = [
        'user_id',
        'transport_order_id',
        'destination_id',
        'loading_point_id',
        'units_of_measure_id',
        'weight',
        'quantity',
        'litreage',
        'litreage_at_20',
        'rate',
        'freight',
    ];
}
