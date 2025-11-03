<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Measurement extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function trips(){
        return $this->hasMany('App\Models\Trip');
    }
    public function capacities(){
        return $this->hasMany('App\Models\Capacity');
    }
    public function trip_destinations(){
        return $this->hasMany('App\Models\TripDestination');
    }
    public function incidents(){
        return $this->hasMany('App\Models\Incident');
    }
    public function ticket_requests(){
        return $this->hasMany('App\Models\TicketRequest');
    }
}
