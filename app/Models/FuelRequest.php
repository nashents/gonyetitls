<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FuelRequest extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'user_id',
        'employee_id',
        'request_type',
        'fuel_type',
        'date',
        'quantity',
    ];

    public function employee(){
        return $this->belongsTo('App\Models\Employee');
    }
    public function horse(){
        return $this->belongsTo('App\Models\Horse');
    }
    public function asset(){
        return $this->belongsTo('App\Models\Asset');
    }
    public function vehicle(){
        return $this->belongsTo('App\Models\Vehicle');
    }
  
    public function allocation(){
        return $this->belongsTo('App\Models\Allocation');
    }
}
