<?php

namespace App\Services\Fleet;

use App\Models\EzyTrackDevice;
use App\Models\IntegrationMapping;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Services\Cartrack\Concerns\ResolvesCartrackIntegration;
use App\Services\EzyTrack\Concerns\ResolvesEzyTrackIntegration;
use App\Services\FanTracker\Concerns\ResolvesFanTrackerIntegration;
use App\Services\Pinpoint\Concerns\ResolvesPinpointIntegration;

/**
 * Resolves live positions (trucks AND rigid vehicles) across every
 * configured tracking provider, keyed by an "asset key" ("horse:{id}" or
 * "vehicle:{id}") rather than by display label.
 *
 * Deliberately separate from App\Http\Livewire\Fleet\LiveMap (which builds
 * map markers keyed by label) so the Live Fleet Map page is left untouched;
 * this is the shared lookup used by both the asset-position polling command
 * (fleet:log-asset-positions) and the Asset Positions table's live columns.
 */
class FleetPositionResolver
{
    use ResolvesCartrackIntegration;
    use ResolvesEzyTrackIntegration;
    use ResolvesFanTrackerIntegration {
        ResolvesCartrackIntegration::companyIdForFleetModel insteadof ResolvesFanTrackerIntegration;
    }
    use ResolvesPinpointIntegration {
        ResolvesCartrackIntegration::companyIdForFleetModel insteadof ResolvesPinpointIntegration;
    }

    /** entity_type -> asset key prefix, shared by every provider's mapping lookup. */
    protected const ASSET_PREFIXES = [
        'horse'   => 'horse',
        'vehicle' => 'vehicle',
    ];

    /**
     * @return Collection<string, array{asset_key:string, asset_type:string, local_id:int, lat:float, lng:float, speed:?float, source:string, observed_at:?string}>
     *         keyed by "horse:{id}" / "vehicle:{id}"
     */
    public function resolve(?int $companyId): Collection
    {
        return collect()
            ->merge($this->safely('cartrack', fn () => $this->cartrackPositions($companyId)))
            ->merge($this->safely('fantracker', fn () => $this->fanTrackerPositions($companyId)))
            ->merge($this->safely('pinpoint', fn () => $this->pinpointPositions($companyId)))
            ->merge($this->safely('ezytrack', fn () => $this->ezyTrackPositions($companyId)));
    }

    /**
     * One provider's positions never being resolvable (unreachable API, or —
     * as happens when a company's stored integration credentials were
     * encrypted under a since-rotated APP_KEY — an undecryptable
     * `credentials` cast throwing DecryptException from deep inside the
     * driver constructor) must not take the whole Asset Positions page down;
     * it should just mean that provider contributes no positions this run.
     */
    protected function safely(string $provider, \Closure $resolver): Collection
    {
        try {
            return $resolver();
        } catch (\Throwable $e) {
            Log::warning("FleetPositionResolver: {$provider} position lookup failed: " . $e->getMessage());

            return collect();
        }
    }

    /** "horse_vehicle" -> "horse", "vehicle_tracker" -> "vehicle", etc. */
    protected function assetTypeFromEntityType(string $entityType): ?string
    {
        foreach (self::ASSET_PREFIXES as $prefix) {
            if (str_starts_with($entityType, $prefix . '_')) {
                return $prefix;
            }
        }

        return null;
    }

    protected function assetKey(string $assetType, $localId): string
    {
        return $assetType . ':' . $localId;
    }

    protected function cartrackPositions(?int $companyId): Collection
    {
        $integration = $this->activeCartrackIntegration($companyId);
        if (! $integration) {
            return collect();
        }

        $result = $this->cachedFleetSnapshot($integration);
        if (! ($result['success'] ?? false)) {
            return collect();
        }

        $mappings = IntegrationMapping::where('company_integration_id', $integration->id)
            ->whereIn('entity_type', ['horse_vehicle', 'vehicle_vehicle'])
            ->get()
            ->keyBy(fn ($m) => strtoupper((string) $m->external_reference));

        $positions = collect();

        foreach ($this->fleetEquipmentRows($result) as $node) {
            $latitude  = data_get($node, 'Location.Latitude');
            $longitude = data_get($node, 'Location.Longitude');
            $identifier = data_get($node, 'EquipmentHeader.EquipmentID');

            if ($latitude === null || $longitude === null || ! $identifier) {
                continue;
            }

            $mapping = $mappings->get(strtoupper((string) $identifier));
            $assetType = $mapping ? $this->assetTypeFromEntityType($mapping->entity_type) : null;
            if (! $mapping || ! $assetType) {
                continue;
            }

            $positions->put($this->assetKey($assetType, $mapping->local_id), [
                'asset_key'   => $this->assetKey($assetType, $mapping->local_id),
                'asset_type'  => $assetType,
                'local_id'    => (int) $mapping->local_id,
                'lat'         => (float) $latitude,
                'lng'         => (float) $longitude,
                'speed'       => data_get($node, 'Location.Speed'),
                'source'      => 'cartrack',
                'observed_at' => data_get($node, 'Location.DateTime'),
            ]);
        }

        return $positions;
    }

