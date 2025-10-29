<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TripExpense extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function trip(){
        return $this->belongsTo('App\Models\Trip');
    }
    public function payment_method(){
        return $this->belongsTo('App\Models\PaymentMethod');
    }
    public function expense(){
        return $this->belongsTo('App\Models\Expense');
    }
    public function allowance(){
        return $this->belongsTo('App\Models\Allowance');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
    public function fuel(){
        return $this->belongsTo('App\Models\Fuel');
    }
    public function bill(){
        return $this->hasOne('App\Models\Bill');
    }

    protected $fillable = [
        'user_id',
        'currency_id',
        'expense_id',
        'trip_id',
        'amount',
    ];
}
