<?php

namespace App\Http\Livewire\CompanyIntegrations;

use Livewire\Component;
use App\Models\CompanyIntegration;
use App\Models\IntegrationProvider;
use App\Models\IntegrationLog;
use App\Integrations\Contracts\FiscalDriver;
use App\Integrations\FiscalHarmony\FiscalHarmonyDriver;
use App\Services\Fiscal\FiscalHarmonyReadinessService;
use App\Services\Fiscal\FiscalProviderResolver;
use Illuminate\Support\Facades\DB;

class Index extends Component
{
    public $integration_providers;

    public $company_id;
    public $company_integration_id;

    public $integration_provider_id;
    public $status = 'inactive';
    public $credentials = [];   // keyed by credential field name
    public $config = [];        // keyed by config field name

    // Fields derived from the selected provider
    public $required_credentials = [];
    public $optional_credentials = [];   // shown, but may be left blank
    public $default_config = [];

    // Fiscal Harmony readiness check results (shown in #fiscalReadinessModal)
    public $readiness = null;
    public $readiness_integration_id;
    public $readiness_provider;

    protected function rules()
    {
        $rules = [
            'integration_provider_id' => 'required|exists:integration_providers,id',
            'status'                  => 'required|in:inactive,active,error,suspended',
        ];

        foreach ($this->required_credentials as $field) {
            $rules['credentials.' . $field] = 'required';
        }

        return $rules;
    }

    public function mount($id = null)
    {
        // The standalone Integrations page renders this component without an id,
        // so fall back to the logged-in user's company. company_id is required
        // (NOT NULL + FK), and a null here is the usual cause of a failed save.
        $user = auth()->user();

        $this->company_id = $id
            ?? optional(optional($user)->employee)->company_id
            ?? optional($user)->company_id
            ?? optional(optional($user)->company)->id;

        $this->integration_providers = IntegrationProvider::where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();
    }

    public function updatedIntegrationProviderId($value)
    {
        $this->loadProviderSchema($value);
    }

    private function loadProviderSchema($providerId, array $existingCredentials = [], array $existingConfig = [])
    {
        $provider = $this->integration_providers->firstWhere('id', $providerId)
            ?? IntegrationProvider::find($providerId);

        $this->required_credentials = $provider && is_array($provider->required_credentials)
            ? $provider->required_credentials
            : [];

        $this->optional_credentials = $provider && is_array($provider->optional_credentials)
            ? array_values(array_diff($provider->optional_credentials, $this->required_credentials))
            : [];

        $this->default_config = $provider && is_array($provider->default_config)
            ? $provider->default_config
            : [];

        // Build credentials array from required fields, preserving existing values on edit
        $credentials = [];
        foreach (array_merge($this->required_credentials, $this->optional_credentials) as $field) {
            $credentials[$field] = $existingCredentials[$field] ?? '';
        }
        $this->credentials = $credentials;

        // Merge defaults with any saved config
        $this->config = array_merge($this->default_config, $existingConfig);
    }

    private function resetInputFields()
    {
        $this->company_integration_id = null;
        $this->integration_provider_id = null;
        $this->status = 'inactive';
        $this->credentials = [];
        $this->config = [];
        $this->required_credentials = [];
        $this->optional_credentials = [];
        $this->default_config = [];
        $this->resetErrorBag();
    }

    public function create()
    {
        $this->resetInputFields();
        $this->dispatchBrowserEvent('show-createModal');
    }

    public function store()
    {
        $this->validate();

        try {
            // withTrashed(): the (company_id, integration_provider_id) unique index
            // doesn't know about soft deletes, so a previously-deleted row for this
            // provider still blocks a plain insert with a duplicate-key error. Treat
            // a soft-deleted match as "restore & update" instead of "already exists".
            $existing = CompanyIntegration::withTrashed()
                ->where('company_id', $this->company_id)
                ->where('integration_provider_id', $this->integration_provider_id)
                ->first();

            if ($existing && ! $existing->trashed()) {
                $this->dispatchBrowserEvent('alert', [
                    'type'    => 'error',
                    'message' => 'This provider is already integrated for your company!',
                ]);
                return;
            }

            // Guard: company_id is NOT NULL with a FK to companies — a null here
            // (e.g. page opened without a company context) is the usual failure.
            if (empty($this->company_id)) {
                $this->dispatchBrowserEvent('alert', [
                    'type'    => 'error',
                    'message' => 'No company context — reload the page from your company profile and try again.',
                ]);
                return;
            }

            if ($this->status === 'active' && ($clash = $this->otherActiveFiscal($this->integration_provider_id))) {
                $this->dispatchBrowserEvent('alert', [
                    'type'    => 'error',
                    'message' => "{$clash} is already the active fiscal provider. Deactivate it first — only one fiscal provider can be active.",
                ]);
                return;
            }

            $attributes = [
                'status'      => $this->status,
                'credentials' => array_filter($this->credentials, fn ($v) => $v !== '' && $v !== null),
                'config'      => $this->config,
            ];

            if ($existing) {
                $existing->restore();
                $existing->update($attributes);
            } else {
                CompanyIntegration::create($attributes + [
                    'company_id'              => $this->company_id,
                    'integration_provider_id' => $this->integration_provider_id,
                ]);
            }

            // The create modal's id is `createModal`; the global handler hides it
            // on `hide-createModal` (it was dispatching a non-existent modal id).
            $this->dispatchBrowserEvent('hide-createModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert', [
                'type'    => 'success',
                'message' => 'Integration created successfully!',
            ]);
        } catch (\Exception $e) {
            // Log the real cause so failures aren't silently swallowed.
            \Illuminate\Support\Facades\Log::error('CompanyIntegration create failed: ' . $e->getMessage());

            $this->dispatchBrowserEvent('alert', [
                'type'    => 'error',
                'message' => 'Something went wrong while creating the integration: ' . $e->getMessage(),
            ]);
        }
    }

