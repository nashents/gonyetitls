<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvoiceItem extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    /**
     * Trip-expense recovery (TripExpenseRecoveryService):
     *  - a trip line on an invoice to a Transporter recovers Transporter
     *    Recoverable, so it credits that account rather than revenue;
     *  - recharge_amount (the Customer-category expenses folded into a
     *    customer line) can never exceed the line itself.
     */
    protected static function booted()
    {
        static::saving(function (InvoiceItem $item) {
            $isTripLine = $item->trip_id || $item->trip_transport_order_id;
            $invoiceTo = $item->invoice_id ? Invoice::whereKey($item->invoice_id)->value('invoice_to') : null;

            if ($isTripLine && $invoiceTo === 'Transporter') {
                $account = Account::where('name', \App\Services\Accounting\TripExpenseRecoveryService::TRANSPORTER_RECOVERABLE)->first();
                if ($account) {
                    $item->account_id = $account->id;
                }
                $item->recharge_amount = null;
                return;
            }

            $recharge = is_numeric($item->recharge_amount) ? (float) $item->recharge_amount : 0;
            $subtotal = is_numeric($item->subtotal) ? (float) $item->subtotal : 0;
            $recharge = round(min(max($recharge, 0), max($subtotal, 0)), 2);
            $item->recharge_amount = ($isTripLine && $recharge > 0) ? $recharge : null;
        });
    }

    public function inventory(){
        return $this->belongsTo('App\Models\Inventory');
    }
    public function invoice(){
        return $this->belongsTo('App\Models\Invoice');
    }
    public function booking(){
        return $this->belongsTo('App\Models\Booking');
    }
    public function tax(){
        return $this->belongsTo('App\Models\Tax');
    }
    public function account(){
        return $this->belongsTo('App\Models\Account');
    }
    public function trip(){
        return $this->belongsTo('App\Models\Trip');
    }
    public function trip_transport_order(){
        return $this->belongsTo('App\Models\TripTransportOrder');
    }
    public function transport_order(){
        return $this->belongsTo('App\Models\TransportOrder');
    }
    public function income_stream(){
        return $this->belongsTo('App\Models\IncomeStream');
    }
    public function product(){
        return $this->belongsTo('App\Models\Product');
    }
    public function payment_items(){
        return $this->hasMany('App\Models\InvoicePaymentItem');
    }
}
