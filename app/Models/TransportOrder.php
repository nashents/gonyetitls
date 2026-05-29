<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransportOrder extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

   public function trips()
    {
        return $this->belongsToMany(
            Trip::class,
            'trip_transport_orders',
            'transport_order_id',
            'trip_id'
        );
    }
    public function customer(){
        return $this->belongsTo('App\Models\Customer');
    }
    public function trip_origins(){
        return $this->hasMany('App\Models\TripOrigin');
    }
     public function trip_destinations(){
        return $this->hasMany('App\Models\TripDestination');
    }
    public function quotation(){
        return $this->belongsTo('App\Models\Quotation');
    }
    public function cargo(){
        return $this->belongsTo('App\Models\Cargo');
    }
    public function trip_type(){
        return $this->belongsTo('App\Models\TripType');
    }
    public function units_of_measure(){
        return $this->belongsTo('App\Models\UnitsOfMeasure');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
    public function consignee(){
        return $this->belongsTo('App\Models\Consignee');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function company(){
        return $this->belongsTo('App\Models\Company');
    }
    public function loading_point(){
        return $this->belongsTo('App\Models\LoadingPoint');
    }
    public function offloading_point(){
        return $this->belongsTo('App\Models\OffloadingPoint');
    }
    public function fromDestination()
    {
        return $this->belongsTo(\App\Models\Destination::class, 'from');
    }
    public function toDestination()
    {
        return $this->belongsTo(\App\Models\Destination::class, 'to');
    }


}
