<?php

namespace App\Services\Pinpoint\Concerns;

use App\Integrations\Pinpoint\PinpointDriver as ConcretePinpointDriver;
use App\Integrations\Contracts\PinpointDriver;
use App\Models\CompanyIntegration;
use App\Models\IntegrationProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

/**
 * Shared resolution of the active Pinpoint integration + driver for a
 * company. Mirrors App\Services\Cartrack\Concerns\ResolvesCartrackIntegration.
 */
trait ResolvesPinpointIntegration
{
    protected string $pinpointProviderKey = 'pinpoint';

    /** The active `pinpoint` integration for a company, or null if none / inactive / unregistered. */
    protected function activePinpointIntegration(?int $companyId): ?CompanyIntegration
    {
        if (! $companyId) {
            return null;
        }

        $provider = IntegrationProvider::where('key', $this->pinpointProviderKey)->first();
        if (! $provider) {
            return null;
        }

        return CompanyIntegration::where('company_id', $companyId)
            ->where('integration_provider_id', $provider->id)
            ->where('status', 'active')
            ->first();
    }

    /** Build the Pinpoint driver for an integration (honours the provider's `driver` FQN, falling back to the concrete driver if misconfigured). */
    protected function pinpointDriverFor(CompanyIntegration $integration): PinpointDriver
    {
        $class = $integration->integration_provider->driver ?? ConcretePinpointDriver::class;

        if (! class_exists($class)) {
            $class = ConcretePinpointDriver::class;
        }

        return new $class($integration);
    }

    /** Same company-resolution rule used for Cartrack/FanTracker/Sage: transporter's company, else the acting user's company. */
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
     * Cached GET /api2/trackers result (5 min — labels/plates barely change),
     * shared by matching + the live map.
     *
     * Named distinctly from FanTracker's identical-purpose helper (rather
     * than relying on `insteadof` where both traits are combined, e.g.
     * LiveMap) — that trait's `cachedTrackerList` internally calls
     * `fanTrackerDriverFor()`, so letting it "win" a name collision silently
     * rebinds this driver-specific call chain to the wrong provider. Confirmed
     * live 2026-09-01: exactly this caused a PinpointDriver to be built where
     * a FanTrackerDriver was expected, throwing a TypeError.
     */
    protected function cachedPinpointTrackerList(CompanyIntegration $integration): array
    {
        return Cache::remember(
            "pinpoint:trackers:{$integration->id}",
            300,
            fn () => $this->pinpointDriverFor($integration)->listTrackers()
        );
    }

    /** Tracker rows from a cached tracker-list result, or an empty collection on failure. */
    protected function pinpointTrackerRows(array $trackerListResult): Collection
    {
        if (! ($trackerListResult['success'] ?? false)) {
            return collect();
        }

        return collect(is_array($trackerListResult['data']) ? $trackerListResult['data'] : []);
    }

    /**
     * Cached GET /api2/last?user=<owner> result (60s, mirrors Cartrack's
     * fleet-snapshot cadence), shared by every consumer. The owner userid is
     * read off the (separately cached) tracker list's `belong` field — every
     * tracker on a normal account shares the same owner, and user=<owner> is
     * what a non-admin token is actually scoped to query (see PinpointDriver).
     */
    protected function cachedFleetLastPositions(CompanyIntegration $integration): array
    {
        return Cache::remember(
            "pinpoint:fleet-last:{$integration->id}",
            60,
            function () use ($integration) {
                $owner = $this->pinpointTrackerRows($this->cachedPinpointTrackerList($integration))
                    ->pluck('belong')
                    ->filter()
                    ->first();

                if (! $owner) {
                    return ['success' => false, 'status' => null, 'data' => null, 'error' => 'Could not determine the Pinpoint account owner (no trackers with a `belong` value found).'];
                }

                return $this->pinpointDriverFor($integration)->getFleetLastPositions($owner);
            }
        );
    }
}
