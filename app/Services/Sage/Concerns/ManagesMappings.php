<?php

namespace App\Services\Sage\Concerns;

use App\Models\CompanyIntegration;
use App\Models\IntegrationMapping;
use Illuminate\Database\Eloquent\Model;

/**
 * Shared mapping read/write + result normalisation for the Class and Project
 * services. Keeps idempotency + error classification in one place.
 */
trait ManagesMappings
{
    /** Find-or-new the mapping row for a company + entity type + local record. */
    protected function mappingFor(CompanyIntegration $integration, string $entityType, Model $model): IntegrationMapping
    {
        return IntegrationMapping::firstOrNew([
            'company_integration_id' => $integration->id,
            'entity_type'            => $entityType,
            'local_id'               => $model->getKey(),
        ]);
    }

    /** True when a create failed only because the record already exists in Sage. */
    protected function isDuplicate(?string $error): bool
    {
        return str_contains(strtolower((string) $error), 'already exists');
    }

    /**
     * Classify a Sage failure into a mapping status. Validation / configuration
     * problems need a human (requires_attention); everything else is a
     * retryable failure.
     */
    protected function classifySageError(?string $error): string
    {
        $e = strtolower((string) $error);

        $attention = [
            'object definition', 'not found', 'not subscribed', 'projectcategory',
            'does not exist', 'is required', 'invalid', 'unsupported', 'not authorized',
            'permission',
        ];
        foreach ($attention as $needle) {
            if (str_contains($e, $needle)) {
                return IntegrationMapping::STATUS_REQUIRES_ATTENTION;
            }
        }

        return IntegrationMapping::STATUS_FAILED;
    }

    /**
     * Apply a driver result to the mapping and return a normalised summary the
     * SageSyncService can audit. A successful Sage call is committed to the
     * mapping immediately; it is never rolled back by a later failure.
     */
    protected function finishSync(IntegrationMapping $mapping, array $res, string $sentId, string $action, string $entityType, Model $model): array
    {
        $request  = $res['request'] ?? null;   // credential-free function XML
        $response = $res['response'] ?? null;

        if (! empty($res['success'])) {
            $externalId = ($res['data']['id'] ?? null) ?: $sentId;
            $mapping->markSynced($externalId, $mapping->local_reference, $request, $response);

            return [
                'success'           => true,
                'status'            => IntegrationMapping::STATUS_SYNCED,
                'action'            => $action,
                'entity'            => $entityType,
                'model'             => $model,
                'external_id'       => $externalId,
                'request_reference' => $sentId,
                'response_status'   => $res['status'] ?? null,
            ];
        }

        $error  = $res['error'] ?? 'Unknown Sage Intacct error.';
        $status = $this->classifySageError($error);

        $status === IntegrationMapping::STATUS_REQUIRES_ATTENTION
            ? $mapping->markRequiresAttention($error, $request, $response)
            : $mapping->markFailed($error, $request, $response);

        return [
            'success'           => false,
            'status'            => $status,
            'action'            => $action,
            'entity'            => $entityType,
            'model'             => $model,
            'external_id'       => null,
            'request_reference' => $sentId,
            'response_status'   => $res['status'] ?? null,
            'error'             => $error,
        ];
    }
}
