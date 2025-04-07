<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Allocation extends Model implements Auditable
{
    use HasFactory, SoftDeletes;

    use \OwenIt\Auditing\Auditable;

    public function employee(){
        return $this->belongsTo('App\Models\Employee');
    }
    public function fuel_requests(){
        return $this->hasMany('App\Models\FuelRequest');
    }
    public function vehicle(){
        return $this->belongsTo('App\Models\Vehicle');
    }
    public function horse(){
        return $this->belongsTo('App\Models\Horse');
    }
    public function container(){
        return $this->belongsTo('App\Models\Container');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }

}
