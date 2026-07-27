<?php

namespace App\Services\Cartrack;

use App\Services\Cartrack\Concerns\ResolvesCartrackIntegration;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Public entry point for live Cartrack reads (mileage + position) used by the
 * trip/fuel/booking creation screens and the live map.
 *
 * Every model this is called with (Horse, Trailer, Vehicle) exposes the same
 * `cartrackMapping()` relation and a `transporter()` relation, so this class
 * stays generic across all three.
 */
class CartrackSyncService
{
    use ResolvesCartrackIntegration;

    /**
     * Current mileage/position for a Horse/Trailer/Vehicle, or null when the
     * integration isn't active, the vehicle isn't matched, or the call fails.
     * Callers must fall back to the model's own stored `mileage` on null.
     */
    public function currentSnapshot(Model $model): ?array
    {
        $mapping = $model->cartrackMapping;

        if (! $mapping || empty($mapping->external_reference)) {
            return null;
        }

        $companyId   = $this->companyIdForFleetModel($model);
        $integration = $this->activeCartrackIntegration($companyId);

        if (! $integration) {
            return null;
        }

        // Pulled from the fleet-wide snapshot (shared cache with LiveMap) rather
        // than GET /aemp/iso15143-3/beta/equipment/{id} — that endpoint returned
        // a 500 for the registration-style id and a 404 for the numeric Cartrack
        // vehicle id when tested live; the fleet endpoint reliably includes
        // every matched vehicle in one call.
        $rows = $this->fleetEquipmentRows($this->cachedFleetSnapshot($integration));

        $target = strtoupper((string) $mapping->external_reference);
        $node = $rows->first(
            fn ($row) => strtoupper((string) data_get($row, 'EquipmentHeader.EquipmentID')) === $target
        );

        if (! $node) {
            return null;
        }

        $snapshot = $this->normalize($node);

        if (! $snapshot) {
            Log::info("Cartrack snapshot: no recognised fields for mapping #{$mapping->id} — " . json_encode($node));
        }

        return $snapshot;
    }

    /**
     * Normalise an ISO 15143-3 (AEMP) equipment row into
     * ['mileage','latitude','longitude','last_update']. Confirmed against a
     * live GET /aemp/iso15143-3/beta/fleet response on 2026-07-27:
     * EquipmentHeader.EquipmentID, Location.{Latitude,Longitude,DateTime},
     * Distance.Odometer (kilometres per Distance.OdometerUnits).
     */
    protected function normalize(array $node): ?array
    {
        $mileage   = data_get($node, 'Distance.Odometer');
        $latitude  = data_get($node, 'Location.Latitude');
        $longitude = data_get($node, 'Location.Longitude');

        if ($mileage === null && $latitude === null && $longitude === null) {
            return null;
        }

        return [
            'mileage'     => $mileage,
            'latitude'    => $latitude,
            'longitude'   => $longitude,
            'last_update' => data_get($node, 'Location.DateTime'),
        ];
    }
}
