<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehicleMake extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function vehicle_models(){
        return $this->hasMany('App\Models\VehicleModel');
    }
    public function vehicles(){
        return $this->hasMany('App\Models\Vehicle');
    }
    public function products(){
        return $this->hasMany('App\Models\Product');
    }
    public function invetories(){
        return $this->hasMany('App\Models\Inventory');
    }

    protected $fillable = [
        'status',
        'name',
        'user_id',
    ];
}
