<?php

namespace App\Services\Sage;

use App\Integrations\Contracts\SageDriver;
use App\Models\CompanyIntegration;
use App\Models\Horse;
use App\Models\IntegrationMapping;
use App\Models\Transporter;
use App\Models\Trip;
use App\Services\Sage\Concerns\ManagesMappings;
use App\Services\Sage\Mappers\SageHorseMapper;
use App\Services\Sage\Mappers\SageTransporterMapper;
use App\Services\Sage\Mappers\SageTripMapper;
use Illuminate\Database\Eloquent\Model;

/**
 * Sage PROJECT sync. The project hierarchy is:
 *   Transporter (SUBCONTRACTOR) → Horse (SUB - TRUCKS) → Trip (TRIPS)
 * A Trip also references the Horse CLASS (orange) via CLASSID.
 */
class SageProjectService
{
    use ManagesMappings;

    protected SageDriver $driver;
    protected CompanyIntegration $integration;
    protected SageClassService $classService;

    public function __construct(SageDriver $driver, CompanyIntegration $integration, SageClassService $classService)
    {
        $this->driver       = $driver;
        $this->integration  = $integration;
        $this->classService = $classService;
    }

    /**
     * Ensure the Transporter PROJECT exists (top-level, SUBCONTRACTOR).
     * Returns ['success'=>bool,'external_id'=>?string].
     */
    public function ensureTransporterProject(?Transporter $transporter): array
    {
        if (! $transporter) {
            return ['success' => true, 'external_id' => null]; // horse project becomes top-level
        }

        $mapping = $this->mappingFor($this->integration, 'transporter_project', $transporter);
        if ($mapping->exists && $mapping->external_id) {
            return ['success' => true, 'external_id' => $mapping->external_id];
        }

        return $this->syncProject(
            'transporter_project',
            $transporter,
            SageTransporterMapper::projectId($transporter),
            $this->applyOverrides(SageTransporterMapper::mapProject($transporter))
        );
    }

    /**
     * Ensure a Horse is fully in Sage: Transporter project → Horse project
     * (child) → Horse class (orange). Returns project_id + class_id for trips.
     */
    public function ensureHorse(Horse $horse): array
    {
        $transporter = $this->ensureTransporterProject($horse->transporter);

        $horsePayload = $this->applyOverrides(
            SageHorseMapper::mapProject($horse, (string) ($transporter['external_id'] ?? ''))
        );
        if (empty($transporter['external_id'])) {
            unset($horsePayload['parentid']);
        }

        $project = $this->syncProject('horse_project', $horse, SageHorseMapper::projectId($horse), $horsePayload);
        $class   = $this->classService->syncHorseClass($horse);

        $success = ! empty($project['success']) && ! empty($class['success']);
        $error   = $project['error'] ?? $class['error'] ?? null;

        // Keep the badge (horse_project) honest if the class step failed.
        if (! $success && ! empty($project['success']) && ! empty($class['error'])) {
            $this->mappingFor($this->integration, 'horse_project', $horse)->markFailed($class['error']);
        }

        return [
            'success'           => $success,
            'status'            => $success ? IntegrationMapping::STATUS_SYNCED : ($project['status'] ?? $class['status'] ?? IntegrationMapping::STATUS_FAILED),
            'action'            => $project['action'] ?? 'sync',
            'entity'            => 'horse',
            'model'             => $horse,
            'external_id'       => $project['external_id'] ?? null,
            'project_id'        => $project['external_id'] ?? null,
            'class_id'          => $class['external_id'] ?? null,
            'request_reference' => $project['request_reference'] ?? null,
            'response_status'   => $project['response_status'] ?? null,
            'error'             => $error,
        ];
    }

