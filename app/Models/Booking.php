<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function inspection(){
        return $this->hasOne('App\Models\Inspection');
    }
    public function mileage(){
        return $this->hasOne('App\Models\Mileage');
    }
    public function requisitions(){
        return $this->hasMany('App\Models\Requisition');
    }
    public function employees(){
        return $this->belongsToMany('App\Models\Employee');
    }
    public function employee(){
        return $this->belongsTo('App\Models\Employee');
    }
    public function asset(){
        return $this->belongsTo('App\Models\Asset');
    }
    public function vendor(){
        return $this->belongsTo('App\Models\Vendor');
    }
    public function ticket(){
        return $this->hasOne('App\Models\Ticket');
    }
    public function horse(){
        return $this->belongsTo('App\Models\Horse');
    }
    public function trailer(){
        return $this->belongsTo('App\Models\Trailer');
    }
    public function vehicle(){
        return $this->belongsTo('App\Models\Vehicle');
    }
    public function driver(){
        return $this->belongsTo('App\Models\Driver');
    }
    public function purchases(){
        return $this->hasMany('App\Models\Purchase');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
   
    public function job_type(){
        return $this->belongsTo('App\Models\JobType');
    }
    public function service_type(){
        return $this->belongsTo('App\Models\ServiceType');
    }
}
