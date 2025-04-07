<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sale extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;
  

    public function invoice_products(){
        return $this->hasMany('App\Models\InvoiceProduct');
    }

    public function sale_items(){
        return $this->hasMany('App\Models\SaleItem');
    }
    public function invoice(){
        return $this->hasOne('App\Models\Invoice');
    }
    public function bills(){
        return $this->hasMany('App\Models\Bill');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }

    public function receipts(){
        return $this->hasMany('App\Models\Receipt');
    }
    public function payments(){
        return $this->hasMany('App\Models\Payment');
    }
    public function sale_payments(){
        return $this->hasMany('App\Models\SalePayment');
    }
    
    public function company(){
        return $this->belongsTo('App\Models\Company');
    }

    public function customer(){
        return $this->belongsTo('App\Models\Customer');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
    public function bank_account(){
        return $this->belongsTo('App\Models\BankAccount');
    }
   
    public function credit_notes(){
        return $this->hasMany('App\Models\CreditNote');
    }
  
}
