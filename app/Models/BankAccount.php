<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BankAccount extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function payments(){
        return $this->hasMany('App\Models\Payment');
    }
    public function sales(){
        return $this->hasMany('App\Models\Sale');
    }
    public function company(){
        return $this->hasMany('App\Models\Company');
    }
    public function invoices(){
        return $this->belongsToMany('App\Models\Invoice');
    }
    public function account(){
        return $this->hasOne('App\Models\Account');
    }
    public function quotations(){
        return $this->belongsToMany('App\Models\Quotation');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }

}
