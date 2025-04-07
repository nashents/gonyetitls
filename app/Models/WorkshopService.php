<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WorkshopService extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
    public function account(){
        return $this->belongsTo('App\Models\Account');
    }
    public function vendor(){
        return $this->belongsTo('App\Models\Vendor');
    }
    public function trailer(){
        return $this->belongsTo('App\Models\Trailer');
    }
    public function horse(){
        return $this->belongsTo('App\Models\Horse');
    }
    public function transporter(){
        return $this->belongsTo('App\Models\Transporter');
    }
    public function expense(){
        return $this->belongsTo('App\Models\Expense');
    }
    public function bill(){
        return $this->hasOne('App\Models\Bill');
    }
}
