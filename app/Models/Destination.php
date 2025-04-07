<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Destination extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function trips(){
        return $this->hasMany('App\Models\Trip');
    }
    public function incidents(){
        return $this->hasMany('App\Models\Incident');
    }
    public function trip_destinations(){
        return $this->hasMany('App\Models\TripDestination');
    }
    public function trip_returns(){
        return $this->hasMany('App\Models\TripReturn');
    }
    public function country(){
        return $this->belongsTo('App\Models\Country');
    }
    public function recoveries(){
        return $this->hasMany('App\Models\Recovery');
    }
    protected $fillable = [
        'user_id',
        'country_id',
        'city',
        'description',
        'long',
        'lat',
    ];
}
