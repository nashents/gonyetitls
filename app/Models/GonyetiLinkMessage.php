<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Outbox/inbox log for Gonyeti Instance Link messages. */
class GonyetiLinkMessage extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'payload' => 'array',
        'sent_at' => 'datetime',
    ];

    public function integration()
    {
        return $this->belongsTo(CompanyIntegration::class, 'company_integration_id');
    }
}
