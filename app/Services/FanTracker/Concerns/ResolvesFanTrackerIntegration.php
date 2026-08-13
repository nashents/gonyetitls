<?php

namespace App\Services\FanTracker\Concerns;

use App\Integrations\FanTracker\FanTrackerDriver as ConcreteFanTrackerDriver;
use App\Integrations\Contracts\FanTrackerDriver;
use App\Models\CompanyIntegration;
use App\Models\IntegrationProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

/**
 * Shared resolution of the active FanTracker integration + driver for a
 * company. Mirrors App\Services\Cartrack\Concerns\ResolvesCartrackIntegration.
 */
trait ResolvesFanTrackerIntegration
{
    protected string $fanTrackerProviderKey = 'fantracker';

    /** The active `fantracker` integration for a company, or null if none / inactive / unregistered. */
    protected function activeFanTrackerIntegration(?int $companyId): ?CompanyIntegration
    {
        if (! $companyId) {
            return null;
        }

        $provider = IntegrationProvider::where('key', $this->fanTrackerProviderKey)->first();
        if (! $provider) {
            return null;
        }

        return CompanyIntegration::where('company_id', $companyId)
            ->where('integration_provider_id', $provider->id)
            ->where('status', 'active')
            ->first();
    }

    /** Build the FanTracker driver for an integration (honours the provider's `driver` FQN, falling back to the concrete driver if misconfigured). */
    protected function fanTrackerDriverFor(CompanyIntegration $integration): FanTrackerDriver
    {
        $class = $integration->integration_provider->driver ?? ConcreteFanTrackerDriver::class;

        if (! class_exists($class)) {
            $class = ConcreteFanTrackerDriver::class;
        }

        return new $class($integration);
    }

    /** Same company-resolution rule used for Cartrack/Sage: transporter's company, else the acting user's company. */
    protected function companyIdForFleetModel($model): ?int
    {
        $companyId = optional($model->transporter)->company_id;

        if (! $companyId) {
            $user = auth()->user();
            $companyId = optional(optional($user)->employee)->company_id ?? optional($user)->company_id;
        }

        return $companyId;
    }

    /** Cached GET /tracker/list result (5 min — labels/ids barely change), shared by matching + the live map. */
    protected function cachedTrackerList(CompanyIntegration $integration): array
    {
        return Cache::remember(
            "fantracker:trackers:{$integration->id}",
            300,
            fn () => $this->fanTrackerDriverFor($integration)->listTrackers()
        );
    }

    /** Tracker rows from a cached tracker-list result, or an empty collection on failure. */
    protected function trackerRows(array $trackerListResult): Collection
    {
        if (! ($trackerListResult['success'] ?? false)) {
            return collect();
        }

        $list = data_get($trackerListResult['data'], 'list', []);

        return collect(is_array($list) ? $list : []);
    }

    /**
     * Cached GET /tracker/get_states for every tracker on the account (30s —
     * matches the live map's poll cadence), shared by the live map. Position
     * only — mileage/odometer is a separate per-tracker call, see
     * FanTrackerSyncService::currentSnapshot.
     */
    protected function cachedFleetStates(CompanyIntegration $integration): array
    {
        $trackerIds = $this->trackerRows($this->cachedTrackerList($integration))->pluck('id')->all();

        if (empty($trackerIds)) {
            return ['success' => true, 'status' => 200, 'data' => ['states' => []], 'error' => null];
        }

        return Cache::remember(
            "fantracker:states:{$integration->id}",
            30,
            fn () => $this->fanTrackerDriverFor($integration)->getStates($trackerIds)
        );
    }
}
