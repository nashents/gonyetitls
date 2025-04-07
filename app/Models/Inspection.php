<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inspection extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function booking(){
        return $this->belongsTo('App\Models\Booking');
    }
    public function service_type(){
        return $this->belongsTo('App\Models\ServiceType');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function employees(){
        return $this->belongsToMany('App\Models\Employee');
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
    public function employee(){
        return $this->belongsTo('App\Models\Employee');
    }
    public function inspection_type(){
        return $this->belongsTo('App\Models\InspectionType');
    }
    public function ticket(){
        return $this->hasOne('App\Models\Ticket');
    }

    public function inspection_results(){
        return $this->hasMany('App\Models\InspectionResult');
    }
}
