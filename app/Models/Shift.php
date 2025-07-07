<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Shift extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function cargo(){
        return $this->belongsTo('App\Models\Cargo');
    }
    public function customer(){
        return $this->belongsTo('App\Models\Customer');
    }
    public function transporter(){
        return $this->belongsTo('App\Models\Transporter');
    }
    public function vehicle(){
        return $this->belongsTo('App\Models\Vehicle');
    }
    public function horse(){
        return $this->belongsTo('App\Models\Horse');
    }
    public function driver(){
        return $this->belongsTo('App\Models\Driver');
    }
    public function fuel(){
        return $this->hasOne('App\Models\Fuel');
    }
    public function rehandlings(){
        return $this->hasMany('App\Models\Rehandling');
    }
    public function trips(){
        return $this->hasMany('App\Models\Trip');
    }

    protected $fillable = [
        'user_id',
        'type',
        'date',
        'shift_start_time',
        'shift_end_time',
        'horse_id',
        'driver_id',
        'customer_id',
        'cargo_id',
        'actual_mileage',
        'calculated_mileage',
        'open_mileage',
        'close_mileage',
        'fuel_consumption_mileage',
    ];
}
