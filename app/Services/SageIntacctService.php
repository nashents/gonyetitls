<?php

namespace App\Services;

use App\Integrations\Contracts\SageDriver;
use App\Models\CompanyIntegration;
use App\Models\Customer;
use App\Models\IntegrationLog;
use App\Models\Vendor;
use App\Services\Sage\Concerns\ResolvesSageIntegration;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Orchestrates syncing Gonyeti customers/vendors to Sage Intacct.
 *
 * Public entry points: syncCustomer(), syncVendor(), retry(). Each one:
 *   1. resolves the model's company → active `sage_intacct` CompanyIntegration
 *      (silently does nothing if the integration is missing/inactive);
 *   2. builds the driver (XML by default, REST when configured);
 *   3. de-dupes via the model's Sage id (sage_intacct_id OR custom_ref) — update
 *      if it already exists in Sage, create otherwise;
 *   4. updates the model's sync state + the integration's last_sync_at/last_error;
 *   5. writes an integration_logs audit row (no secrets, ever).
 *
 * Queue-ready: the components call dispatchSync(); to move syncing onto a queue
 * later, swap that one method for a job dispatch — call sites don't change.
 */
class SageIntacctService
{
    use ResolvesSageIntegration;

    // ─────────────────────────────────────────────────────────────
    // PUBLIC API
    // ─────────────────────────────────────────────────────────────

    public function syncCustomer(Customer $customer): array
    {
        return $this->sync($customer, 'customer');
    }

    public function syncVendor(Vendor $vendor): array
    {
        return $this->sync($vendor, 'vendor');
    }

    /**
     * Retry a previously failed sync. Idempotent: because we de-dupe on the
     * Sage id, this never creates a duplicate in Sage.
     */
    public function retry(Model $model): array
    {
        $entity = $model instanceof Vendor ? 'vendor' : 'customer';
        return $this->sync($model, $entity);
    }

    // ─────────────────────────────────────────────────────────────
    // CORE
    // ─────────────────────────────────────────────────────────────

    protected function sync(Model $model, string $entity): array
    {
        $integration = $this->activeIntegrationFor($model);

        // No active Sage integration for this company → do nothing. The ERP
        // record stands; we don't mark it failed because nothing was attempted.
        if (! $integration) {
            return ['success' => false, 'skipped' => true, 'error' => 'Sage Intacct integration is not active for this company.'];
        }

        try {
            $driver  = $this->driverFor($integration);
            $payload = $entity === 'vendor' ? $this->mapVendor($model) : $this->mapCustomer($model);

            $sageId = $model->sageId(); // sage_intacct_id ?: custom_ref

            if ($sageId) {
                // Already exists in Sage → update, never create a duplicate.
                $result = $entity === 'vendor'
                    ? $driver->updateVendor($sageId, $payload)
                    : $driver->updateCustomer($sageId, $payload);
                $action = 'update';
            } else {
                $result = $entity === 'vendor'
                    ? $driver->createVendor($payload)
                    : $driver->createCustomer($payload);
                $action = 'create';
            }
        } catch (Throwable $e) {
            // Defensive: a bug/exception in a driver should still be recorded
            // cleanly and never leak through to the Livewire component.
            Log::error("SageIntacct sync {$entity} #{$model->getKey()} threw: " . $e->getMessage());
            $result = ['success' => false, 'status' => null, 'error' => 'Internal error during Sage sync.'];
            $action = $model->sageId() ? 'update' : 'create';
        }

        return $this->finish($model, $entity, $action, $integration, $result, $payload['id'] ?? null);
    }

