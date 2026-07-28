<?php

namespace App\Http\Livewire\CompanyIntegrations;

use Livewire\Component;
use App\Models\CompanyIntegration;
use App\Models\IntegrationProvider;
use App\Models\IntegrationLog;
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
    public $default_config = [];

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

        $this->default_config = $provider && is_array($provider->default_config)
            ? $provider->default_config
            : [];

        // Build credentials array from required fields, preserving existing values on edit
        $credentials = [];
        foreach ($this->required_credentials as $field) {
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

            $integration->update([
                'last_tested_at'  => now(),
                'status'          => $ok ? 'active' : 'error',
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
                'message' => $ok ? 'Connection test succeeded!' : ('Connection test failed: ' . $error),
            ]);
        } catch (\Exception $e) {
            $this->dispatchBrowserEvent('alert', [
                'type'    => 'error',
                'message' => 'Connection test failed: ' . $e->getMessage(),
            ]);
        }
    }

    public function render()
    {
        return view('livewire.company-integrations.index', [
            'company_integrations' => CompanyIntegration::with('integration_provider')
                ->where('company_id', $this->company_id)
                ->orderBy('created_at', 'desc')
                ->get(),
        ]);
    }
}