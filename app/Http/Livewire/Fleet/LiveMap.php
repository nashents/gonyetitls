<?php

namespace App\Http\Livewire\Fleet;

use App\Models\Horse;
use App\Models\IntegrationMapping;
use App\Models\Trailer;
use App\Models\Vehicle;
use App\Services\Cartrack\Concerns\ResolvesCartrackIntegration;
use App\Services\Integrations\IntegrationGate;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Live vehicle-location board: pulls the whole fleet's latest Cartrack
 * position (ISO 15143-3 snapshot) and plots it against Gonyeti's own
 * Horse/Trailer/Vehicle records via the IntegrationMapping cache built by
 * `cartrack:match-vehicles`, so markers show Gonyeti fleet numbers instead
 * of raw Cartrack ids.
 */
class LiveMap extends Component
{
    use ResolvesCartrackIntegration;

    /** Refresh cadence for the browser poll — the underlying fetch is itself cached (see ResolvesCartrackIntegration::cachedFleetSnapshot). */
    public int $pollSeconds = 30;

    protected ?string $apiError = null;

    protected const ENTITY_MODELS = [
        'horse_vehicle'   => [Horse::class, 'Horse'],
        'trailer_vehicle' => [Trailer::class, 'Trailer'],
        'vehicle_vehicle' => [Vehicle::class, 'Vehicle'],
    ];

    /** Follows the app-wide convention: every integration's UI states plainly when it isn't active, rather than silently showing nothing. */
    public function getCartrackEnabledProperty(): bool
    {
        return IntegrationGate::enabledForUser('cartrack');
    }

    public function render()
    {
        return view('livewire.fleet.live-map', [
            'markers'  => $this->cartrackEnabled ? $this->markers() : [],
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
                'latitude'    => (float) $latitude,
                'longitude'   => (float) $longitude,
                'last_update' => data_get($node, 'Location.DateTime'),
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
