<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A payment a client made against one of its Gonyeti license invoices. */
class SubscriptionPayment extends Model
{
    const METHODS = ['Bank Transfer', 'Cash', 'EcoCash', 'Card', 'Other'];

    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'date',
        'amount' => 'float',
    ];

    public function invoice()
    {
        return $this->belongsTo(SubscriptionInvoice::class, 'subscription_invoice_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by')->withTrashed();
    }
}
