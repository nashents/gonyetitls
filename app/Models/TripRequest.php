<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * A road leg sent over from the client's freight forwarding instance
 * (Gonyeti Instance Link), waiting to be turned into a trip here.
 */
class TripRequest extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    const PENDING = 'pending';
    const TRIP_CREATED = 'trip_created';
    const REJECTED = 'rejected';
    const CANCELLED = 'cancelled';

    const STATUSES = [
        self::PENDING => 'Pending',
        self::TRIP_CREATED => 'Trip Created',
        self::REJECTED => 'Rejected',
        self::CANCELLED => 'Cancelled',
    ];

    protected $guarded = ['id'];

    protected $casts = [
        'payload' => 'array',
        'planned_departure' => 'datetime',
        'planned_arrival' => 'datetime',
        'responded_at' => 'datetime',
        'changed_since_trip' => 'boolean',
    ];

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function origin()
    {
        return $this->belongsTo(Destination::class, 'origin_id');
    }

    public function destination()
    {
        return $this->belongsTo(Destination::class, 'destination_id');
    }

    public function responder()
    {
        return $this->belongsTo(User::class, 'responded_by');
    }

    public function integration()
    {
        return $this->belongsTo(CompanyIntegration::class, 'company_integration_id');
    }

    public function isPending(): bool
    {
        return $this->status === self::PENDING;
    }
}
