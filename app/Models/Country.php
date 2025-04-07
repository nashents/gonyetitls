<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Country extends Model implements Auditable
{
    use HasFactory, SoftDeletes;

    use \OwenIt\Auditing\Auditable;

    public function destinations(){
        return $this->hasMany('App\Models\Destination');
    }
    public function trip_locations(){
        return $this->hasMany('App\Models\TripLocation');
    }
    public function provinces(){
        return $this->hasMany('App\Models\Province');
    }

    protected $fillable = [
        'user_id',
        'name',
    ];
}
