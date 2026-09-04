<?php

namespace App\Services\Sage;

use App\Integrations\Contracts\SageDriver;
use App\Models\CompanyIntegration;
use App\Models\Container;
use App\Models\Fuel;
use App\Models\IntegrationMapping;
use App\Services\Sage\Concerns\ManagesMappings;
use App\Services\SageIntacctService;

/**
 * Syncs an APPROVED Gonyeti Fuel order to a Sage "PR - Diesel" document (Quote
 * class — created via create_potransaction, convertible in Sage).
 *
 *  • supplier   = the fuelling station (the fuel's Container — matched to a Sage
 *                 VENDOR by name, created if missing);
 *  • line       = one diesel item (config fuel.item_*), qty = litres, price = unit
 *                 price, carrying the CLASS + EMPLOYEE (+ optional PROJECT — horse
 *                 project, else trip project) that identify the truck/driver;
 *  • NO REG/Driver custom fields — Sage made those pick-lists optional on the
 *    "PR - Diesel" definition (2026-08-07), so class + employee stand in for them.
 *
 * Only authorized (approved) fuels sync (gate below). Idempotent: mapping
 * entity_type="fuel_pr_diesel", local_id=fuel_id — an already-created doc skips.
 */
class SageFuelDieselService
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

    public function syncFuel(Fuel $fuel): array
    {
        $entity  = 'fuel_pr_diesel';

        // Only push authorized (approved) fuel orders — never a draft/pending one.
        // Single gate honoured by both the FuelObserver and the manual index button.
        if (strcasecmp((string) $fuel->authorization, 'approved') !== 0) {
            return $this->result(true, 'skipped', null, null, $entity, $fuel);
        }

        $mapping = $this->mappingFor($this->integration, $entity, $fuel);
        $mapping->local_model     = get_class($fuel);
        $mapping->local_reference = $this->referenceNo($fuel);

        // Already created → skip (these documents get converted; don't recreate).
        if ($mapping->exists && $mapping->external_id) {
            return $this->result(true, 'skipped', $mapping->external_id, null, $entity, $fuel);
        }

        $mapping->last_attempted_at = now();
        if (! $mapping->exists) {
            $mapping->save();
        }

        // Only station-sourced fuel has an external supplier (truck-to-truck
        // transfers have no fuelling station → nothing to raise).
        $container = $fuel->container;
        if (! $container) {
            return $this->fail($mapping, $entity, $fuel, 'Fuel order has no fuelling station (container) — no PR - Diesel raised.', IntegrationMapping::STATUS_REQUIRES_ATTENTION);
        }

        $vendorSageId = $this->resolveStationVendor($container, $fuel);
        if (! $vendorSageId) {
            return $this->fail($mapping, $entity, $fuel, "Could not resolve the fuelling station '{$container->name}' as a Sage vendor.", IntegrationMapping::STATUS_REQUIRES_ATTENTION);
        }

        $diesel = $this->resolveDieselItem();
        if (empty($diesel['success'])) {
            return $this->fail($mapping, $entity, $fuel, 'Diesel item sync failed: ' . ($diesel['error'] ?? 'unknown'), IntegrationMapping::STATUS_FAILED);
        }

        // PROJECT = the trip project when the fuel is on a trip, else the horse's
        // own project; CLASS = the horse class; EMPLOYEE = the driver. Class +
        // employee identify the truck/driver on the PR - Diesel (REG/Driver dropped).
        [$projectId, $classId, $isTripProject] = $this->resolveProjectAndClass($fuel);
        $employeeId    = $this->resolveDriverEmployee($fuel);
        $vendorContact = $this->vendorContactName($vendorSageId) ?: $container->name;

        $qty = (float) ($fuel->quantity ?: 0);
        if ($qty <= 0) {
            $qty = 1;
        }
        $unitPrice = (float) ($fuel->unit_price ?: 0);
        if ($unitPrice <= 0) {
            // Fall back to total / qty when no unit price is captured.
            $unitPrice = $qty > 0 ? ((float) ($fuel->amount ?: 0) / $qty) : (float) ($fuel->amount ?: 0);
        }

        $line = [
            'itemid'       => $diesel['external_id'],
            'itemdesc'     => 'Diesel - ' . $container->name,
            'quantity'     => $qty,
            'unit'         => (string) config('sageintacct.fuel.line_unit', 'Each'),
            'price'        => number_format($unitPrice, 2, '.', ''),
            'locationid'   => config('sageintacct.project.location_id'),
            'departmentid' => config('sageintacct.project.department_id'),
            'projectid'    => $projectId ?: null,
            'employeeid'   => $employeeId ?: null,
            'classid'      => $classId ?: null,
        ];

        // "PR - Diesel" identifies the truck/driver via the CLASS + EMPLOYEE (+ optional
        // PROJECT) dimensions on the line. Sage made the old REG/Driver pick-lists
        // optional (2026-08-07, API-verified), so no header custom fields are sent.
        $header = [
            'transactiontype' => (string) config('sageintacct.fuel.type', 'PR - Diesel'),
            'datecreated'     => $fuel->date ?: now()->toDateString(),
            'datedue'         => $fuel->date ?: now()->toDateString(),
            'vendorid'        => $vendorSageId,
            'referenceno'     => $this->referenceNo($fuel),
            'contactname'     => $vendorContact,
            'currency'        => optional($fuel->currency)->name ?: null,
            'exchratetype'    => config('sageintacct.purchasing.exchange_rate_type') ?: null,
            'entityid'        => config('sageintacct.purchasing.entity_id') ?: null,
        ];

        // A "Completed" trip project blocks purchasing submittal, and trip projects
        // are Completed by the time a fuel is synced outside the trip's own sync
        // window. Briefly reopen the trip project (same two-phase pattern syncTrip
        // uses), post, then restore — so fuel still lands on the trip project.
        $res = $this->postAllowingProject($projectId, $isTripProject, fn () => $this->driver->createRequisition($header, [$line]));

        return $this->finishSync($mapping, $res, $header['referenceno'], 'create', $entity, $fuel);
    }

    /**
     * Run $create with the given trip PROJECT temporarily in a purchasing-allowed
     * status. Horse projects (and no project) post directly. For a trip project we
     * reopen → post → restore in a finally, so the project is never left reopened.
     */
    protected function postAllowingProject(?string $projectId, bool $isTripProject, callable $create): array
    {
        // Trips are kept OPEN under the Finance model, so the trip project is already
        // in a purchasing-allowed status — post directly. (The reopen/restore below is
        // only for the legacy "auto-complete trips" mode, where the project is Completed;
        // if Finance has closed a trip in Sage, a late fuel then parks for attention,
        // which is the correct controlled-reopen signal.)
        if (! $projectId || ! $isTripProject || config('sageintacct.trip.keep_open', true)) {
            return $create();
        }

        $inProgress = (string) config('sageintacct.project.status_in_progress', 'In Progress');
        $completed  = (string) config('sageintacct.project.status_completed', 'Completed');

        $this->driver->updateProject($projectId, ['projectstatus' => $inProgress]);
        try {
            return $create();
        } finally {
            $this->driver->updateProject($projectId, ['projectstatus' => $completed]);
        }
    }

    /** Stable de-dup reference: FUEL-{order_number}. */
    protected function referenceNo(Fuel $fuel): string
    {
        return mb_substr('FUEL-' . ($fuel->order_number ?: $fuel->id), 0, 100);
    }

    /**
     * Repair the PROJECTID on an ALREADY-CREATED PR - Diesel that landed on the
     * wrong project (e.g. synced before its trip's Sage project existed). Unlike
     * syncFuel(), this deliberately bypasses the "already synced → skip"
     * idempotency guard — it is an explicit, opt-in action (Reconciliation
     * queue), never called automatically, since Sage may reject it outright
     * once the document has been converted (Quote → PO) — that rejection is
     * expected and safe, surfaced as a normal error for a manual Sage-side fix.
     */
    public function repairTripProject(Fuel $fuel): array
    {
        $entity  = 'fuel_pr_diesel';
        $mapping = $this->mappingFor($this->integration, $entity, $fuel);

        if (! $mapping->exists || ! $mapping->external_id) {
            return $this->result(false, 'failed', null, 'This fuel order has not been synced to Sage yet — use the normal sync instead.', $entity, $fuel);
        }

        if (! $fuel->trip_id) {
            return $this->result(false, 'failed', $mapping->external_id, 'This fuel order is not linked to a trip — nothing to repair.', $entity, $fuel);
        }

        [$projectId, , $isTripProject] = $this->resolveProjectAndClass($fuel);
        if (! $isTripProject || ! $projectId) {
            return $this->result(false, 'failed', $mapping->external_id, "Could not resolve the trip's Sage project — sync the trip first.", $entity, $fuel);
        }

        $safeDocId = str_replace("'", '', $mapping->external_id);
        $lineRes   = $this->driver->readByQuery('PODOCUMENTENTRY', ['RECORDNO'], "DOCID = '{$safeDocId}'", 1);
        $recordNo  = $lineRes['data'][0]['RECORDNO'] ?? null;

        if (empty($lineRes['success']) || ! $recordNo) {
            return $this->fail($mapping, $entity, $fuel, 'Could not find the requisition line in Sage: ' . ($lineRes['error'] ?? 'no line returned.'), IntegrationMapping::STATUS_REQUIRES_ATTENTION);
        }

        $mapping->last_attempted_at = now();

        $res = $this->driver->updateRequisition($mapping->external_id, [[
            'recordno'  => $recordNo,
            'projectid' => $projectId,
        ]]);

        return $this->finishSync($mapping, $res, $mapping->external_id, 'update', $entity, $fuel);
    }

    /**
     * Resolve the fuelling station as a Sage VENDORID. Order: cached mapping →
     * the container's Gonyeti vendor (synced) → an existing Sage vendor of the
     * same NAME → create one. Cached per container in integration_mappings.
     */
    protected function resolveStationVendor(Container $container, Fuel $fuel): ?string
    {
        $mapping = $this->mappingFor($this->integration, 'fuel_station_vendor', $container);
        if ($mapping->exists && $mapping->external_id) {
            return $mapping->external_id;
        }

        $vendorSageId = null;

        // 1) The container's own Gonyeti vendor, if set — ensure it is in Sage.
        if ($container->vendor) {
            $vendor = $container->vendor;
            if (! ($vendor->sage_intacct_id ?: $vendor->custom_ref)) {
                if (! $vendor->company_id) {
                    $vendor->company_id = optional($fuel->user)->company_id ?? optional(optional($fuel->horse)->transporter)->company_id;
                }
                app(SageIntacctService::class)->syncVendor($vendor);
                $vendor->refresh();
            }
            $vendorSageId = $vendor->sage_intacct_id ?: $vendor->custom_ref;
        }

        // 2) An existing Sage vendor named after the station.
        $name = trim((string) $container->name);
        if (! $vendorSageId && $name !== '') {
            $vendorSageId = $this->findVendorIdByName($name);
        }

        // 3) Create a station vendor in Sage — ONLY when master-data push is enabled.
        //    Under the Finance model Sage is the sole vendor creator, so we do not
        //    auto-create the station; an unresolved station parks the fuel order for
        //    attention instead (caller returns requires_attention).
        if (! $vendorSageId && $name !== '' && config('sageintacct.master_data.push', false)) {
            $id  = (string) config('sageintacct.fuel.station_vendor_prefix', 'FSTN-') . $container->id;
            $res = $this->driver->createVendor([
                'id'       => $id,
                'name'     => $name,
                'taxgroup' => config('sageintacct.vendor.tax_group') ?: null,
                'currency' => optional($fuel->currency)->code ?: null,
                'status'   => 'active',
            ]);
            if (! empty($res['success']) || $this->isDuplicate($res['error'] ?? null)) {
                $vendorSageId = ($res['data']['id'] ?? null) ?: $id;
            }
        }

        if ($vendorSageId) {
            $mapping->local_model = get_class($container);
            $mapping->markSynced($vendorSageId, $name);
        }

        return $vendorSageId;
    }

    /** The diesel line item — an explicit config id, else ensure one by name. */
    protected function resolveDieselItem(): array
    {
        if ($id = config('sageintacct.fuel.item_id')) {
            return ['success' => true, 'external_id' => $id];
        }

        return $this->itemService->ensureNamedItem(
            (string) config('sageintacct.fuel.item_name', 'Diesel'),
            null,
            config('sageintacct.item.tax_group') ?: null
        );
    }

    /**
     * The Sage PROJECT + horse CLASS for the fuel line:
     *   • fuel on a trip  → the TRIP project (requires a brief reopen at post time,
     *                       since trip projects are Completed — see postAllowingProject)
     *   • otherwise       → the HORSE's own project (long-lived, purchasing-allowed)
     * CLASS is always the horse class.
     *
     * A fuel order can be approved/synced independently of its trip (e.g. before
     * the trip has ever been pushed to Sage), so this does not just read the
     * trip_project mapping — if it's missing, it syncs the trip's project
     * on demand (project only, no requisitions/fuel side effects — see
     * SageProjectService::ensureTripProject) so the PR still lands on the
     * correct trip instead of silently falling back to the horse project.
     *
     * @return array{0:?string,1:?string,2:bool}  [projectId, classId, isTripProject]
     */
    protected function resolveProjectAndClass(Fuel $fuel): array
    {
        $classId = $fuel->horse_id ? $this->mappingExternalId('horse_class', $fuel->horse_id) : null;

        if ($fuel->trip_id) {
            $tripProjectId = $this->mappingExternalId('trip_project', $fuel->trip_id);

            if (! $tripProjectId && $fuel->trip) {
                $projectService = new SageProjectService($this->driver, $this->integration, new SageClassService($this->driver, $this->integration));
                $tripProjectId  = $projectService->ensureTripProject($fuel->trip)['external_id'] ?? null;
            }

            if ($tripProjectId) {
                return [$tripProjectId, $classId, true];
            }
        }

        // No trip (or the trip project still couldn't be resolved) → the horse's own project.
        $horseProjectId = $fuel->horse_id ? $this->mappingExternalId('horse_project', $fuel->horse_id) : null;

        return [$horseProjectId, $classId, false];
    }

    protected function resolveDriverEmployee(Fuel $fuel): ?string
    {
        if (! $fuel->driver) {
            return null;
        }

        $employee = $this->employeeService->ensureForDriver($fuel->driver);

        return $employee['external_id'] ?? null;
    }

    protected function mappingExternalId(string $entityType, $localId): ?string
    {
        $m = IntegrationMapping::where([
            'company_integration_id' => $this->integration->id,
            'entity_type'            => $entityType,
            'local_id'               => $localId,
        ])->first();

        return $m && $m->external_id ? $m->external_id : null;
    }

    protected function findVendorIdByName(string $name): ?string
    {
        $safe = str_replace("'", '', $name);
        $res  = $this->driver->readByQuery('VENDOR', ['VENDORID', 'NAME'], "NAME = '{$safe}'", 1);

        return (! empty($res['success']) && ! empty($res['data'][0]['VENDORID'])) ? $res['data'][0]['VENDORID'] : null;
    }

    protected function vendorContactName(string $vendorSageId): ?string
    {
        $safe = str_replace("'", '', $vendorSageId);
        $res  = $this->driver->readByQuery('VENDOR', ['VENDORID', 'DISPLAYCONTACT.CONTACTNAME'], "VENDORID = '{$safe}'", 1);

        return (! empty($res['success']) && ! empty($res['data'][0]['DISPLAYCONTACT.CONTACTNAME']))
            ? $res['data'][0]['DISPLAYCONTACT.CONTACTNAME']
            : null;
    }

    protected function fail(IntegrationMapping $mapping, string $entity, Fuel $fuel, string $message, string $status): array
    {
        $status === IntegrationMapping::STATUS_REQUIRES_ATTENTION
            ? $mapping->markRequiresAttention($message)
            : $mapping->markFailed($message);

        return $this->result(false, $status, null, $message, $entity, $fuel);
    }

    protected function result(bool $success, string $status, ?string $externalId, ?string $error, string $entity, Fuel $fuel): array
    {
        return [
            'success'     => $success,
            'status'      => $status,
            'action'      => 'create',
            'entity'      => $entity,
            'model'       => $fuel,
            'external_id' => $externalId,
            'error'       => $error,
        ];
    }
}