    protected function fanTrackerPositions(?int $companyId): Collection
    {
        $integration = $this->activeFanTrackerIntegration($companyId);
        if (! $integration) {
            return collect();
        }

        $result = $this->cachedFleetStates($integration);
        if (! ($result['success'] ?? false)) {
            return collect();
        }

        $mappings = IntegrationMapping::where('company_integration_id', $integration->id)
            ->whereIn('entity_type', ['horse_tracker', 'vehicle_tracker'])
            ->get()
            ->keyBy('external_id');

        $positions = collect();

        foreach ((array) data_get($result['data'], 'states', []) as $trackerId => $node) {
            $latitude  = data_get($node, 'gps.location.lat');
            $longitude = data_get($node, 'gps.location.lng');

            if ($latitude === null || $longitude === null) {
                continue;
            }

            $mapping = $mappings->get((string) $trackerId);
            $assetType = $mapping ? $this->assetTypeFromEntityType($mapping->entity_type) : null;
            if (! $mapping || ! $assetType) {
                continue;
            }

            $positions->put($this->assetKey($assetType, $mapping->local_id), [
                'asset_key'   => $this->assetKey($assetType, $mapping->local_id),
                'asset_type'  => $assetType,
                'local_id'    => (int) $mapping->local_id,
                'lat'         => (float) $latitude,
                'lng'         => (float) $longitude,
                'speed'       => data_get($node, 'gps.speed'),
                'source'      => 'fantracker',
                'observed_at' => data_get($node, 'last_update'),
            ]);
        }

        return $positions;
    }

    protected function pinpointPositions(?int $companyId): Collection
    {
        $integration = $this->activePinpointIntegration($companyId);
        if (! $integration) {
            return collect();
        }

        $result = $this->cachedFleetLastPositions($integration);
        if (! ($result['success'] ?? false)) {
            return collect();
        }

        $mappings = IntegrationMapping::where('company_integration_id', $integration->id)
            ->whereIn('entity_type', ['horse_pinpoint', 'vehicle_pinpoint'])
            ->get()
            ->keyBy('external_id');

        $positions = collect();

        foreach ((array) $result['data'] as $uin => $node) {
            $latitude  = data_get($node, 'lat');
            $longitude = data_get($node, 'lng');

            if ($latitude === null || $longitude === null) {
                continue;
            }

            $mapping = $mappings->get((string) $uin);
            $assetType = $mapping ? $this->assetTypeFromEntityType($mapping->entity_type) : null;
            if (! $mapping || ! $assetType) {
                continue;
            }

            $positions->put($this->assetKey($assetType, $mapping->local_id), [
                'asset_key'   => $this->assetKey($assetType, $mapping->local_id),
                'asset_type'  => $assetType,
                'local_id'    => (int) $mapping->local_id,
                'lat'         => (float) $latitude,
                'lng'         => (float) $longitude,
                'speed'       => data_get($node, 'speed'),
                'source'      => 'pinpoint',
                'observed_at' => data_get($node, 'date'),
            ]);
        }

        return $positions;
    }

    protected function ezyTrackPositions(?int $companyId): Collection
    {
        $integration = $this->activeEzyTrackIntegration($companyId);
        if (! $integration) {
            return collect();
        }

        $mappings = IntegrationMapping::where('company_integration_id', $integration->id)
            ->whereIn('entity_type', ['horse_ezytrack_device', 'vehicle_ezytrack_device'])
            ->get()
            ->keyBy('external_reference');

        if ($mappings->isEmpty()) {
            return collect();
        }

        $devices = EzyTrackDevice::whereIn('serial_number', $mappings->keys())->get()->keyBy('serial_number');

        $positions = collect();

        foreach ($mappings as $serial => $mapping) {
            $device = $devices->get($serial);
            $assetType = $this->assetTypeFromEntityType($mapping->entity_type);
            if (! $device || ! $device->hasPosition() || ! $assetType) {
                continue;
            }

            $positions->put($this->assetKey($assetType, $mapping->local_id), [
                'asset_key'   => $this->assetKey($assetType, $mapping->local_id),
                'asset_type'  => $assetType,
                'local_id'    => (int) $mapping->local_id,
                'lat'         => (float) $device->latitude,
                'lng'         => (float) $device->longitude,
                'speed'       => $device->speed_kmh,
                'source'      => 'ezytrack',
                'observed_at' => optional($device->last_gps_at)->toDateTimeString(),
            ]);
        }

        return $positions;
    }
}
