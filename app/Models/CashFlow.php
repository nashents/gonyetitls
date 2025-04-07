<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CashFlow extends Model implements Auditable
{
    use HasFactory, SoftDeletes;

    use \OwenIt\Auditing\Auditable;

    public function vehicle(){
        return $this->belongsTo('App\Models\Vehicle');
    }
    public function payment(){
        return $this->belongsTo('App\Models\Payment');
    }
    public function vendor(){
        return $this->belongsTo('App\Models\Vendor');
    }

    public function recovery(){
        return $this->belongsTo('App\Models\Recovery');
    }
    public function account(){
        return $this->belongsTo('App\Models\Account');
    }
    public function horse(){
        return $this->belongsTo('App\Models\Horse');
    }
    public function trailer(){
        return $this->belongsTo('App\Models\Trailer');
    }
    public function driver(){
        return $this->belongsTo('App\Models\Driver');
    }
    public function employee(){
        return $this->belongsTo('App\Models\Employee');
    }
    public function fuel(){
        return $this->belongsTo('App\Models\Fuel');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
    public function trip(){
        return $this->belongsTo('App\Models\Trip');
    }
}