    /**
     * Apply the driver result to the model + integration and write the audit log.
     */
    protected function finish(Model $model, string $entity, string $action, CompanyIntegration $integration, array $result, ?string $requestReference): array
    {
        if (! empty($result['success'])) {
            // Prefer the id Sage echoes back; if the response omits it, fall back
            // to the CUSTOMERID/VENDORID we sent ($requestReference) — that is the
            // value we target updates by — and finally to any id already known.
            // Using ?: so empty strings also fall through.
            $sageId = ($result['data']['id'] ?? null) ?: ($requestReference ?: $model->sageId());
            $model->markSageSynced((string) $sageId);

            $integration->forceFill([
                'last_sync_at'    => now(),
                'last_success_at' => now(),
                'last_error'      => null,
                'status'          => 'active',
            ])->save();

            $this->logAudit($integration, $model, $entity, $action, 'ok', null, $requestReference, $result['status'] ?? null);

            return ['success' => true, 'sage_id' => $sageId];
        }

        $error = $result['error'] ?? 'Unknown Sage Intacct error.';
        $model->markSageFailed($error);

        $integration->forceFill(['last_error' => $error])->save();

        $this->logAudit($integration, $model, $entity, $action, 'fail', $error, $requestReference, $result['status'] ?? null);

        return ['success' => false, 'error' => $error];
    }

    // ─────────────────────────────────────────────────────────────
    // FIELD MAPPING  (Gonyeti → Sage generic payload)
    // Adjust these mappings to the client's Sage configuration.
    // ─────────────────────────────────────────────────────────────

    protected function mapCustomer(Customer $c): array
    {
        return [
            // CUSTOMERID: existing-Sage records reuse custom_ref; new ones send
            // the Gonyeti customer_number (fallback CUST-{id} if unset).
            'id'       => $c->sageId() ?: ($c->customer_number ?: 'CUST-' . $c->id),
            'name'     => $c->name,
            'email'    => $c->email,
            'phone'    => $c->phonenumber,
            'address1' => $c->street_address,
            'city'     => $c->city,
            'country'  => $c->country,
            // ADJUST: pick the tax field that matches the client's Sage setup.
            'taxid'    => $c->vat_number ?: $c->tin_number,
            // CURRENCY only applies when multi-currency is enabled in Sage.
            'currency' => optional($c->currency)->code,
            'status'   => $this->mapStatus($c->status),
        ];
    }

    protected function mapVendor(Vendor $v): array
    {
        return [
            // VENDORID: reuse Sage id, else vendor_number, else VEND-{id}.
            'id'       => $v->sageId() ?: ($v->vendor_number ?: 'VEND-' . $v->id),
            'name'     => $v->name,
            'email'    => $v->email,
            'phone'    => $v->phonenumber,
            'address1' => $v->street_address,
            'city'     => $v->city,
            'country'  => $v->country,
            'taxid'    => $v->vat_number ?: $v->tin_number,
            'currency' => optional($v->currency)->code,
            'status'   => $this->mapStatus($v->status),
        ];
    }

    /** Gonyeti stores status as 1/0; Sage expects active/inactive. */
    protected function mapStatus($status): string
    {
        return (string) $status === '0' ? 'inactive' : 'active';
    }

    // ─────────────────────────────────────────────────────────────
    // RESOLUTION
    // ─────────────────────────────────────────────────────────────

    /**
     * Find the active Sage integration for the model's company.
     * Resolution now lives in the shared ResolvesSageIntegration trait.
     */
    protected function activeIntegrationFor(Model $model): ?CompanyIntegration
    {
        return $this->activeSageIntegration($model->company_id);
    }

    protected function driverFor(CompanyIntegration $integration): SageDriver
    {
        return $this->sageDriverFor($integration);
    }

    // ─────────────────────────────────────────────────────────────
    // AUDIT  (reuses the existing integration_logs table)
    // ─────────────────────────────────────────────────────────────

    /**
     * Write one audit row. The spec's model_type/model_id/request_reference/
     * synced_at live in `meta` (safe metadata only — never credentials).
     */
    protected function logAudit(CompanyIntegration $integration, Model $model, string $entity, string $action, string $status, ?string $error, ?string $requestReference, $responseStatus): void
    {
        IntegrationLog::create([
            'company_integration_id' => $integration->id,
            'direction'              => 'outbound',
            'action'                 => $action,            // create | update
            'status'                 => $status,           // ok | fail
            'message'                => $error,            // readable error or null
            'meta'                   => [
                'entity'            => $entity,            // customer | vendor
                'model_type'        => get_class($model),
                'model_id'          => $model->getKey(),
                'request_reference' => $requestReference,  // CUSTOMERID / VENDORID sent
                'response_status'   => $responseStatus,
                'synced_at'         => now()->toIso8601String(),
            ],
        ]);
    }
}
