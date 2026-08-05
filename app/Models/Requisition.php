<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Requisition extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function purchase(){
        return $this->belongsTo('App\Models\Purchase');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function trip(){
        return $this->belongsTo('App\Models\Trip');
    }
    public function booking(){
        return $this->belongsTo('App\Models\Booking');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
    public function bill(){
        return $this->hasOne('App\Models\Bill');
    }
    public function requisition_items(){
        return $this->hasMany('App\Models\RequisitionItem');
    }
    public function payments(){
        return $this->hasMany('App\Models\Payment');
    }
    public function department(){
        return $this->belongsTo('App\Models\Department');
    }
    public function employee(){
        return $this->belongsTo('App\Models\Employee');
    }
    public function expense(){
        return $this->belongsTo('App\Models\Expense');
    }
    public function account(){
        return $this->belongsTo('App\Models\Account');
    }
    public function asset(){
        return $this->belongsTo('App\Models\Asset');
    }
    public function driver(){
        return $this->belongsTo('App\Models\Driver');
    }
    public function horse(){
        return $this->belongsTo('App\Models\Horse');
    }
    public function trailer(){
        return $this->belongsTo('App\Models\Trailer');
    }
    public function transporter(){
        return $this->belongsTo('App\Models\Transporter');
    }
    public function vehicle(){
        return $this->belongsTo('App\Models\Vehicle');
    }
    public function attached_employee(){
        return $this->belongsTo('App\Models\Employee', 'attached_employee_id');
    }
}