    public function edit($id)
    {
        $integration = CompanyIntegration::findOrFail($id);

        $this->company_integration_id = $integration->id;
        $this->integration_provider_id = $integration->integration_provider_id;
        $this->status = $integration->status;

        $this->loadProviderSchema(
            $integration->integration_provider_id,
            $integration->credentials ?? [],
            $integration->config ?? []
        );

        $this->resetErrorBag();
        $this->dispatchBrowserEvent('show-company_integrationEditModal');
    }

    public function update()
    {
        $this->validate();

        $integration = CompanyIntegration::findOrFail($this->company_integration_id);

        if ($this->status === 'active' && ($clash = $this->otherActiveFiscal($integration->integration_provider_id, $integration->id))) {
            $this->dispatchBrowserEvent('alert', [
                'type'    => 'error',
                'message' => "{$clash} is already the active fiscal provider. Deactivate it first — only one fiscal provider can be active.",
            ]);
            return;
        }

        $integration->update([
            'status'      => $this->status,
            'credentials' => array_filter($this->credentials, fn ($v) => $v !== '' && $v !== null),
            'config'      => $this->config,
        ]);

        $this->dispatchBrowserEvent('hide-company_integrationEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert', [
            'type'    => 'success',
            'message' => 'Integration updated successfully!',
        ]);
    }

    public function delete($id)
    {
        CompanyIntegration::findOrFail($id)->delete();

        $this->dispatchBrowserEvent('alert', [
            'type'    => 'success',
            'message' => 'Integration deleted successfully!',
        ]);
    }

