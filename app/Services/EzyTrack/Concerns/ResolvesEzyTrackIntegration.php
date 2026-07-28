<?php

namespace App\Services\EzyTrack\Concerns;

use App\Models\CompanyIntegration;
use App\Models\IntegrationProvider;

/**
 * Shared resolution of the active `ezytrack` CompanyIntegration for a company.
 * Mirrors App\Services\Cartrack\Concerns\ResolvesCartrackIntegration, minus
 * the outbound-driver helpers Cartrack needs (EzyTrack has nothing to call —
 * it pushes to us).
 */
trait ResolvesEzyTrackIntegration
{
    protected $ezytrackProviderKey = 'ezytrack';

    /** entity_type values integration_mappings uses to link a fleet unit to an EzyTrack device. */
    protected function ezyTrackMappingEntityTypes()
    {
        return ['horse_ezytrack_device', 'trailer_ezytrack_device', 'vehicle_ezytrack_device'];
    }

    protected function activeEzyTrackIntegration($companyId)
    {
        if (! $companyId) {
            return null;
        }

        $provider = IntegrationProvider::where('key', $this->ezytrackProviderKey)->first();
        if (! $provider) {
            return null;
        }

        return CompanyIntegration::where('company_id', $companyId)
            ->where('integration_provider_id', $provider->id)
            ->where('status', 'active')
            ->first();
    }
}
