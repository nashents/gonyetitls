<?php

namespace App\Services\Sage;

use App\Integrations\Contracts\SageDriver;
use App\Models\CompanyIntegration;
use App\Models\IntegrationMapping;
use App\Models\Trip;
use App\Services\Sage\Concerns\ManagesMappings;
use App\Services\Sage\Mappers\SageRequisitionMapper;
use App\Services\SageIntacctService;

/**
 * Syncs a Trip's expense-type trip_expenses to Sage Purchase Requisitions —
 * one requisition per (vendor × currency), each expense a line attached to the
 * trip Project, horse Class, driver Employee, and the expense Item.
 *
 * Idempotent per group: mapping keyed entity_type="trip_requisition_v{vendor}_c{ccy}",
 * local_id=trip_id. An already-created requisition is skipped (Sage requisitions
 * get "Converted" and can't be safely recreated).
 */
class SageRequisitionService
{
    use ManagesMappings;

    protected SageDriver $driver;
    protected CompanyIntegration $integration;
    protected SageItemService $itemService;
    protected SageEmployeeService $employeeService;

    public function __construct(SageDriver $driver, CompanyIntegration $integration)
    {
        $this->driver          = $driver;
        $this->integration     = $integration;
        $this->itemService     = new SageItemService($driver, $integration);
        $this->employeeService = new SageEmployeeService($driver, $integration);
    }

    /**
     * @param  string|null  $projectId  the trip's Sage project id
     * @param  string|null  $classId    the horse class id
     * @return array  ['requisitions' => [per-group result, …]]
     */
    public function syncTripRequisitions(Trip $trip, ?string $projectId, ?string $classId): array
    {
        // Expense-type only (must reference a master Expense).
        $expenses = $trip->trip_expenses()
            ->whereNotNull('expense_id')
            ->with(['vendor', 'currency', 'expense'])
            ->get();

        // Ensure the driver's Employee once (shared across this trip's lines).
        $employee   = $this->employeeService->ensureForDriver($trip->driver);
        $employeeId = $employee['external_id'] ?? null;

        // Group by vendor + currency.
        $groups = $expenses->groupBy(fn ($e) => ($e->vendor_id ?: 0) . '_' . ($e->currency_id ?: 0));

        $results = [];
        foreach ($groups as $group) {
            $results[] = $this->syncGroup($trip, $group, $projectId, $classId, $employeeId);
        }

        return ['requisitions' => $results];
    }

    /** One (vendor × currency) group → one requisition. */
    protected function syncGroup(Trip $trip, $group, ?string $projectId, ?string $classId, ?string $employeeId): array
    {
        $first    = $group->first();
        $vendor   = $first->vendor;
        $vendorId = (int) ($first->vendor_id ?: 0);
        $ccyId    = (int) ($first->currency_id ?: 0);
        $entity   = 'trip_requisition_v' . $vendorId . '_c' . $ccyId;

        $mapping = $this->mappingFor($this->integration, $entity, $trip);
        $mapping->local_model     = get_class($trip);
        $mapping->local_reference = SageRequisitionMapper::referenceNo($trip, $vendorId);

        // Already created → skip (converted requisitions can't be recreated).
        if ($mapping->exists && $mapping->external_id) {
            return $this->result(true, 'skipped', $mapping->external_id, null, $entity);
        }

        $mapping->last_attempted_at = now();
        if (! $mapping->exists) {
            $mapping->save();
        }

        if (! $vendor) {
            return $this->fail($mapping, $entity, 'Expense has no vendor to raise a requisition for.', IntegrationMapping::STATUS_REQUIRES_ATTENTION);
        }

        // Ensure the vendor is in Sage (auto-sync once via Phase-1 service).
        $vendorSageId = $vendor->sage_intacct_id ?: $vendor->custom_ref;
        if (! $vendorSageId) {
            // Some vendors have no company_id; sync them under the trip's Sage
            // company so the Phase-1 service resolves the right integration.
            if (! $vendor->company_id) {
                $vendor->company_id = $trip->company_id;
            }
            app(SageIntacctService::class)->syncVendor($vendor);
            $vendor->refresh();
            $vendorSageId = $vendor->sage_intacct_id ?: $vendor->custom_ref;
        }
        if (! $vendorSageId) {
            return $this->fail($mapping, $entity, "Vendor '{$vendor->name}' is not synced to Sage yet.", IntegrationMapping::STATUS_REQUIRES_ATTENTION);
        }

        $vendorContact = $this->vendorContactName($vendorSageId) ?: $vendor->name;

        // Ensure an item for each expense; build the lines.
        $lines = [];
        foreach ($group as $expense) {
            if (! $expense->expense) {
                continue;
            }
            $item = $this->itemService->ensureItem($expense->expense);
            if (empty($item['success']) || empty($item['external_id'])) {
                return $this->fail($mapping, $entity, 'Item sync failed for expense: ' . ($item['error'] ?? 'unknown'), IntegrationMapping::STATUS_FAILED);
            }
            $lines[] = SageRequisitionMapper::line($expense, $item['external_id'], $projectId, $classId, $employeeId);
        }

        if (empty($lines)) {
            return $this->result(true, 'skipped', null, null, $entity);
        }

        $header = SageRequisitionMapper::header($trip, $vendorId, $vendorSageId, $vendorContact, optional($first->currency)->code);
        $res    = $this->driver->createRequisition($header, $lines);

        return $this->finishSync($mapping, $res, $header['referenceno'], 'create', $entity, $trip);
    }

    /** Fetch the vendor's contact name for pay-to / return-to. */
    protected function vendorContactName(string $vendorSageId): ?string
    {
        $safe = str_replace("'", '', $vendorSageId);
        $res  = $this->driver->readByQuery('VENDOR', ['VENDORID', 'DISPLAYCONTACT.CONTACTNAME'], "VENDORID = '{$safe}'", 1);

        return (! empty($res['success']) && ! empty($res['data'][0]['DISPLAYCONTACT.CONTACTNAME']))
            ? $res['data'][0]['DISPLAYCONTACT.CONTACTNAME']
            : null;
    }

    protected function fail(IntegrationMapping $mapping, string $entity, string $message, string $status): array
    {
        $status === IntegrationMapping::STATUS_REQUIRES_ATTENTION
            ? $mapping->markRequiresAttention($message)
            : $mapping->markFailed($message);

        return $this->result(false, $status, null, $message, $entity);
    }

    protected function result(bool $success, string $status, ?string $externalId, ?string $error, string $entity): array
    {
        return ['success' => $success, 'status' => $status, 'external_id' => $externalId, 'error' => $error, 'entity' => $entity];
    }
}
