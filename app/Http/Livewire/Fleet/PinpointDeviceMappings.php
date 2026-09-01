<?php

namespace App\Http\Livewire\Fleet;

use App\Models\Horse;
use App\Models\IntegrationMapping;
use App\Models\Trailer;
use App\Models\Vehicle;
use App\Services\Pinpoint\Concerns\ResolvesPinpointIntegration;
use App\Services\Pinpoint\PinpointVehicleMatcher;
use App\Services\Integrations\IntegrationGate;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * UI equivalent of `php artisan pinpoint:match-vehicles`, so nobody needs
 * console access to link a company's fleet. Lists every tracker on the
 * Pinpoint account (live, via GET /api2/trackers) next to its current
 * mapping (if any), offers a one-click "Run Auto-Match" (registration_number
 * vs plate/name — see PinpointVehicleMatcher), and — since that auto-match
 * only works when a tracker's plate or name already holds the registration
 * number — a manual "Map" action per tracker, same mechanism FanTracker's/
 * EzyTrack's device mapping screens use. Mirrors
 * App\Http\Livewire\Fleet\FanTrackerDeviceMappings.
 */
class PinpointDeviceMappings extends Component
{
    use ResolvesPinpointIntegration;

    protected const ENTITY_MODELS = [
        'horse'   => [Horse::class, 'Horse'],
        'trailer' => [Trailer::class, 'Trailer'],
        'vehicle' => [Vehicle::class, 'Vehicle'],
    ];

    public $companyId;
    public $companyIntegrationId;

    public $search = '';

    public $mappingUin;
    public $mappingLabel;
    public $entityType = 'vehicle';
    public $localId = '';

    public function mount()
    {
        $this->companyId = $this->currentCompanyId();

        $integration = $this->activePinpointIntegration($this->companyId);
        $this->companyIntegrationId = $integration ? $integration->id : null;
    }

    public function getPinpointEnabledProperty()
    {
        return IntegrationGate::enabledForUser('pinpoint');
    }

    public function getEntityOptionsProperty()
    {
        if (! isset(self::ENTITY_MODELS[$this->entityType])) {
            return collect();
        }

        [$modelClass] = self::ENTITY_MODELS[$this->entityType];

        return $modelClass::orderBy('fleet_number')->get();
    }

    /** Runs the same match PinpointVehicleMatcher::matchForCompany does from the console command, from a button click. */
    public function runAutoMatch()
    {
        if (! $this->companyIntegrationId) {
            $this->dispatchBrowserEvent('alert', [
                'type'    => 'error',
                'message' => 'Enable Pinpoint under Integrations for your company first.',
            ]);
            return;
        }

        $result = (new PinpointVehicleMatcher())->matchForCompany($this->companyId);

        if (! ($result['success'] ?? false)) {
            $this->dispatchBrowserEvent('alert', [
                'type'    => 'error',
                'message' => $result['error'] ?? 'Auto-match failed.',
            ]);
            return;
        }

        $matched = count($result['summary']['matched']);
        $unmatched = count($result['summary']['unmatched']);

        $this->dispatchBrowserEvent('alert', [
            'type'    => $matched > 0 ? 'success' : 'warning',
            'message' => "Auto-match by registration number: {$matched} matched, {$unmatched} unmatched. Unmatched trackers can still be linked manually below.",
        ]);
    }

    public function openMapModal($uin, $label)
    {
        $this->mappingUin = $uin;
        $this->mappingLabel = $label;
        $this->entityType = 'vehicle';
        $this->localId = '';
        $this->resetErrorBag();

        $this->dispatchBrowserEvent('show-mapTrackerModal');
    }

    public function saveMapping()
    {
        $this->validate([
            'entityType' => 'required|in:horse,trailer,vehicle',
            'localId'    => 'required',
        ]);

        if (! $this->companyIntegrationId) {
            $this->dispatchBrowserEvent('alert', [
                'type'    => 'error',
                'message' => 'Enable Pinpoint under Integrations for your company first.',
            ]);
            return;
        }

        [$modelClass, $label] = self::ENTITY_MODELS[$this->entityType];
        $model = $modelClass::findOrFail($this->localId);

        IntegrationMapping::updateOrCreate(
            [
                'company_integration_id' => $this->companyIntegrationId,
                'entity_type'            => $this->entityType . '_pinpoint',
                'local_id'               => $model->id,
            ],
            [
                'local_model'        => $modelClass,
                'local_reference'    => $model->fleet_number ?? $model->registration_number,
                'external_id'        => $this->mappingUin,
                'external_reference' => $this->mappingLabel,
                'sync_status'        => IntegrationMapping::STATUS_SYNCED,
                'last_synced_at'     => now(),
            ]
        );

        $this->dispatchBrowserEvent('hide-mapTrackerModal');
        $this->dispatchBrowserEvent('alert', [
            'type'    => 'success',
            'message' => 'Tracker ' . $this->mappingLabel . ' mapped to ' . $label . ' ' . ($model->fleet_number ?? $model->registration_number) . '.',
        ]);
    }

    public function unmap($mappingId)
    {
        IntegrationMapping::findOrFail($mappingId)->delete();

        $this->dispatchBrowserEvent('alert', [
            'type'    => 'success',
            'message' => 'Mapping removed.',
        ]);
    }

    public function render()
    {
        $integration = $this->activePinpointIntegration($this->companyId);

        $trackers = collect();
        $apiError = null;

        if ($integration) {
            $result = $this->cachedTrackerList($integration);

            if ($result['success'] ?? false) {
                $trackers = $this->trackerRows($result);

                if ($this->search !== '') {
                    $needle = strtolower($this->search);
                    $trackers = $trackers->filter(function ($t) use ($needle) {
                        return str_contains(strtolower($t['name'] ?? ''), $needle)
                            || str_contains(strtolower($t['plate'] ?? ''), $needle);
                    });
                }
            } else {
                $apiError = $result['error'] ?? 'Pinpoint request failed.';
            }
        }

        $mappings = $this->companyIntegrationId
            ? IntegrationMapping::where('company_integration_id', $this->companyIntegrationId)
                ->whereIn('entity_type', ['horse_pinpoint', 'trailer_pinpoint', 'vehicle_pinpoint'])
                ->get()
                ->keyBy(fn ($m) => (string) $m->external_id)
            : collect();

        return view('livewire.fleet.pinpoint-device-mappings', [
            'trackers' => $trackers,
            'mappings' => $mappings,
            'apiError' => $apiError,
        ]);
    }

    protected function currentCompanyId()
    {
        $user = Auth::user();

        return optional(optional($user)->employee)->company_id ?? optional($user)->company_id;
    }
}
