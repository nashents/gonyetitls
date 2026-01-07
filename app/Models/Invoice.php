<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $casts = [
        'accrual_balance' => 'decimal:2', // Ensures it's treated as a decimal
    ];

    public function invoice_products(){
        return $this->hasMany('App\Models\InvoiceProduct');
    }
    public function invoice_items(){
        return $this->hasMany('App\Models\InvoiceItem');
    }
    public function additional_costs(){
        return $this->hasMany('App\Models\AdditionalCost');
    }
    public function discount(){
        return $this->hasOne('App\Models\Discount');
    }
    public function bills(){
        return $this->hasMany('App\Models\Bill');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function transporter(){
        return $this->belongsTo('App\Models\Transporter');
    }
    public function sale(){
        return $this->belongsTo('App\Models\Sale');
    }

    public function receipts(){
        return $this->hasMany('App\Models\Receipt');
    }
    public function payments(){
        return $this->hasMany('App\Models\Payment');
    }
    public function invoice_payments(){
        return $this->hasMany('App\Models\InvoicePayment');
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
    public function bank_accounts(){
        return $this->belongsToMany('App\Models\BankAccount');
    }
    // public function trip(){
    //     return $this->belongsTo('App\Models\Trip');
    // }
    
    public function credit_notes(){
        return $this->hasMany('App\Models\CreditNote');
    }

    public function invoice_trips(){
        return $this->hasMany('App\Models\InvoiceTrip');
    }

    protected $fillable =[
        'user_id',
        'customer_id',
        'currency_id',
        'invoice_number',
        'trip_id',
        'vat',
        'total',
        'subtotal',
        'date',
        'expiry',
        'memo',
        'footer',
        'subheading',
    ];
}
