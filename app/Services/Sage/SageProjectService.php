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
        // Only offloaded (completed) trips that have been authorized are synced.
        if (! $this->isSyncable($trip)) {
            return [
                'success' => false,
                'skipped' => true,
                'status'  => 'skipped',
                'entity'  => 'trip',
                'model'   => $trip,
                'error'   => 'Trip must be authorized, offloaded and marked as Completed before it can sync to Sage.',
            ];
        }

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

        // Project manager = the driver's Sage EMPLOYEE (created if it doesn't exist).
        $managerId = null;
        if ($trip->driver) {
            $employee  = (new SageEmployeeService($this->driver, $this->integration))->ensureForDriver($trip->driver);
            $managerId = $employee['external_id'] ?? null;
        }

        $trailerRegs = $trip->trailers
            ->pluck('registration_number')
            ->filter()
            ->values()
            ->all();

        $payload = $this->applyOverrides(
            SageTripMapper::map($trip, $horse['project_id'], $horse['class_id'], $trailerRegs, $customerSageId, $managerId)
        );
        if (empty($horse['project_id'])) {
            unset($payload['parentid']);
        }

        // Trip Number controls (Finance workflow §4.2): the Trip Number (= PROJECTID)
        // must be UNIQUE and IMMUTABLE once synced. Reject a duplicate (already synced
        // for a different trip) or a number that changed after the first sync.
        $tripProjectId = SageTripMapper::projectId($trip);
        if ($guard = $this->guardTripNumber($trip, $tripProjectId)) {
            return $guard;
        }

        // A "Completed" project blocks Purchasing submittal in Sage, so create the
        // project in the purchasing-allowed status first, raise the requisitions /
        // dispatch sheet, THEN finalise the project status (e.g. Completed).
        $finalStatus            = $payload['projectstatus'] ?? null;
        $inProgress             = (string) config('sageintacct.project.status_in_progress', 'In Progress');
        $payload['projectstatus'] = $inProgress;

        $result    = $this->syncProject('trip_project', $trip, $tripProjectId, $payload);
        $projectId = $result['external_id'] ?? $tripProjectId;

        // After the project is in Sage, sync the trip's expenses as Purchase
        // Requisitions / a Dispatch Sheet. Failures are attached but never fail
        // the project sync.
        if (! empty($result['success'])) {
            $requisitions = (new SageRequisitionService($this->driver, $this->integration))
                ->syncTripRequisitions($trip, $projectId, $horse['class_id'] ?? null);
            $result['requisitions'] = $requisitions['requisitions'] ?? [];

            // Raise a "PR - Diesel" for each approved fuel order attached to the
            // trip (supplier = the fuelling station/container), while the project
            // is still purchasing-allowed.
            $fuelResults = [];
            foreach ($trip->fuels()->where('authorization', 'approved')->get() as $fuel) {
                $fuelResults[] = (new SageFuelDieselService($this->driver, $this->integration))->syncFuel($fuel);
            }
            $result['fuel'] = $fuelResults;

            // Finalise the project status now that the purchasing docs are raised —
            // UNLESS trips are kept OPEN (Finance model): the integration must NOT
            // financially close the trip; Finance completes it in Sage so late
            // supplier/customer invoices can still post against it.
            if (! config('sageintacct.trip.keep_open', true)
                && $finalStatus && strcasecmp($finalStatus, $inProgress) !== 0) {
                $this->driver->updateProject($projectId, ['projectstatus' => $finalStatus]);
                $result['project_finalised_status'] = $finalStatus;
            }
        }

        return $result;
    }

    // ─────────────────────────────────────────────────────────────

    /**
     * Only authorized, offloaded, AND marked-completed (status == 1) trips sync
     * to Sage — completion is the final lock, so nothing changes after the push.
     * The offloaded statuses are configurable (sageintacct.trip.syncable_statuses).
     */
    protected function isSyncable(Trip $trip): bool
    {
        if (strcasecmp((string) $trip->authorization, 'approved') !== 0) {
            return false;
        }

        // Marked as completed (status = 1) — no more edits after this.
        if ((int) $trip->status !== 1) {
            return false;
        }

        $allowed = array_map('strtolower', (array) config('sageintacct.trip.syncable_statuses', ['Offloaded']));

        return in_array(strtolower((string) $trip->trip_status), $allowed, true);
    }

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
    /**
     * Enforce the Trip Number controls before creating/updating the Sage project:
     *   • DUPLICATE — the same Trip Number (PROJECTID) is already synced for another
     *     trip (unique-number rule);
     *   • IMMUTABLE — this trip was already synced under a different PROJECTID, i.e.
     *     the Trip Number changed after the first sync.
     * Returns a requires_attention result to reject, or null to proceed.
     */
    protected function guardTripNumber(Trip $trip, string $projectId): ?array
    {
        $dup = IntegrationMapping::where([
            'company_integration_id' => $this->integration->id,
            'entity_type'            => 'trip_project',
            'external_id'            => $projectId,
        ])->where('local_id', '!=', $trip->id)->whereNotNull('external_id')->first();
        if ($dup) {
            return $this->validationError($trip, "Trip Number '{$projectId}' is already synced to Sage for a different trip (#{$dup->local_id}). Trip Numbers must be unique across both systems.");
        }

        $existing = IntegrationMapping::where([
            'company_integration_id' => $this->integration->id,
            'entity_type'            => 'trip_project',
            'local_id'               => $trip->id,
        ])->whereNotNull('external_id')->first();
        if ($existing && strcasecmp((string) $existing->external_id, $projectId) !== 0) {
            return $this->validationError($trip, "Trip Number changed after the first Sage sync ('{$existing->external_id}' → '{$projectId}'). The Trip Number is immutable once synced — use a controlled amendment/cancellation instead of renumbering.");
        }

        return null;
    }

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
