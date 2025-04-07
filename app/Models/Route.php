<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Route extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function trips(){
        return $this->hasMany('App\Models\Trip');
    }
    public function truck_stops(){
        return $this->hasMany('App\Models\TruckStop');
    }
    public function borders(){
        return $this->belongsToMany('App\Models\Border');
    }
    public function compliances(){
        return $this->hasMany('App\Models\Compliance');
    }

    protected $fillable=[
        'user_id',
        'name',
        'from',
        'to',
        'rank',
        'description',
        'tollgates',
        'status',
        'distance',
        'expiry_date',
    ];
}
