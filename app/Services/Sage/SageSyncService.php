<?php

namespace App\Services\Sage;

use App\Models\CompanyIntegration;
use App\Models\Horse;
use App\Models\IntegrationLog;
use App\Models\Trailer;
use App\Models\Trip;
use App\Services\Sage\Concerns\ResolvesSageIntegration;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Public entry point for fleet/trip → Sage sync (Classes & Projects).
 *
 * Called inline by the Livewire components for single records and by the queue
 * jobs for bulk. Resolves the company's active integration + driver, delegates
 * to SageClassService / SageProjectService, then writes an integration_logs
 * audit row. Company scope: trips use trip.company_id; horses/trailers use
 * their transporter's company, falling back to the acting user's company.
 */
class SageSyncService
{
    use ResolvesSageIntegration;

    public function syncHorse(Horse $horse): array
    {
        return $this->run($horse, 'horse');
    }

    public function syncTrailer(Trailer $trailer): array
    {
        return $this->run($trailer, 'trailer');
    }

    public function syncTrip(Trip $trip): array
    {
        return $this->run($trip, 'trip');
    }

    /** Retry a previously-attempted record (idempotent — de-dup handles it). */
    public function retry(Model $model): array
    {
        if ($model instanceof Trip) {
            return $this->syncTrip($model);
        }
        if ($model instanceof Trailer) {
            return $this->syncTrailer($model);
        }
        return $this->syncHorse($model);
    }

    // ─────────────────────────────────────────────────────────────
    // CORE
    // ─────────────────────────────────────────────────────────────

    protected function run(Model $model, string $type): array
    {
        $integration = $this->activeSageIntegration($this->companyIdFor($model, $type));

        if (! $integration) {
            return ['success' => false, 'skipped' => true, 'error' => 'Sage Intacct integration is not active for this company.'];
        }

        try {
            $driver          = $this->sageDriverFor($integration);
            $classService    = new SageClassService($driver, $integration);
            $projectService  = new SageProjectService($driver, $integration, $classService);

            if ($type === 'trip') {
                $result = $projectService->syncTrip($model);
            } elseif ($type === 'trailer') {
                $result = $classService->syncTrailer($model);   // stays a green CLASS
            } else {
                // Horse → Transporter project + Horse project + Horse class (orange).
                $result = $projectService->ensureHorse($model);
            }
        } catch (Throwable $e) {
            Log::error("Sage sync {$type} #{$model->getKey()} threw: " . $e->getMessage());
            $result = [
                'success' => false, 'status' => 'failed', 'action' => 'sync', 'entity' => $type,
                'model' => $model, 'external_id' => null, 'request_reference' => null,
                'response_status' => null, 'error' => 'Internal error during Sage sync.',
            ];
        }

        $this->audit($integration, $result);

        return $result;
    }

    /** Company that owns the record → which Sage integration to push to. */
    protected function companyIdFor(Model $model, string $type): ?int
    {
        if ($type === 'trip') {
            return $model->company_id;
        }

        // Horses/trailers have no company_id: use the transporter's company,
        // else the acting user's company.
        $companyId = optional($model->transporter)->company_id;

        if (! $companyId) {
            $user = auth()->user();
            $companyId = optional(optional($user)->employee)->company_id ?? optional($user)->company_id;
        }

        return $companyId;
    }

    /**
     * Write one audit row (no secrets) + update the integration's sync markers.
     * A failing audit never undoes the Sage call or the mapping already saved.
     */
    protected function audit(CompanyIntegration $integration, array $r): void
    {
        $model = $r['model'] ?? null;

        IntegrationLog::create([
            'company_integration_id' => $integration->id,
            'direction'              => 'outbound',
            'action'                 => $r['action'] ?? 'sync',
            'status'                 => ! empty($r['success']) ? 'ok' : 'fail',
            'message'                => $r['error'] ?? null,
            'meta'                   => [
                'entity'            => $r['entity'] ?? null,
                'model_type'        => $model ? get_class($model) : null,
                'model_id'          => $model ? $model->getKey() : null,
                'request_reference' => $r['request_reference'] ?? null,
                'sync_status'       => $r['status'] ?? null,
                'response_status'   => $r['response_status'] ?? null,
                'synced_at'         => now()->toIso8601String(),
            ],
        ]);

        $integration->forceFill(
            ! empty($r['success'])
                ? ['last_sync_at' => now(), 'last_success_at' => now(), 'last_error' => null]
                : ['last_error' => $r['error'] ?? null]
        )->save();
    }
}
