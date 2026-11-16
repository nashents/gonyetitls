<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Delivery log: one row per invoice / reminder sent to one recipient on one channel. */
class SubscriptionNotification extends Model
{
    protected $guarded = ['id'];

    public function invoice()
    {
        return $this->belongsTo(SubscriptionInvoice::class, 'subscription_invoice_id');
    }
}