    public function testConnection($id)
    {
        $integration = CompanyIntegration::with('integration_provider')->findOrFail($id);

        try {
            // Resolve the provider's driver class (e.g. SageIntacctDriver),
            // build it from this integration, and run its testConnection().
            $driverClass = $integration->integration_provider->driver ?? null;

            if (! $driverClass || ! class_exists($driverClass)) {
                throw new \RuntimeException('No driver is configured for this provider yet.');
            }

            $driver = new $driverClass($integration);
            $result = $driver->testConnection();

            $ok    = ! empty($result['success']);
            $error = $ok ? null : ($result['error'] ?? 'Connection test failed');

            // A passing test activates the integration — unless another fiscal
            // provider is already active (a company fiscalises through one only).
            $clash  = $ok ? $this->otherActiveFiscal($integration->integration_provider_id, $integration->id) : null;
            $status = $ok ? ($clash ? $integration->status : 'active') : 'error';

            $integration->update([
                'last_tested_at'  => now(),
                'status'          => $status,
                'last_success_at' => $ok ? now() : $integration->last_success_at,
                'last_error'      => $error,
            ]);

            IntegrationLog::create([
                'company_integration_id' => $integration->id,
                'direction'              => 'outbound',
                'action'                 => 'test',
                'status'                 => $ok ? 'ok' : 'fail',
                'message'                => $ok ? 'Connection test succeeded' : $error,
            ]);

            $this->dispatchBrowserEvent('alert', [
                'type'    => $ok ? 'success' : 'error',
                'message' => $ok
                    ? 'Connection test succeeded!' . (! empty($result['data']['summary']) ? ' ' . $result['data']['summary'] : '')
                        . ($clash ? " Not activated: {$clash} is the active fiscal provider." : '')
                    : ('Connection test failed: ' . $error),
            ]);
        } catch (\Exception $e) {
            $this->dispatchBrowserEvent('alert', [
                'type'    => 'error',
                'message' => 'Connection test failed: ' . $e->getMessage(),
            ]);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // FISCAL HARMONY READINESS (same report as `php artisan fiscalharmony:check`)
    // ─────────────────────────────────────────────────────────────

    /** Run the readiness check; $mapTaxes also creates missing FH tax mappings. */
    public function fiscalReadiness($id, $mapTaxes = false)
    {
        $integration = CompanyIntegration::with('integration_provider')
            ->where('company_id', $this->company_id)
            ->findOrFail($id);

        $this->readiness_integration_id = $integration->id;
        $this->readiness_provider       = $integration->integration_provider->name ?? 'Fiscal Harmony';

        try {
            if (($integration->integration_provider->key ?? null) !== 'fiscal_harmony') {
                throw new \RuntimeException('The readiness check is only available for Fiscal Harmony.');
            }

            $api    = (new FiscalHarmonyDriver($integration))->api();
            $report = app(FiscalHarmonyReadinessService::class)->run($api, (bool) $mapTaxes);
        } catch (\Throwable $e) {
            $report = ['fatal' => $e->getMessage(), 'checks' => [], 'ready' => false, 'problems' => 1];
        }

        $this->readiness = $report;

        IntegrationLog::create([
            'company_integration_id' => $integration->id,
            'direction'              => 'outbound',
            'action'                 => $mapTaxes ? 'readiness_map_taxes' : 'readiness_check',
            'status'                 => ! empty($report['ready']) ? 'ok' : 'fail',
            'message'                => $report['fatal']
                ?? (! empty($report['ready']) ? 'Ready to fiscalise' : ($report['problems'] . ' issue(s) to resolve')),
        ]);

        $this->dispatchBrowserEvent('show-fiscalReadinessModal');
    }

    // ─────────────────────────────────────────────────────────────
    // FISCAL DEVICE (fiscal-type integrations)
    // ─────────────────────────────────────────────────────────────

    public function fiscalDeviceStatus($id)
    {
        $this->runFiscalAction($id, 'status', fn (FiscalDriver $d) => $d->deviceStatus());
    }

    public function openFiscalDay($id)
    {
        $this->runFiscalAction($id, 'open_fiscal_day', fn (FiscalDriver $d) => $d->openFiscalDay());
    }

    public function closeFiscalDay($id)
    {
        $this->runFiscalAction($id, 'close_fiscal_day', fn (FiscalDriver $d) => $d->closeFiscalDay());
    }

    protected function runFiscalAction($id, string $action, callable $call): void
    {
        $integration = CompanyIntegration::with('integration_provider')
            ->where('company_id', $this->company_id)
            ->findOrFail($id);

        try {
            $result = $call(app(FiscalProviderResolver::class)->driver($integration));
        } catch (\Throwable $e) {
            $result = ['success' => false, 'error' => $e->getMessage()];
        }

        $ok      = ! empty($result['success']);
        $message = $ok ? ($result['data']['summary'] ?? 'Done.') : ($result['error'] ?? 'The fiscal device did not respond.');

        IntegrationLog::create([
            'company_integration_id' => $integration->id,
            'direction'              => 'outbound',
            'action'                 => $action,
            'status'                 => $ok ? 'ok' : 'fail',
            'message'                => \Illuminate\Support\Str::limit($message, 1000),
        ]);

        $this->dispatchBrowserEvent('alert', [
            'type'    => $ok ? 'success' : 'error',
            'message' => $message,
        ]);
    }

    /** Name of another ACTIVE fiscal integration for this company, if $providerId is a fiscal provider. */
    protected function otherActiveFiscal($providerId, $exceptId = null): ?string
    {
        $provider = IntegrationProvider::find($providerId);
        if (! $provider || $provider->type !== FiscalProviderResolver::TYPE) {
            return null;
        }

        return CompanyIntegration::with('integration_provider')
            ->where('company_id', $this->company_id)
            ->where('status', 'active')
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->whereHas('integration_provider', fn ($q) => $q->where('type', FiscalProviderResolver::TYPE))
            ->first()?->integration_provider?->name;
    }

    /** Fiscal rows whose driver lets us open/close the fiscal day (Revmax, not Fiscal Harmony). */
    protected function fiscalDayIntegrationIds($integrations): array
    {
        return $integrations
            ->filter(function ($integration) {
                $class = $integration->integration_provider->driver ?? null;
                if (($integration->integration_provider->type ?? null) !== FiscalProviderResolver::TYPE
                    || ! $class || ! class_exists($class) || ! is_subclass_of($class, FiscalDriver::class)) {
                    return false;
                }

                return (new $class($integration))->managesFiscalDay();
            })
            ->pluck('id')
            ->all();
    }

    public function render()
    {
        $companyIntegrations = CompanyIntegration::with('integration_provider')
            ->where('company_id', $this->company_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.company-integrations.index', [
            'company_integrations'       => $companyIntegrations,
            'fiscal_day_integration_ids' => $this->fiscalDayIntegrationIds($companyIntegrations),
        ]);
    }
}