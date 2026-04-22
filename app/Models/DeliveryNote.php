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
}
