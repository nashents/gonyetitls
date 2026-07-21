<?php

namespace App\Services\Sage;

use App\Integrations\Contracts\SageDriver;
use App\Models\CompanyIntegration;
use App\Models\IntegrationMapping;
use App\Models\Trip;
use App\Services\Sage\Concerns\ManagesMappings;
use App\Services\Sage\Mappers\SageTripMapper;
use App\Services\SageIntacctService;

/**
 * Syncs Gonyeti Trips to Sage Projects.
 *
 * Enforces dependencies: the trip's Customer must exist in Sage (auto-synced
 * once via the Phase-1 service, else a clear validation error), and the Horse /
 * Trailer classes are ensured (idempotent) before the Project is created.
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

    public function syncTrip(Trip $trip): array
    {
        // ── Dependency 1: Customer must be synced ────────────────────────
        $customer = $trip->customer;
        if (! $customer) {
            return $this->validationError($trip, 'Trip has no customer, so it cannot be linked to a Sage project.');
        }

        $customerSageId = $customer->sage_intacct_id ?: $customer->custom_ref;
        if (! $customerSageId) {
            // Auto-sync the customer once via the existing Phase-1 service.
            app(SageIntacctService::class)->syncCustomer($customer);
            $customer->refresh();
            $customerSageId = $customer->sage_intacct_id ?: $customer->custom_ref;
        }
        if (! $customerSageId) {
            return $this->validationError($trip, "Customer '{$customer->name}' is not synced to Sage yet — sync the customer first.");
        }

        // ── Dependency 2: Horse class (+ its Transporter parent) ─────────
        $horseClassId = null;
        if ($trip->horse) {
            $horseResult  = $this->classService->syncHorse($trip->horse);
            $horseClassId = $horseResult['external_id'] ?? null;
        }

        // ── Dependency 3: Trailer classes (best-effort) + collect regs ───
        $trailerRegs = [];
        foreach ($trip->trailers as $trailer) {
            $this->classService->syncTrailer($trailer);
            if ($trailer->registration_number) {
                $trailerRegs[] = $trailer->registration_number;
            }
        }

        // ── Build + send the Project ─────────────────────────────────────
        $payload = SageTripMapper::map($trip, $customerSageId, $horseClassId, $trailerRegs);

        // Per-company PROJECTCATEGORY override on the integration config.
        if (! empty($this->integration->config['project_category'])) {
            $payload['category'] = $this->integration->config['project_category'];
        }

        $projectId = SageTripMapper::projectId($trip);

        $mapping = $this->mappingFor($this->integration, 'trip', $trip);
        $mapping->local_model      = get_class($trip);
        $mapping->local_reference  = $trip->trip_number ?: $projectId;
        $mapping->last_attempted_at = now();
        if (! $mapping->exists) {
            $mapping->sync_status = IntegrationMapping::STATUS_PENDING;
            $mapping->save();
        }

        if ($mapping->external_id) {
            $res = $this->driver->updateProject($mapping->external_id, $payload);
            return $this->finishSync($mapping, $res, $mapping->external_id, 'update', 'trip', $trip);
        }

        $res = $this->driver->createProject($payload);
        if (empty($res['success']) && $this->isDuplicate($res['error'] ?? null)) {
            $res = $this->driver->updateProject($projectId, $payload);
            return $this->finishSync($mapping, $res, $projectId, 'update', 'trip', $trip);
        }

        return $this->finishSync($mapping, $res, $projectId, 'create', 'trip', $trip);
    }

    /**
     * A missing-dependency validation error: mark the mapping requires_attention
     * (never create a Project) and return a clear message for the user.
     */
    protected function validationError(Trip $trip, string $message): array
    {
        $mapping = $this->mappingFor($this->integration, 'trip', $trip);
        $mapping->local_model     = get_class($trip);
        $mapping->local_reference = $trip->trip_number;
        $mapping->markRequiresAttention($message);

        return [
            'success'           => false,
            'status'            => IntegrationMapping::STATUS_REQUIRES_ATTENTION,
            'action'            => 'validate',
            'entity'            => 'trip',
            'model'             => $trip,
            'external_id'       => null,
            'request_reference' => $trip->trip_number,
            'response_status'   => null,
            'error'             => $message,
        ];
    }
}
