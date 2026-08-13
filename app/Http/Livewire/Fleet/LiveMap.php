<?php

namespace App\Http\Livewire\Fleet;

use App\Models\EzyTrackDevice;
use App\Models\Horse;
use App\Models\IntegrationMapping;
use App\Models\Trailer;
use App\Models\Vehicle;
use App\Services\Cartrack\Concerns\ResolvesCartrackIntegration;
use App\Services\EzyTrack\Concerns\ResolvesEzyTrackIntegration;
use App\Services\FanTracker\Concerns\ResolvesFanTrackerIntegration;
use App\Services\Integrations\IntegrationGate;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Live vehicle-location board: plots every configured tracking source
 * against Gonyeti's own Horse/Trailer/Vehicle records.
 *
 *  - Cartrack: pulls the whole fleet's latest position (ISO 15143-3
 *    snapshot) and resolves it via the IntegrationMapping cache built by
 *    `cartrack:match-vehicles`.
 *  - EzyTrack: reads the locally cached state of whichever devices have
 *    pushed us a position (see EzyTrackPositionIngestor), resolved via the
 *    manual links made on the "EzyTrack Device Mapping" screen.
 *  - FanTracker: pulls tracker/get_states for the whole fleet and resolves
 *    it via the IntegrationMapping cache built by `fantracker:match-vehicles`.
 *
 * Every source shows Gonyeti fleet numbers instead of raw provider ids.
 */
class LiveMap extends Component
{
    use ResolvesCartrackIntegration;
    use ResolvesEzyTrackIntegration;
    use ResolvesFanTrackerIntegration {
        ResolvesCartrackIntegration::companyIdForFleetModel insteadof ResolvesFanTrackerIntegration;
    }

    /** Refresh cadence for the browser poll — the underlying fetch is itself cached (see ResolvesCartrackIntegration::cachedFleetSnapshot). */
    public int $pollSeconds = 30;

    protected ?string $apiError = null;

    protected const ENTITY_MODELS = [
        'horse_vehicle'   => [Horse::class, 'Horse'],
        'trailer_vehicle' => [Trailer::class, 'Trailer'],
        'vehicle_vehicle' => [Vehicle::class, 'Vehicle'],
    ];

    protected const EZYTRACK_ENTITY_MODELS = [
        'horse_ezytrack_device'   => [Horse::class, 'Horse'],
        'trailer_ezytrack_device' => [Trailer::class, 'Trailer'],
        'vehicle_ezytrack_device' => [Vehicle::class, 'Vehicle'],
    ];

    protected const FANTRACKER_ENTITY_MODELS = [
        'horse_tracker'   => [Horse::class, 'Horse'],
        'trailer_tracker' => [Trailer::class, 'Trailer'],
        'vehicle_tracker' => [Vehicle::class, 'Vehicle'],
    ];

    /** Follows the app-wide convention: every integration's UI states plainly when it isn't active, rather than silently showing nothing. */
    public function getCartrackEnabledProperty(): bool
    {
        return IntegrationGate::enabledForUser('cartrack');
    }

    public function getEzyTrackEnabledProperty(): bool
    {
        return IntegrationGate::enabledForUser('ezytrack');
    }

    public function getFanTrackerEnabledProperty(): bool
    {
        return IntegrationGate::enabledForUser('fantracker');
    }

    public function render()
    {
        $markers = array_merge(
            $this->cartrackEnabled ? $this->markers() : [],
            $this->ezyTrackEnabled ? $this->ezyTrackMarkers() : [],
            $this->fanTrackerEnabled ? $this->fanTrackerMarkers() : []
        );

        return view('livewire.fleet.live-map', [
            'markers'  => $markers,
            'apiError' => $this->apiError,
        ]);
    }

    protected function markers(): array
    {
        $companyId = $this->currentCompanyId();
        $integration = $this->activeCartrackIntegration($companyId);

        if (! $integration) {
            return [];
        }

        return $this->buildMarkers($integration);
    }

