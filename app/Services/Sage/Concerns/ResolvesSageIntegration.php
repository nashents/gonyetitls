<?php

namespace App\Services\Sage\Concerns;

use App\Integrations\Contracts\SageDriver;
use App\Integrations\SageIntacct\SageIntacctDriver;
use App\Models\CompanyIntegration;
use App\Models\IntegrationProvider;

/**
 * Shared resolution of the active Sage integration + driver for a company.
 * Used by both the Phase-1 SageIntacctService (customers/vendors) and the
 * Phase-2 fleet/trip services so the logic lives in exactly one place.
 */
trait ResolvesSageIntegration
{
    protected string $sageProviderKey = 'sage_intacct';

    /**
     * The active `sage_intacct` integration for a company, or null if none /
     * inactive / the provider isn't registered.
     */
    protected function activeSageIntegration(?int $companyId): ?CompanyIntegration
    {
        if (! $companyId) {
            return null;
        }

        $provider = IntegrationProvider::where('key', $this->sageProviderKey)->first();
        if (! $provider) {
            return null;
        }

        return CompanyIntegration::where('company_id', $companyId)
            ->where('integration_provider_id', $provider->id)
            ->where('status', 'active')
            ->first();
    }

    /**
     * Build the Sage driver for an integration (honours the provider's `driver`
     * FQN, falling back to the façade so a misconfigured row still works).
     */
    protected function sageDriverFor(CompanyIntegration $integration): SageDriver
    {
        $class = $integration->integration_provider->driver ?? SageIntacctDriver::class;

        if (! class_exists($class)) {
            $class = SageIntacctDriver::class;
        }

        return new $class($integration);
    }
}
