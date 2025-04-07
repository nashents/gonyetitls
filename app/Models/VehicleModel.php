<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehicleModel extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function vehicle_make(){
        return $this->belongsTo('App\Models\VehicleMake');
    }
    public function invetories(){
        return $this->hasMany('App\Models\Inventory');
    }
    public function vehicles(){
        return $this->hasMany('App\Models\Vehicle');
    }
    protected $fillable = [
        'vehicle_make_id',
        'name',
        'user_id',
    ];
}
