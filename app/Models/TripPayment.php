<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TripPayment extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $casts = [
        'payment_date'    => 'date',
        'amount'          => 'float',
        'exchange_rate'   => 'float',
        'trip_amount'     => 'float',
        'exchange_amount' => 'float',
    ];

    public function trip(){
        return $this->belongsTo('App\Models\Trip');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function invoice_payment_item(){
        return $this->belongsTo('App\Models\InvoicePaymentItem');
    }
}
