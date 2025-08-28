<?php

namespace App\Models;

use App\Models\LoadingPoint;
use App\Models\OffloadingPoint;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Shift extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function team(){
        return $this->belongsTo('App\Models\Team');
    }
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
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
    public function company(){
        return $this->belongsTo('App\Models\Company');
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

    public function loading_points()
    {
        return $this->belongsToMany(LoadingPoint::class);
    }
    public function offloading_points()
    {
        return $this->belongsToMany(OffloadingPoint::class);
    }

    protected $fillable = [
        'type',
        'date',
        'driver_id',
        'company_id',
        'user_id',
        'shift_number',
        'shift_start_time',
        'shift_end_time',
        'horse_id',
        'vehicle_id',
        'customer_id',
        'currency_id',
        'transporter_id',
        'cargo_id',
        'actual_mileage',
        'calculated_mileage',
        'open_mileage',
        'close_mileage',
        'fuel_consumption_mileage',
        'fuel_consumption_hours',
        'equipment',
        'total_loads',
        'total_fuel',
        'authorization',
        'authorized_by_id',
        'authorization_date',
        'status',
        'for',
        'reason',
        'depart_workshop_time',
        'arrive_location_time',
        'depart_location_time',
        'arrive_workshop_time',
        'total_weight',
        'freight',
    ];
}
