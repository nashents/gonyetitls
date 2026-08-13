<?php

namespace App\Http\Livewire\Fleet;

use App\Models\Horse;
use App\Models\IntegrationMapping;
use App\Models\Trailer;
use App\Models\Vehicle;
use App\Services\FanTracker\Concerns\ResolvesFanTrackerIntegration;
use App\Services\FanTracker\FanTrackerVehicleMatcher;
use App\Services\Integrations\IntegrationGate;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * UI equivalent of `php artisan fantracker:match-vehicles`, so nobody needs
 * console access to link a company's fleet. Lists every tracker on the
 * FanTracker account (live, via tracker/list) next to its current mapping
 * (if any), offers a one-click "Run Auto-Match" (registration_number vs
 * tracker label — see FanTrackerVehicleMatcher), and — since that auto-match
 * only works once trackers are labelled with the vehicle's registration
 * number — a manual "Map" action per tracker, same mechanism EzyTrack's
 * device mapping screen uses.
 */
class FanTrackerDeviceMappings extends Component
{
    use ResolvesFanTrackerIntegration;

    protected const ENTITY_MODELS = [
        'horse'   => [Horse::class, 'Horse'],
        'trailer' => [Trailer::class, 'Trailer'],
        'vehicle' => [Vehicle::class, 'Vehicle'],
    ];

    public $companyId;
    public $companyIntegrationId;

    public $search = '';

    public $mappingTrackerId;
    public $mappingLabel;
    public $entityType = 'vehicle';
    public $localId = '';

    public function mount()
    {
        $this->companyId = $this->currentCompanyId();

        $integration = $this->activeFanTrackerIntegration($this->companyId);
        $this->companyIntegrationId = $integration ? $integration->id : null;
    }

    public function getFanTrackerEnabledProperty()
    {
        return IntegrationGate::enabledForUser('fantracker');
    }

    public function getEntityOptionsProperty()
    {
        if (! isset(self::ENTITY_MODELS[$this->entityType])) {
            return collect();
        }

        [$modelClass] = self::ENTITY_MODELS[$this->entityType];

        return $modelClass::orderBy('fleet_number')->get();
    }

    /** Runs the same match FanTrackerVehicleMatcher::matchForCompany does from the console command, from a button click. */
    public function runAutoMatch()
    {
        if (! $this->companyIntegrationId) {
            $this->dispatchBrowserEvent('alert', [
                'type'    => 'error',
                'message' => 'Enable FanTracker under Integrations for your company first.',
            ]);
            return;
        }

        $result = (new FanTrackerVehicleMatcher())->matchForCompany($this->companyId);

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

    public function openMapModal($trackerId, $label)
    {
        $this->mappingTrackerId = $trackerId;
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
                'message' => 'Enable FanTracker under Integrations for your company first.',
            ]);
            return;
        }

        [$modelClass, $label] = self::ENTITY_MODELS[$this->entityType];
        $model = $modelClass::findOrFail($this->localId);

        IntegrationMapping::updateOrCreate(
            [
                'company_integration_id' => $this->companyIntegrationId,
                'entity_type'            => $this->entityType . '_tracker',
                'local_id'               => $model->id,
            ],
            [
                'local_model'        => $modelClass,
                'local_reference'    => $model->fleet_number ?? $model->registration_number,
                'external_id'        => $this->mappingTrackerId,
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
        $integration = $this->activeFanTrackerIntegration($this->companyId);

        $trackers = collect();
        $apiError = null;

        if ($integration) {
            $result = $this->cachedTrackerList($integration);

            if ($result['success'] ?? false) {
                $trackers = $this->trackerRows($result);

                if ($this->search !== '') {
                    $needle = strtolower($this->search);
                    $trackers = $trackers->filter(fn ($t) => str_contains(strtolower($t['label'] ?? ''), $needle));
                }
            } else {
                $apiError = $result['error'] ?? 'FanTracker request failed.';
            }
        }

        $mappings = $this->companyIntegrationId
            ? IntegrationMapping::where('company_integration_id', $this->companyIntegrationId)
                ->whereIn('entity_type', ['horse_tracker', 'trailer_tracker', 'vehicle_tracker'])
                ->get()
                ->keyBy(fn ($m) => (string) $m->external_id)
            : collect();

        return view('livewire.fleet.fantracker-device-mappings', [
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
