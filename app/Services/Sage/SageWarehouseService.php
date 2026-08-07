<?php

namespace App\Services\Sage;

use App\Integrations\Contracts\SageDriver;
use App\Models\CompanyIntegration;
use App\Models\Store;
use App\Services\Sage\Concerns\ManagesMappings;
use App\Services\Sage\Support\SageFormat;

/**
 * Syncs a Gonyeti Store to a Sage WAREHOUSE (push). De-dups by NAME so we link to
 * the client's existing warehouse rather than duplicating it. Idempotent via the
 * mapping entity_type="store_warehouse", local_id=store_id — once linked, later
 * calls update the warehouse in place.
 */
class SageWarehouseService
{
    use ManagesMappings;

    protected SageDriver $driver;
    protected CompanyIntegration $integration;

    public function __construct(SageDriver $driver, CompanyIntegration $integration)
    {
        $this->driver      = $driver;
        $this->integration = $integration;
    }

    /** @return array normalised finishSync result (SageSyncService audits it) */
    public function ensureStore(Store $store): array
    {
        $mapping = $this->mappingFor($this->integration, 'store_warehouse', $store);
        $payload = $this->payload($store);

        // Already linked → push updates (name / status may have changed).
        if ($mapping->exists && $mapping->external_id) {
            return $this->finishSync($mapping, $this->driver->updateWarehouse($mapping->external_id, $payload), $mapping->external_id, 'update', 'store_warehouse', $store);
        }

        $mapping->local_model       = get_class($store);
        $mapping->local_reference   = $store->name ?: $payload['id'];
        $mapping->last_attempted_at = now();
        if (! $mapping->exists) {
            $mapping->save();
        }

        $warehouseId = $payload['id'];

        // Link to an existing Sage warehouse with the same NAME (the client's own).
        if ($store->name && ($existing = $this->findWarehouseIdByName($store->name))) {
            return $this->finishSync($mapping, $this->driver->updateWarehouse($existing, $payload), $existing, 'link', 'store_warehouse', $store);
        }

        $res = $this->driver->createWarehouse($payload);
        if (empty($res['success']) && $this->isDuplicate($res['error'] ?? null)) {
            $res = $this->driver->updateWarehouse($warehouseId, $payload);
            return $this->finishSync($mapping, $res, $warehouseId, 'update', 'store_warehouse', $store);
        }

        return $this->finishSync($mapping, $res, $warehouseId, 'create', 'store_warehouse', $store);
    }

    /** Sage WAREHOUSE payload from a Store (id generated WH-{store_id}). */
    protected function payload(Store $store): array
    {
        return [
            'id'         => SageFormat::id('WH-' . $store->id, (int) config('sageintacct.class.id_max_length', 20)),
            'name'       => $store->name,
            'status'     => ((string) $store->status === '0') ? 'inactive' : 'active',
            // A multi-entity company REQUIRES a LOCATIONID on each warehouse. Use
            // the configured warehouse location, else the operating entity every
            // other document is scoped to (purchasing.entity_id, e.g. E100).
            'locationid' => config('sageintacct.warehouse.location_id')
                ?: (config('sageintacct.purchasing.entity_id') ?: null),
        ];
    }

    protected function findWarehouseIdByName(string $name): ?string
    {
        $safe = str_replace("'", '', $name);
        $res  = $this->driver->readByQuery('WAREHOUSE', ['WAREHOUSEID', 'NAME'], "NAME = '{$safe}'", 1);

        return (! empty($res['success']) && ! empty($res['data'][0]['WAREHOUSEID'])) ? $res['data'][0]['WAREHOUSEID'] : null;
    }
}
