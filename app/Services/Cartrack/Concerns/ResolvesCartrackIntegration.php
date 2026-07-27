<?php

namespace App\Services\Cartrack\Concerns;

use App\Integrations\Cartrack\CartrackDriver as ConcreteCartrackDriver;
use App\Integrations\Contracts\CartrackDriver;
use App\Models\CompanyIntegration;
use App\Models\IntegrationProvider;
use Illuminate\Support\Facades\Cache;

/**
 * Shared resolution of the active Cartrack integration + driver for a company.
 * Mirrors App\Services\Sage\Concerns\ResolvesSageIntegration.
 */
trait ResolvesCartrackIntegration
{
    protected string $cartrackProviderKey = 'cartrack';

    /**
     * The active `cartrack` integration for a company, or null if none /
     * inactive / the provider isn't registered.
     */
    protected function activeCartrackIntegration(?int $companyId): ?CompanyIntegration
    {
        if (! $companyId) {
            return null;
        }

        $provider = IntegrationProvider::where('key', $this->cartrackProviderKey)->first();
        if (! $provider) {
            return null;
        }

        return CompanyIntegration::where('company_id', $companyId)
            ->where('integration_provider_id', $provider->id)
            ->where('status', 'active')
            ->first();
    }

    /**
     * Build the Cartrack driver for an integration (honours the provider's
     * `driver` FQN, falling back to the concrete driver if misconfigured).
     */
    protected function cartrackDriverFor(CompanyIntegration $integration): CartrackDriver
    {
        $class = $integration->integration_provider->driver ?? ConcreteCartrackDriver::class;

        if (! class_exists($class)) {
            $class = ConcreteCartrackDriver::class;
        }

        return new $class($integration);
    }

    /** Same company-resolution rule used for Sage: transporter's company, else the acting user's company. */
    protected function companyIdForFleetModel($model): ?int
    {
        $companyId = optional($model->transporter)->company_id;

        if (! $companyId) {
            $user = auth()->user();
            $companyId = optional(optional($user)->employee)->company_id ?? optional($user)->company_id;
        }

        return $companyId;
    }

    /**
     * Cached GET /aemp/iso15143-3/beta/fleet result, shared by every consumer
     * (live map + per-vehicle mileage lookups) so they don't each hit Cartrack
     * separately. The single-vehicle equipment/{id} endpoint proved unreliable
     * (500/404 depending on which id was tried) — the fleet-wide snapshot is
     * the one confirmed-working source for position/odometer, so pull from it
     * and filter client-side instead.
     */
    protected function cachedFleetSnapshot(CompanyIntegration $integration): array
    {
        return Cache::remember(
            "cartrack:fleet-snapshot:{$integration->id}",
            60,
            fn () => $this->cartrackDriverFor($integration)->getFleetSnapshot()
        );
    }

    /** Equipment rows from a cached fleet snapshot result, or an empty collection on failure. */
    protected function fleetEquipmentRows(array $fleetSnapshotResult): \Illuminate\Support\Collection
    {
        if (! ($fleetSnapshotResult['success'] ?? false)) {
            return collect();
        }

        $equipment = data_get($fleetSnapshotResult['data'], 'Equipment', []);

        return collect(is_array($equipment) ? $equipment : []);
    }
}
