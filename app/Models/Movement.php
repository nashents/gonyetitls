<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Movement extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function employee(){
        return $this->belongsTo('App\Models\Employee');
    }
    public function branch(){
        return $this->belongsTo('App\Models\Branch');
    }
    public function department(){
        return $this->belongsTo('App\Models\Department');
    }
    public function horse(){
        return $this->belongsTo('App\Models\Horse');
    }
    public function vehicle(){
        return $this->belongsTo('App\Models\Vehicle');
    }
    public function trailer(){
        return $this->belongsTo('App\Models\Trailer');
    }
    public function asset(){
        return $this->belongsTo('App\Models\Asset');
    }
    public function inventory(){
        return $this->belongsTo('App\Models\Inventory');
    }
    public function tyre(){
        return $this->belongsTo('App\Models\Tyre');
    }

    protected $fillable = [
        'user_id',
        'tyre_assignment_id',
        'location',
        'moved_mileage',
        'date',
        'current_mileage',
        'tyre_id',
        'driver_id',
        'mileage_id',
        'assignment_id',
        'asset_assignment_id',
        'inventory_assignment_id',
        'vehicle_assignment_id',
        'trailer_assignment_id',
        'breakdown_assignment_id',
        'asset_id',
        'inventory_id',
        'horse_id',
        'trailer_id',
        'vehicle_id',
        'employee_id',
        'department_id',
        'branch_id',
    ];
}
