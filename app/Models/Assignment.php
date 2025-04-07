<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Assignment extends Model implements Auditable
{
    use HasFactory, SoftDeletes;

    use \OwenIt\Auditing\Auditable;

    public function driver(){
        return $this->belongsTo('App\Models\Driver');
    }
    public function transporter(){
        return $this->belongsTo('App\Models\Transporter');
    }
    public function vehicle(){
        return $this->belongsTo('App\Models\Vehicle');
    }
    public function mileage(){
        return $this->hasOne('App\Models\Mileage');
    }
    public function horse(){
        return $this->belongsTo('App\Models\Horse');
    }
    public function trailer(){
        return $this->belongsTo('App\Models\Trailer');
    }

    protected $fillable =[
        'user_id',
        'vehicle_id',
        'horse_id',
        'driver_id',
        'transporter_id',
        'starting_odometer',
        'ending_odometer',
        'start_date' ,
        'end_date',
        'comments',
        'status' ,
    ];
}
