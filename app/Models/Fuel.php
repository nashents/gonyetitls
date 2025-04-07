<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Fuel extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function driver(){
        return $this->belongsTo('App\Models\Driver');
    }
    public function mileage(){
        return $this->hasOne('App\Models\Mileage');
    }
    public function employee(){
        return $this->belongsTo('App\Models\Employee');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
    public function vehicle(){
        return $this->belongsTo('App\Models\Vehicle');
    }
    public function horse(){
        return $this->belongsTo('App\Models\Horse');
    }
    public function vendor(){
        return $this->belongsTo('App\Models\Vendor');
    }
    public function asset(){
        return $this->belongsTo('App\Models\Asset');
    }
    public function container(){
        return $this->belongsTo('App\Models\Container');
    }
    public function trip(){
        return $this->belongsTo('App\Models\Trip');
    }
    public function cash_flow(){
        return $this->hasOne('App\Models\CashFlow');
    }
    public function trip_expense(){
        return $this->hasOne('App\Models\TripExpense');
    }
    public function bill(){
        return $this->hasOne('App\Models\Bill');
    }
}
