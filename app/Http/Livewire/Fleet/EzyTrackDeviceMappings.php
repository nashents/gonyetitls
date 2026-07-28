<?php

namespace App\Http\Livewire\Fleet;

use App\Models\EzyTrackDevice;
use App\Models\Horse;
use App\Models\IntegrationMapping;
use App\Models\Trailer;
use App\Models\Vehicle;
use App\Services\EzyTrack\Concerns\ResolvesEzyTrackIntegration;
use App\Services\Integrations\IntegrationGate;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Lets a company link an EzyTrack (Digital Matter) tracking device — known
 * only by its serial number — to one of its own Horses/Trailers/Vehicles.
 * The link is stored as an integration_mappings row, same mechanism Cartrack
 * uses, just populated by hand here instead of an automatic registration
 * match (the device payload carries no registration number to match on).
 */
class EzyTrackDeviceMappings extends Component
{
    use ResolvesEzyTrackIntegration;

    protected const ENTITY_MODELS = [
        'horse'   => [Horse::class, 'Horse'],
        'trailer' => [Trailer::class, 'Trailer'],
        'vehicle' => [Vehicle::class, 'Vehicle'],
    ];

    public $companyId;
    public $companyIntegrationId;

    public $mappingDeviceId;
    public $mappingSerial;
    public $entityType = 'vehicle';
    public $localId = '';

    /** Reference info for whoever needs to hand EzyTrack the webhook details — see blade for display. */
    public $webhookUrl;
    public $webhookTokenMasked;

    public function mount()
    {
        $this->companyId = $this->currentCompanyId();

        $integration = $this->activeEzyTrackIntegration($this->companyId);
        $this->companyIntegrationId = $integration ? $integration->id : null;

        $this->webhookUrl = route('webhooks.ezytrack');
        $this->webhookTokenMasked = $this->maskToken(config('services.ezytrack.token'));
    }

    public function getEzyTrackEnabledProperty()
    {
        return IntegrationGate::enabledForUser('ezytrack');
    }

    /** Shows enough of the token to confirm it's configured without exposing the full secret on-screen. */
    protected function maskToken($token)
    {
        if (empty($token)) {
            return null;
        }

        $token = (string) $token;

        if (strlen($token) <= 12) {
            return str_repeat('*', strlen($token));
        }

        return substr($token, 0, 8) . str_repeat('*', 12) . substr($token, -4);
    }

    public function getEntityOptionsProperty()
    {
        if (! isset(self::ENTITY_MODELS[$this->entityType])) {
            return collect();
        }

        [$modelClass] = self::ENTITY_MODELS[$this->entityType];

        return $modelClass::orderBy('fleet_number')->get();
    }

    public function openMapModal($deviceId)
    {
        $device = EzyTrackDevice::findOrFail($deviceId);

        $this->mappingDeviceId = $device->id;
        $this->mappingSerial = $device->serial_number;
        $this->entityType = 'vehicle';
        $this->localId = '';
        $this->resetErrorBag();

        $this->dispatchBrowserEvent('show-mapDeviceModal');
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
                'message' => 'Enable EzyTrack under Integrations for your company first.',
            ]);
            return;
        }

        $device = EzyTrackDevice::findOrFail($this->mappingDeviceId);
        [$modelClass, $label] = self::ENTITY_MODELS[$this->entityType];
        $model = $modelClass::findOrFail($this->localId);

        IntegrationMapping::updateOrCreate(
            [
                'company_integration_id' => $this->companyIntegrationId,
                'entity_type'            => $this->entityType . '_ezytrack_device',
                'local_id'               => $model->id,
            ],
            [
                'local_model'        => $modelClass,
                'local_reference'    => $model->fleet_number ?? $model->registration_number,
                'external_id'        => $device->serial_number,
                'external_reference' => $device->serial_number,
                'sync_status'        => IntegrationMapping::STATUS_SYNCED,
                'last_synced_at'     => now(),
            ]
        );

        $this->dispatchBrowserEvent('hide-mapDeviceModal');
        $this->dispatchBrowserEvent('alert', [
            'type'    => 'success',
            'message' => 'Device ' . $device->serial_number . ' mapped to ' . $label . ' ' . ($model->fleet_number ?? $model->registration_number) . '.',
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
        $mappings = $this->companyIntegrationId
            ? IntegrationMapping::where('company_integration_id', $this->companyIntegrationId)
                ->whereIn('entity_type', $this->ezyTrackMappingEntityTypes())
                ->get()
                ->keyBy('external_reference')
            : collect();

        $devices = EzyTrackDevice::orderByDesc('last_seen_at')->get();

        return view('livewire.fleet.ezytrack-device-mappings', [
            'devices'  => $devices,
            'mappings' => $mappings,
        ]);
    }

    protected function currentCompanyId()
    {
        $user = Auth::user();

        return optional(optional($user)->employee)->company_id ?? optional($user)->company_id;
    }
}
