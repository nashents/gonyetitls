<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Incident extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function trip(){
        return $this->belongsTo('App\Models\Trip');
    }
    public function employee(){
        return $this->belongsTo('App\Models\Employee');
    }
    public function immediate_causes(){
        return $this->hasMany('App\Models\ImmediateCause');
    }
    public function basic_causes(){
        return $this->hasMany('App\Models\BasicCause');
    }
    public function driver(){
        return $this->belongsTo('App\Models\Driver');
    }
    public function transporter(){
        return $this->belongsTo('App\Models\Transporter');
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
    public function cargo(){
        return $this->belongsTo('App\Models\Cargo');
    }
    public function measurement(){
        return $this->belongsTo('App\Models\Measurement');
    }
    public function incident_damages(){
        return $this->hasMany('App\Models\IncidentDamage');
    }
    public function contacts(){
        return $this->hasMany('App\Models\Contact');
    }
    public function documents(){
        return $this->hasMany('App\Models\Document');
    }
    public function incident_dates(){
        return $this->hasMany('App\Models\IncidentDate');
    }
    public function incident_injuries(){
        return $this->hasMany('App\Models\IncidentInjury');
    }
    public function incident_others(){
        return $this->hasMany('App\Models\IncidentOther');
    }
   
    public function destination(){
        return $this->belongsTo('App\Models\Destination');
    }
    public function customer(){
        return $this->belongsTo('App\Models\Customer');
    }
   
}