    protected function buildMarkers($integration): array
    {
        $result = $this->cachedFleetSnapshot($integration);

        if (! ($result['success'] ?? false)) {
            $this->apiError = $result['error'] ?? 'Cartrack request failed.';
            return [];
        }

        $mappings = IntegrationMapping::where('company_integration_id', $integration->id)
            ->whereIn('entity_type', array_keys(self::ENTITY_MODELS))
            ->get()
            ->keyBy(fn ($m) => strtoupper((string) $m->external_reference));

        $markers = [];

        foreach ($this->fleetEquipmentRows($result) as $node) {
            $latitude  = data_get($node, 'Location.Latitude');
            $longitude = data_get($node, 'Location.Longitude');

            if ($latitude === null || $longitude === null) {
                continue;
            }

            $identifier = data_get($node, 'EquipmentHeader.EquipmentID');
            $mapping = $identifier ? $mappings->get(strtoupper((string) $identifier)) : null;

            $label = $identifier ?? 'Unknown vehicle';
            $type  = null;

            if ($mapping) {
                [$modelClass, $typeLabel] = self::ENTITY_MODELS[$mapping->entity_type];
                $model = $modelClass::find($mapping->local_id);
                if ($model) {
                    $label = $model->fleet_number ?? $model->registration_number ?? $label;
                    $type  = $typeLabel;
                }
            }

            $markers[] = [
                'label'       => $label,
                'type'        => $type,
                'source'      => 'Cartrack',
                'latitude'    => (float) $latitude,
                'longitude'   => (float) $longitude,
                'last_update' => data_get($node, 'Location.DateTime'),
            ];
        }

        return $markers;
    }

    /** EzyTrack markers: locally cached device state (pushed via webhook), resolved through the manual device mapping. */
    protected function ezyTrackMarkers(): array
    {
        $companyId = $this->currentCompanyId();
        $integration = $this->activeEzyTrackIntegration($companyId);

        if (! $integration) {
            return [];
        }

        $mappings = IntegrationMapping::where('company_integration_id', $integration->id)
            ->whereIn('entity_type', array_keys(self::EZYTRACK_ENTITY_MODELS))
            ->get()
            ->keyBy('external_reference');

        if ($mappings->isEmpty()) {
            return [];
        }

        $devices = EzyTrackDevice::whereIn('serial_number', $mappings->keys())->get()->keyBy('serial_number');

        $markers = [];

        foreach ($mappings as $serial => $mapping) {
            $device = $devices->get($serial);

            if (! $device || ! $device->hasPosition()) {
                continue;
            }

            [$modelClass, $typeLabel] = self::EZYTRACK_ENTITY_MODELS[$mapping->entity_type];
            $model = $modelClass::find($mapping->local_id);

            if (! $model) {
                continue;
            }

            $markers[] = [
                'label'       => $model->fleet_number ?? $model->registration_number ?? $serial,
                'type'        => $typeLabel,
                'source'      => 'EzyTrack',
                'latitude'    => (float) $device->latitude,
                'longitude'   => (float) $device->longitude,
                'last_update' => optional($device->last_gps_at)->toDateTimeString(),
            ];
        }

        return $markers;
    }

    /** FanTracker markers: tracker/get_states for the whole fleet, resolved through the mapping built by `fantracker:match-vehicles`. */
    protected function fanTrackerMarkers(): array
    {
        $companyId = $this->currentCompanyId();
        $integration = $this->activeFanTrackerIntegration($companyId);

        if (! $integration) {
            return [];
        }

        $result = $this->cachedFleetStates($integration);

        if (! ($result['success'] ?? false)) {
            $this->apiError = $result['error'] ?? 'FanTracker request failed.';
            return [];
        }

        $mappings = IntegrationMapping::where('company_integration_id', $integration->id)
            ->whereIn('entity_type', array_keys(self::FANTRACKER_ENTITY_MODELS))
            ->get()
            ->keyBy('external_id');

        $markers = [];

        foreach ((array) data_get($result['data'], 'states', []) as $trackerId => $node) {
            $latitude  = data_get($node, 'gps.location.lat');
            $longitude = data_get($node, 'gps.location.lng');

            if ($latitude === null || $longitude === null) {
                continue;
            }

            $mapping = $mappings->get((string) $trackerId);

            $label = 'Unknown vehicle';
            $type  = null;

            if ($mapping) {
                [$modelClass, $typeLabel] = self::FANTRACKER_ENTITY_MODELS[$mapping->entity_type];
                $model = $modelClass::find($mapping->local_id);
                if ($model) {
                    $label = $model->fleet_number ?? $model->registration_number ?? $label;
                    $type  = $typeLabel;
                }
            }

            $markers[] = [
                'label'       => $label,
                'type'        => $type,
                'source'      => 'FanTracker',
                'latitude'    => (float) $latitude,
                'longitude'   => (float) $longitude,
                'last_update' => data_get($node, 'last_update'),
            ];
        }

        return $markers;
    }

    protected function currentCompanyId(): ?int
    {
        $user = Auth::user();

        return optional(optional($user)->employee)->company_id ?? optional($user)->company_id;
    }
}
