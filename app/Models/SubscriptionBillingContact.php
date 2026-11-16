<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Someone at a client company who receives its license invoices and reminders. */
class SubscriptionBillingContact extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'send_email' => 'boolean',
        'send_whatsapp' => 'boolean',
        'status' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
