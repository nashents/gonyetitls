<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Mileage extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function trip(){
        return $this->belongsTo('App\Models\Trip');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function trailer(){
        return $this->belongsTo('App\Models\Trailer');
    }
    public function vehicle(){
        return $this->belongsTo('App\Models\Vehicle');
    }
    public function horse(){
        return $this->belongsTo('App\Models\Horse');
    }
    public function booking(){
        return $this->belongsTo('App\Models\Booking');
    }
    public function fuel(){
        return $this->belongsTo('App\Models\Fuel');
    }
    public function assignment(){
        return $this->belongsTo('App\Models\Assignment');
    }
    public function trailer_assignment(){
        return $this->belongsTo('App\Models\TrailerAssignment');
    }
    public function vehicle_assignment(){
        return $this->belongsTo('App\Models\VehicleAssignment');
    }
    public function tyre_assignment(){
        return $this->belongsTo('App\Models\TyreAssignment');
    }
}
