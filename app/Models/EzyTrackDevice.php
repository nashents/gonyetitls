<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Latest known state of one EzyTrack (Digital Matter) tracking device.
 * Upserted by App\Services\EzyTrack\EzyTrackPositionIngestor on every
 * inbound webhook POST — see database migration for column meanings.
 */
class EzyTrackDevice extends Model
{
    // Eloquent's convention would derive "ezy_track_devices"; the migration uses "ezytrack_devices".
    protected $table = 'ezytrack_devices';

    protected $guarded = [];

    protected $casts = [
        'last_record_at' => 'datetime',
        'last_gps_at'    => 'datetime',
        'last_seen_at'   => 'datetime',
        'latitude'       => 'float',
        'longitude'      => 'float',
        'speed_kmh'      => 'float',
    ];

    public function hasPosition()
    {
        return $this->latitude !== null && $this->longitude !== null;
    }
}
