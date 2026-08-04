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
 * Syncs a Gonyeti Fuel order to a Sage "PR - Diesel" document.
 *
 *  • supplier   = the fuelling station (the fuel's Container — matched to a Sage
 *                 VENDOR by name, created if missing);
 *  • line       = one diesel item (config fuel.item_*), qty = litres, price = unit
 *                 price, attached to the trip PROJECT when the fuel is on a trip
 *                 (+ horse CLASS + driver EMPLOYEE);
 *  • header     = REG (truck reg) + Driver custom fields (required by the
 *                 definition), entity-scoped so the Convert action is available.
 *
 * Idempotent: mapping entity_type="fuel_pr_diesel", local_id=fuel_id — an
 * already-created document is skipped.
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

        [$projectId, $classId] = $this->resolveTripProjectAndClass($fuel);
        $employeeId            = $this->resolveDriverEmployee($fuel);
        $vendorContact         = $this->vendorContactName($vendorSageId) ?: $container->name;

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

        $header = [
            'transactiontype' => (string) config('sageintacct.purchasing.diesel_type', 'PR - Diesel'),
            'datecreated'     => $fuel->date ?: now()->toDateString(),
            'datedue'         => $fuel->date ?: now()->toDateString(),
            'vendorid'        => $vendorSageId,
            'referenceno'     => $this->referenceNo($fuel),
            'contactname'     => $vendorContact,
            'currency'        => optional($fuel->currency)->code ?: null,
            'exchratetype'    => config('sageintacct.purchasing.exchange_rate_type') ?: null,
            'entityid'        => config('sageintacct.purchasing.entity_id') ?: null,
            'customfields'    => [
                (string) config('sageintacct.purchasing.dispatch_reg_field', 'REG')       => $this->truckRegistration($fuel),
                (string) config('sageintacct.purchasing.dispatch_driver_field', 'Driver') => $this->driverName($fuel),
            ],
        ];

        $res = $this->driver->createRequisition($header, [$line]);

        return $this->finishSync($mapping, $res, $header['referenceno'], 'create', $entity, $fuel);
    }

    /** Stable de-dup reference: FUEL-{order_number}. */
    protected function referenceNo(Fuel $fuel): string
    {
        return mb_substr('FUEL-' . ($fuel->order_number ?: $fuel->id), 0, 100);
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

        // 3) Create a station vendor in Sage.
        if (! $vendorSageId && $name !== '') {
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
     * Attach to the trip's Sage PROJECT (+ horse CLASS) when the fuel is on a
     * trip. Uses existing mappings; syncs the trip once if not yet in Sage.
     *
     * @return array{0:?string,1:?string}  [projectId, classId]
     */
    protected function resolveTripProjectAndClass(Fuel $fuel): array
    {
        $projectId = null;
        $classId   = null;

        // Attach to the trip project when the trip is already synced to Sage.
        // (Trips sync on creation; we don't trigger a heavy trip sync here.)
        if ($fuel->trip_id) {
            $projectId = $this->mappingExternalId('trip_project', $fuel->trip_id);
        }

        if ($fuel->horse_id) {
            $classId = $this->mappingExternalId('horse_class', $fuel->horse_id);
        }

        return [$projectId, $classId];
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

    /** REG custom field (truck registration) — the fuel's horse. */
    protected function truckRegistration(Fuel $fuel): string
    {
        return (string) (optional($fuel->horse)->registration_number ?: '');
    }

    /** Driver custom field — the fuel's driver name. */
    protected function driverName(Fuel $fuel): string
    {
        $employee = optional($fuel->driver)->employee;
        $name     = trim(trim((string) optional($employee)->name) . ' ' . trim((string) optional($employee)->surname));

        return $name !== '' ? $name : (string) optional($fuel->employee)->name;
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
