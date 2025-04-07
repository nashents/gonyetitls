<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TruckStop extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function trips(){
        return $this->belongsToMany('App\Models\Trip');
    }
    public function route(){
        return $this->belongsTo('App\Models\Route');
    }

    protected $fillable=[
        'route_id',
        'name',
        'status',
    ];
}
