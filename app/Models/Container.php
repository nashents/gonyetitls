<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Container extends Model implements Auditable
{
    use HasFactory, SoftDeletes;

    use \OwenIt\Auditing\Auditable;

    public function vendor(){
    return $this->belongsTo('App\Models\Vendor');
    }
    public function consignee(){
    return $this->belongsTo('App\Models\Consignee');
    }
    public function user(){
    return $this->belongsTo('App\Models\User');
    }
    public function bills(){
        return $this->hasMany('App\Models\Bill');
    }
    public function currency(){
    return $this->belongsTo('App\Models\Currency');
    }
    public function allocations(){
        return $this->hasMany('App\Models\Allocation');
    }

    public function top_ups(){
        return $this->hasMany('App\Models\TopUp');
    }
    public function payments(){
        return $this->hasMany('App\Models\Payment');
    }
    protected $fillable = [
        'user_id',
        'vendor_id',
        'currency_id',
        'fuel_type',
        'capacity',
        'quantity',
        'rate',
        'amount',
        'balance',
        'name',
        'email',
        'phonenumber',
        'address',
    ];
}
