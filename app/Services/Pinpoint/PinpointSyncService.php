<?php

namespace App\Services\Pinpoint;

use App\Services\Pinpoint\Concerns\ResolvesPinpointIntegration;
use Illuminate\Database\Eloquent\Model;

/**
 * Public entry point for live Pinpoint reads (mileage + position) used by the
 * trip/fuel/booking creation screens and the live map. Mirrors
 * App\Services\Cartrack\CartrackSyncService.
 *
 * Every model this is called with (Horse, Trailer, Vehicle) exposes the same
 * `pinpointMapping()` relation and a `transporter()` relation, so this class
 * stays generic across all three.
 */
class PinpointSyncService
{
    use ResolvesPinpointIntegration;

    /**
     * Current mileage/position for a Horse/Trailer/Vehicle, or null when the
     * integration isn't active, the vehicle isn't matched, or the call fails.
     * Callers must fall back to the model's own stored `mileage` on null.
     */
    public function currentSnapshot(Model $model): ?array
    {
        $mapping = $model->pinpointMapping;

        if (! $mapping || empty($mapping->external_id)) {
            return null;
        }

        $companyId   = $this->companyIdForFleetModel($model);
        $integration = $this->activePinpointIntegration($companyId);

        if (! $integration) {
            return null;
        }

        $result = $this->cachedFleetLastPositions($integration);

        if (! ($result['success'] ?? false)) {
            return null;
        }

        // /api2/last responds with `data` keyed by tracker uin, whether one
        // uin or __all_sys_ was requested — a direct key lookup, no scan needed.
        $node = data_get($result['data'], (string) $mapping->external_id);

        return $node ? $this->normalize($node) : null;
    }

    /**
     * Normalise an /api2/last node into ['mileage','latitude','longitude','last_update'].
     * Per the Pinpoint API docs: `lat`/`lng` are the GPS position, `date` is the
     * GPS timestamp, and `io.7` is the Odometer IO channel (kilometres).
     */
    protected function normalize(array $node): ?array
    {
        $mileage   = data_get($node, 'io.7');
        $latitude  = data_get($node, 'lat');
        $longitude = data_get($node, 'lng');

        if ($mileage === null && $latitude === null && $longitude === null) {
            return null;
        }

        return [
            'mileage'     => $mileage,
            'latitude'    => $latitude,
            'longitude'   => $longitude,
            'last_update' => data_get($node, 'date'),
        ];
    }
}
