<?php

namespace App\Services\FanTracker;

use App\Services\FanTracker\Concerns\ResolvesFanTrackerIntegration;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Public entry point for live FanTracker reads (mileage + position) used by
 * the trip/booking creation screens and the live map. Mirrors
 * App\Services\Cartrack\CartrackSyncService.
 *
 * Every model this is called with (Horse, Trailer, Vehicle) exposes the same
 * `fanTrackerMapping()` relation and a `transporter()` relation, so this
 * class stays generic across all three.
 */
class FanTrackerSyncService
{
    use ResolvesFanTrackerIntegration;

    /**
     * Current mileage/position for a Horse/Trailer/Vehicle, or null when the
     * integration isn't active, the vehicle isn't matched, or the call fails.
     * Callers must fall back to the model's own stored `mileage` on null.
     */
    public function currentSnapshot(Model $model): ?array
    {
        $mapping = $model->fanTrackerMapping;

        if (! $mapping || empty($mapping->external_id)) {
            return null;
        }

        $companyId   = $this->companyIdForFleetModel($model);
        $integration = $this->activeFanTrackerIntegration($companyId);

        if (! $integration) {
            return null;
        }

        $trackerId = (int) $mapping->external_id;

        $counters = $this->cachedCounters($integration, $trackerId);
        $state    = $this->cachedState($integration, $trackerId);

        $mileage = null;
        if ($counters['success'] ?? false) {
            $mileage = collect(data_get($counters['data'], 'list', []))
                ->firstWhere('type', 'odometer')['value'] ?? null;
        }

        $latitude  = null;
        $longitude = null;
        $lastUpdate = null;
        if ($state['success'] ?? false) {
            $node = data_get($state['data'], "states.{$trackerId}");
            $latitude   = data_get($node, 'gps.location.lat');
            $longitude  = data_get($node, 'gps.location.lng');
            $lastUpdate = data_get($node, 'last_update');
        }

        if ($mileage === null && $latitude === null && $longitude === null) {
            return null;
        }

        return [
            'mileage'     => $mileage,
            'latitude'    => $latitude,
            'longitude'   => $longitude,
            'last_update' => $lastUpdate,
        ];
    }

    /** Cached GET /tracker/get_counters for one tracker (60s, mirrors Cartrack's per-vehicle read cadence). */
    protected function cachedCounters($integration, int $trackerId): array
    {
        return Cache::remember(
            "fantracker:counters:{$integration->id}:{$trackerId}",
            60,
            fn () => $this->fanTrackerDriverFor($integration)->getCounters($trackerId)
        );
    }

    /** Cached GET /tracker/get_states for one tracker (30s). Kept separate from cachedFleetStates so a single mileage lookup doesn't force-fetch the whole fleet. */
    protected function cachedState($integration, int $trackerId): array
    {
        return Cache::remember(
            "fantracker:state:{$integration->id}:{$trackerId}",
            30,
            fn () => $this->fanTrackerDriverFor($integration)->getStates([$trackerId])
        );
    }
}
