<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TripDocumentSignature extends Model
{
    const DELIVERY_NOTE = 'delivery_note';

    // Signing parties on the delivery note, in print order.
    const DELIVERY_NOTE_ROLES = [
        'dispatcher' => 'Dispatched By (Consignor)',
        'driver'     => 'Driver',
        'receiver'   => 'Received By (Consignee)',
    ];

    protected $fillable = [
        'trip_id',
        'document',
        'role',
        'signer_name',
        'signer_designation',
        'signature',
        'signed_at',
        'user_id',
        'ip_address',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
