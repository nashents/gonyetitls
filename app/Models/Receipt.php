<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Receipt extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;


    public function sale(){
        return $this->belongsTo('App\Models\Sale');
    }
    public function invoice(){
        return $this->belongsTo('App\Models\Invoice');
    }
   
    public function recovery(){
        return $this->belongsTo('App\Models\Recovery');
    }
    public function driver(){
        return $this->belongsTo('App\Models\Driver');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
    public function payment(){
        return $this->belongsTo('App\Models\Payment');
    }
    public function trip(){
        return $this->belongsTo('App\Models\Trip');
    }
    public function company(){
        return $this->belongsTo('App\Models\Company');
    }
    public function customer(){
        return $this->belongsTo('App\Models\Customer');
    }
}
