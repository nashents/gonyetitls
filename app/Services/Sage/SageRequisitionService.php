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
 * Syncs a Trip's expense- AND allowance-type trip_expenses to Sage purchasing
 * documents — one per (vendor × currency), each line attached to the trip
 * Project, horse Class, driver Employee, and the expense/allowance Item.
 *
 * Document split by vendor:
 *   • paycard vendor (config purchasing.dispatch_vendor_*) → a "Dispatch Sheet"
 *   • every other vendor                                   → a Purchase Requisition
 * Both are Quote-class PO transactions created the same way (entity-scoped so the
 * Convert action is available); their edit/delete/convert rules come from the
 * Sage transaction definition.
 *
 * Idempotent per group: mapping keyed entity_type="trip_{dispatch|requisition}_v{vendor}_c{ccy}",
 * local_id=trip_id. An already-created document is skipped.
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
        // Expense- OR allowance-type trip expenses (each references a master).
        // Fuel-linked lines are EXCLUDED — those sync separately as their own
        // "PR - Diesel" via SageFuelDieselService (see SageProjectService::syncTrip);
        // including them here would double-post the same fuel spend to Sage as
        // two separate purchasing documents.
        $expenses = $trip->trip_expenses()
            ->whereNull('fuel_id')
            ->where(fn ($q) => $q->whereNotNull('expense_id')->orWhereNotNull('allowance_id'))
            ->with(['vendor', 'currency', 'expense', 'allowance'])
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

    /** One (vendor × currency) group → one Dispatch Sheet or Purchase Requisition. */
    protected function syncGroup(Trip $trip, $group, ?string $projectId, ?string $classId, ?string $employeeId): array
    {
        $first    = $group->first();
        $vendor   = $first->vendor;
        $vendorId = (int) ($first->vendor_id ?: 0);
        $ccyId    = (int) ($first->currency_id ?: 0);

        // Route the paycard vendor's lines onto a Dispatch Sheet; everything else
        // stays a Purchase Requisition. The document type drives the entity_type
        // key so the two never collide for the same trip.
        $isDispatch = $this->isDispatchVendor($vendor);
        $docType    = $isDispatch
            ? (string) config('sageintacct.purchasing.dispatch_sheet_type', 'Dispatch Sheet')
            : (string) config('sageintacct.purchasing.requisition_type', 'Purchase requisition');
        $entity = ($isDispatch ? 'trip_dispatch_v' : 'trip_requisition_v') . $vendorId . '_c' . $ccyId;

        $mapping = $this->mappingFor($this->integration, $entity, $trip);
        $mapping->local_model     = get_class($trip);
        $mapping->local_reference = SageRequisitionMapper::referenceNo($trip, $vendorId);

        // Already created → skip (these documents get converted; don't recreate).
        if ($mapping->exists && $mapping->external_id) {
            return $this->result(true, 'skipped', $mapping->external_id, null, $entity);
        }

        $mapping->last_attempted_at = now();
        if (! $mapping->exists) {
            $mapping->save();
        }

        if (! $vendor) {
            return $this->fail($mapping, $entity, 'Trip expense has no vendor to raise a document for.', IntegrationMapping::STATUS_REQUIRES_ATTENTION);
        }

        $vendorSageId = $this->resolveVendorSageId($vendor, $trip, $isDispatch);
        if (! $vendorSageId) {
            return $this->fail($mapping, $entity, "Vendor '{$vendor->name}' is not synced to Sage yet.", IntegrationMapping::STATUS_REQUIRES_ATTENTION);
        }

        $vendorContact = $this->vendorContactName($vendorSageId) ?: $vendor->name;

        // Ensure an item for each expense/allowance line; build the lines.
        $lines = [];
        foreach ($group as $line) {
            if ($line->expense) {
                $item = $this->itemService->ensureItem($line->expense);
            } elseif ($line->allowance) {
                $item = $this->itemService->ensureAllowanceItem($line->allowance);
            } else {
                continue;
            }
            if (empty($item['success']) || empty($item['external_id'])) {
                return $this->fail($mapping, $entity, 'Item sync failed for line: ' . ($item['error'] ?? 'unknown'), IntegrationMapping::STATUS_FAILED);
            }
            $lines[] = SageRequisitionMapper::line($line, $item['external_id'], $projectId, $classId, $employeeId);
        }

        if (empty($lines)) {
            return $this->result(true, 'skipped', null, null, $entity);
        }

        $header = SageRequisitionMapper::header($trip, $vendorId, $vendorSageId, $vendorContact, optional($first->currency)->code, $docType);

        // The Dispatch Sheet definition now uses the CLASS + EMPLOYEE dimensions
        // (carried on each line, like trip-project requisitions). Sage made the old
        // REG/Driver pick-lists optional (2026-08-07, API-verified), so we no longer
        // send them — the class + employee on the lines identify the truck/driver.

        $res = $this->driver->createRequisition($header, $lines);

        return $this->finishSync($mapping, $res, $header['referenceno'], 'create', $entity, $trip);
    }

    /**
     * Is this the drivers' paycard vendor (→ Dispatch Sheet)? Matches by the
     * configured Sage VENDORID, then the configured name, then a loose
     * "paycard … dispatch" check. Static (pure function of config + $vendor)
     * so callers that just need the routing decision — e.g. a sync-status
     * badge — don't need a driver/integration instance to ask it.
     */
    public static function isDispatchVendor($vendor): bool
    {
        if (! $vendor) {
            return false;
        }

        $sid      = $vendor->sage_intacct_id ?: $vendor->custom_ref;
        $targetId = (string) config('sageintacct.purchasing.dispatch_vendor_sage_id', '');
        if ($targetId !== '' && $sid && strcasecmp((string) $sid, $targetId) === 0) {
            return true;
        }

        $name       = self::normalizeName($vendor->name);
        $targetName = self::normalizeName((string) config('sageintacct.purchasing.dispatch_vendor_name', ''));
        if ($targetName !== '' && $name === $targetName) {
            return true;
        }

        return str_contains($name, 'paycard') && str_contains($name, 'dispatch');
    }

    /**
     * The Sage VENDORID to post to. Dispatch documents always post to the fixed
     * paycard VENDORID (config); the Gonyeti vendor is linked to it for future
     * runs. Other vendors are auto-synced once via the Phase-1 service.
     */
    protected function resolveVendorSageId($vendor, Trip $trip, bool $isDispatch): ?string
    {
        $fixed = (string) config('sageintacct.purchasing.dispatch_vendor_sage_id', '');
        if ($isDispatch && $fixed !== '') {
            if (! ($vendor->sage_intacct_id ?: $vendor->custom_ref)) {
                $vendor->forceFill(['sage_intacct_id' => $fixed, 'custom_ref' => $fixed])->saveQuietly();
            }
            return $fixed;
        }

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

        return $vendorSageId ?: null;
    }

    /** Lower-case, punctuation-stripped name for loose vendor matching. */
    protected static function normalizeName(?string $s): string
    {
        $s = mb_strtolower(preg_replace('/[^a-z0-9\s]/i', ' ', (string) $s));

        return trim(preg_replace('/\s+/', ' ', $s));
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
