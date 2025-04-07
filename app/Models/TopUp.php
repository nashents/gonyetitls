<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TopUp extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;
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

    public function container(){
        return $this->belongsTo('App\Models\Container');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function payments(){
        return $this->hasMany('App\Models\Payment');
    }
    public function bill(){
        return $this->hasOne('App\Models\Bill');
    }

}
