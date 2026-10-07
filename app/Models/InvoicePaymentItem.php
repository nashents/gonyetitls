<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvoicePaymentItem extends Model
{
    use HasFactory;

    protected $casts = [
        'amount'      => 'float',
        'trip_amount' => 'float',
    ];

    public function invoice_payment(){
        return $this->belongsTo('App\Models\InvoicePayment');
    }
    public function payment(){
        return $this->belongsTo('App\Models\Payment');
    }
    public function invoice(){
        return $this->belongsTo('App\Models\Invoice');
    }
    public function invoice_item(){
        return $this->belongsTo('App\Models\InvoiceItem');
    }
    public function trip(){
        return $this->belongsTo('App\Models\Trip');
    }
}
