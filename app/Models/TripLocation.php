<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TripLocation extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function trip(){
        return $this->belongsTo('App\Models\Trip');
    }
    
    public function driver(){
        return $this->belongsTo('App\Models\Driver');
    }
    public function country(){
        return $this->belongsTo('App\Models\Country');
    }

    public function location_pin(){
        return $this->hasOne('App\Models\LocationPin');
    }
}
