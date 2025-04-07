<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TripReturn extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

      public function trip_type(){
        return $this->belongsTo('App\Models\TripType');
    }
    public function vehicle(){
        return $this->belongsTo('App\Models\Vehicle');
    }
    public function horse(){
        return $this->belongsTo('App\Models\Horse');
    }
    public function trailer(){
        return $this->belongsTo('App\Models\Trailer');
    }
    public function driver(){
        return $this->belongsTo('App\Models\Driver');
    }
    public function cargo(){
        return $this->belongsTo('App\Models\Cargo');
    }
    public function broker(){
        return $this->belongsTo('App\Models\Broker');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
    public function customer(){
        return $this->belongsTo('App\Models\Customer');
    }
    public function destination(){
        return $this->belongsTo('App\Models\Destination');
    }
    public function trip_return(){
        return $this->hasOne('App\Models\TripReturn');
    }
    public function cash_flow(){
        return $this->hasOne('App\Models\CashFlow');
    }

    public function trip(){
        return $this->belongsTo('App\Models\Trip');
    }
}
