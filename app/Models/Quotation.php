<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Quotation extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function quotation_items(){
        return $this->hasMany('App\Models\QuotationItem');
    }
     public function additional_costs(){
        return $this->hasMany('App\Models\AdditionalCost');
    }
    public function customer(){
        return $this->belongsTo('App\Models\Customer');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
    public function bank_accounts(){
        return $this->belongsToMany('App\Models\BankAccount');
    }
    public function discount(){
        return $this->hasOne('App\Models\Discount');
    }
    public function company(){
        return $this->belongsTo('App\Models\Company');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function trips(){
        return $this->hasMany('App\Models\Trip');
    }
    public function transport_orders(){
        return $this->hasMany('App\Models\TransportOrder');
    }
}
