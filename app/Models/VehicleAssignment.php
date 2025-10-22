<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehicleAssignment extends Model implements Auditable
{
    use HasFactory, SoftDeletes;

    use \OwenIt\Auditing\Auditable;

    public function employee(){
        return $this->belongsTo('App\Models\Employee');
    }
    public function mileage(){
        return $this->hasOne('App\Models\Mileage');
    }
    public function vehicle(){
        return $this->belongsTo('App\Models\Vehicle');
    }
  

    protected $fillable =[
        'user_id',
        'transporter_id',
        'vehicle_id',
        'employee_id',
        'starting_odometer',
        'ending_odometer',
        'start_date' ,
        'end_date',
        'comments',
        'status' ,
    ];
}
