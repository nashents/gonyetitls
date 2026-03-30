<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnitsOfMeasure extends Model
{
    use HasFactory, SoftDeletes;

    public function trips(){
        return $this->hasMany('App\Models\Trip');
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
