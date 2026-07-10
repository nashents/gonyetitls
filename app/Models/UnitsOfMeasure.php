<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class UnitsOfMeasure extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function trips(){
        return $this->hasMany('App\Models\Trip');
    }
    public function trip_origins(){
        return $this->hasMany('App\Models\TripOrigin');
    }
       public function deals(){
        return $this->hasMany('App\Models\Deal');
    }
    public function trip_transport_orders(){
        return $this->hasMany('App\Models\TripTransportOrder');
    }
    public function delivery_notes(){
        return $this->hasMany('App\Models\DeliveryNote');
    }
    public function trip_destinations(){
        return $this->hasMany('App\Models\TripDestinations');
    }
    public function transport_orders(){
        return $this->hasMany('App\Models\TransportOrder');
    }
    public function products(){
        return $this->hasMany('App\Models\Product');
    }
}
