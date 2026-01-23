<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function trip(){
        return $this->belongsTo('App\Models\Trip');
    }
    public function transaction_type(){
        return $this->belongsTo('App\Models\TransactionType');
    }
    public function loan(){
        return $this->belongsTo('App\Models\Loan');
    }
    public function sale(){
        return $this->belongsTo('App\Models\Sale');
    }
    public function account(){
        return $this->belongsTo('App\Models\Account');
    }
    public function driver(){
        return $this->belongsTo('App\Models\Driver');
    }
    public function container(){
        return $this->belongsTo('App\Models\Container');
    }
    public function requisition(){
        return $this->belongsTo('App\Models\Requisition');
    }
    public function top_up(){
        return $this->belongsTo('App\Models\TopUp');
    }
    public function invoice(){
        return $this->belongsTo('App\Models\Invoice');
    }
    public function invoice_payments(){
        return $this->hasMany('App\Models\InvoicePayment');
    }
    public function bill(){
        return $this->belongsTo('App\Models\Bill');
    }
     public function bill_payment(){
        return $this->hasOne('App\Models\BillPayment');
    }
    public function cash_flow(){
        return $this->hasOne('App\Models\CashFlow');
    }
    public function recovery(){
        return $this->belongsTo('App\Models\Recovery');
    }
    public function vendor(){
        return $this->belongsTo('App\Models\Vendor');
    }
    public function transporter(){
        return $this->belongsTo('App\Models\Transporter');
    }
  
    public function customer(){
        return $this->belongsTo('App\Models\Customer');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function documents(){
        return $this->hasMany('App\Models\Document');
    }
    public function denominations(){
        return $this->hasMany('App\Models\Denomination');
    }
    public function receipt(){
        return $this->hasOne('App\Models\Receipt');
    }
    public function bank_account(){
        return $this->belongsTo('App\Models\BankAccount');
    }
}
