<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Retread extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function bill(){
        return $this->HasOne('App\Models\Bill');
    }
    public function account(){
        return $this->belongsTo('App\Models\Account');
    }
    public function account_type(){
        return $this->belongsTo('App\Models\AccountType');
    }
    public function vendor(){
        return $this->belongsTo('App\Models\Vendor');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
    public function retread_tyres(){
        return $this->hasMany('App\Models\RetreadTyre');
    }
    public function retread_items(){
        return $this->hasMany('App\Models\RetreadItem');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function documents(){
        return $this->hasMany('App\Models\Document');
    }
  
}