    /**
     * Trip → PROJECT (TRIPS), child of the Horse project, class = Horse class.
     * Dependencies (transporter/horse projects + horse class) are auto-ensured.
     */
    public function syncTrip(Trip $trip): array
    {
        if (! $trip->horse) {
            return $this->validationError($trip, 'Trip has no horse, so it cannot be linked to a Sage project.');
        }

        $horse = $this->ensureHorse($trip->horse);
        if (! $horse['success']) {
            return $this->dependencyFailure($trip, 'Horse could not be synced: ' . ($horse['error'] ?? 'unknown error'));
        }

        // Customer is optional (CUSTOMERID is set only if already synced).
        $customer       = $trip->customer;
        $customerSageId = $customer ? ($customer->sage_intacct_id ?: $customer->custom_ref) : null;

        $trailerRegs = $trip->trailers
            ->pluck('registration_number')
            ->filter()
            ->values()
            ->all();

        $payload = $this->applyOverrides(
            SageTripMapper::map($trip, $horse['project_id'], $horse['class_id'], $trailerRegs, $customerSageId)
        );
        if (empty($horse['project_id'])) {
            unset($payload['parentid']);
        }

        $result = $this->syncProject('trip_project', $trip, SageTripMapper::projectId($trip), $payload);

        // After the project is in Sage, sync the trip's expenses as Purchase
        // Requisitions. Failures are attached but never fail the project sync.
        if (! empty($result['success'])) {
            $requisitions = (new SageRequisitionService($this->driver, $this->integration))
                ->syncTripRequisitions($trip, $result['external_id'] ?? null, $horse['class_id'] ?? null);
            $result['requisitions'] = $requisitions['requisitions'] ?? [];
        }

        return $result;
    }

    // ─────────────────────────────────────────────────────────────

    /** Update if already linked, else create (fallback to update on duplicate). */
    protected function syncProject(string $entityType, Model $model, string $projectId, array $payload): array
    {
        $mapping = $this->mappingFor($this->integration, $entityType, $model);
        $mapping->local_model       = get_class($model);
        $mapping->local_reference   = $projectId;
        $mapping->last_attempted_at = now();
        if (! $mapping->exists) {
            $mapping->sync_status = IntegrationMapping::STATUS_PENDING;
            $mapping->save();
        }

        if ($mapping->external_id) {
            $res = $this->driver->updateProject($mapping->external_id, $payload);
            return $this->finishSync($mapping, $res, $mapping->external_id, 'update', $entityType, $model);
        }

        $res = $this->driver->createProject($payload);
        if (empty($res['success']) && $this->isDuplicate($res['error'] ?? null)) {
            $res = $this->driver->updateProject($projectId, $payload);
            return $this->finishSync($mapping, $res, $projectId, 'update', $entityType, $model);
        }

        return $this->finishSync($mapping, $res, $projectId, 'create', $entityType, $model);
    }

    /** Apply per-company overrides from the integration config. */
    protected function applyOverrides(array $payload): array
    {
        $cfg = $this->integration->config ?? [];

        if (! empty($cfg['project_category'])) {
            $payload['category'] = $cfg['project_category'];
        }
        if (! empty($cfg['project_location_id'])) {
            $payload['locationid'] = $cfg['project_location_id'];
        }
        if (! empty($cfg['project_department_id'])) {
            $payload['departmentid'] = $cfg['project_department_id'];
        }

        return $payload;
    }

    /** Missing-dependency (user must fix) → requires_attention, no project created. */
    protected function validationError(Trip $trip, string $message): array
    {
        return $this->tripProblem($trip, $message, IntegrationMapping::STATUS_REQUIRES_ATTENTION, 'validate');
    }

    /** A dependency sync failed (retryable) → failed. */
    protected function dependencyFailure(Trip $trip, string $message): array
    {
        return $this->tripProblem($trip, $message, IntegrationMapping::STATUS_FAILED, 'dependency');
    }

    protected function tripProblem(Trip $trip, string $message, string $status, string $action): array
    {
        $mapping = $this->mappingFor($this->integration, 'trip_project', $trip);
        $mapping->local_model     = get_class($trip);
        $mapping->local_reference = $trip->manifest_number ?: $trip->trip_number;
        $status === IntegrationMapping::STATUS_REQUIRES_ATTENTION
            ? $mapping->markRequiresAttention($message)
            : $mapping->markFailed($message);

        return [
            'success'           => false,
            'status'            => $status,
            'action'            => $action,
            'entity'            => 'trip',
            'model'             => $trip,
            'external_id'       => null,
            'request_reference' => $trip->manifest_number ?: $trip->trip_number,
            'response_status'   => null,
            'error'             => $message,
        ];
    }
}
