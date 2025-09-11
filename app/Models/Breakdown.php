<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Breakdown extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function trip(){
        return $this->belongsTo('App\Models\Trip');
    }
    public function breakdown_assignment(){
        return $this->hasOne('App\Models\BreakdownAssignment');
    }
    public function transporter(){
        return $this->belongsTo('App\Models\Transporter');
    }
    public function driver(){
        return $this->belongsTo('App\Models\Driver');
    }
    public function booking(){
        return $this->hasOne('App\Models\Booking');
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
    public function trailers(){
        return $this->belongsToMany('App\Models\Trailer');
    }
}
