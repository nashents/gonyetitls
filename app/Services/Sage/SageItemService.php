<?php

namespace App\Services\Sage;

use App\Integrations\Contracts\SageDriver;
use App\Models\Allowance;
use App\Models\CompanyIntegration;
use App\Models\Expense;
use App\Models\Product;
use App\Services\Sage\Concerns\ManagesMappings;
use App\Services\Sage\Mappers\SageAllowanceItemMapper;
use App\Services\Sage\Mappers\SageExpenseItemMapper;
use App\Services\Sage\Mappers\SageProductItemMapper;

/**
 * Ensures a Gonyeti Expense exists as a Sage ITEM. De-dups by NAME so we link to
 * the client's existing (tax-configured) items rather than duplicating.
 */
class SageItemService
{
    use ManagesMappings;

    protected SageDriver $driver;
    protected CompanyIntegration $integration;

    public function __construct(SageDriver $driver, CompanyIntegration $integration)
    {
        $this->driver      = $driver;
        $this->integration = $integration;
    }

    /** Returns ['success'=>bool,'external_id'=>?string,'error'=>?string]. */
    public function ensureItem(Expense $expense): array
    {
        $mapping = $this->mappingFor($this->integration, 'expense_item', $expense);
        if ($mapping->exists && $mapping->external_id) {
            return ['success' => true, 'external_id' => $mapping->external_id];
        }

        $mapping->local_model       = get_class($expense);
        $mapping->local_reference   = SageExpenseItemMapper::name($expense) ?: SageExpenseItemMapper::itemId($expense);
        $mapping->last_attempted_at = now();
        if (! $mapping->exists) {
            $mapping->save();
        }

        $payload = SageExpenseItemMapper::map($expense);
        $itemId  = $payload['id'];
        $name    = SageExpenseItemMapper::name($expense);

        // Link to an existing Sage item with the same NAME (avoids the tax setup).
        if ($name && ($existing = $this->findItemIdByName($name))) {
            return $this->finishSync($mapping, $this->driver->updateItem($existing, $payload), $existing, 'link', 'expense_item', $expense);
        }

        $res = $this->driver->createItem($payload);
        if (empty($res['success']) && $this->isDuplicate($res['error'] ?? null)) {
            $res = $this->driver->updateItem($itemId, $payload);
            return $this->finishSync($mapping, $res, $itemId, 'update', 'expense_item', $expense);
        }

        return $this->finishSync($mapping, $res, $itemId, 'create', 'expense_item', $expense);
    }

    /**
     * Ensure a Gonyeti Product exists as a Sage ITEM (push). Mirrors ensureItem:
     * links to an existing Sage item by NAME (keeping its tax setup), else creates
     * one. Idempotent — once mapped, later calls update the item in place.
     *
     * @return array normalised finishSync result (SageSyncService audits it)
     */
    public function ensureProduct(Product $product): array
    {
        $mapping = $this->mappingFor($this->integration, 'product_item', $product);
        $payload = SageProductItemMapper::map($product);

        // Already linked → push updates (name / type / tax may have changed).
        if ($mapping->exists && $mapping->external_id) {
            return $this->finishSync($mapping, $this->driver->updateItem($mapping->external_id, $payload), $mapping->external_id, 'update', 'product_item', $product);
        }

        $mapping->local_model       = get_class($product);
        $mapping->local_reference   = SageProductItemMapper::name($product) ?: SageProductItemMapper::itemId($product);
        $mapping->last_attempted_at = now();
        if (! $mapping->exists) {
            $mapping->save();
        }

        $itemId = $payload['id'];
        $name   = SageProductItemMapper::name($product);

        // Link to an existing Sage item with the same NAME (the client catalogue).
        if ($name && ($existing = $this->findItemIdByName($name))) {
            return $this->finishSync($mapping, $this->driver->updateItem($existing, $payload), $existing, 'link', 'product_item', $product);
        }

        $res = $this->driver->createItem($payload);
        if (empty($res['success']) && $this->isDuplicate($res['error'] ?? null)) {
            $res = $this->driver->updateItem($itemId, $payload);
            return $this->finishSync($mapping, $res, $itemId, 'update', 'product_item', $product);
        }

        return $this->finishSync($mapping, $res, $itemId, 'create', 'product_item', $product);
    }

    /**
     * Ensure a Gonyeti Allowance exists as a Sage ITEM. Mirrors ensureItem —
     * de-dups by NAME so trip allowance lines link to existing items.
     */
    public function ensureAllowanceItem(Allowance $allowance): array
    {
        $mapping = $this->mappingFor($this->integration, 'allowance_item', $allowance);
        if ($mapping->exists && $mapping->external_id) {
            return ['success' => true, 'external_id' => $mapping->external_id];
        }

        $mapping->local_model       = get_class($allowance);
        $mapping->local_reference   = SageAllowanceItemMapper::name($allowance) ?: SageAllowanceItemMapper::itemId($allowance);
        $mapping->last_attempted_at = now();
        if (! $mapping->exists) {
            $mapping->save();
        }

        $payload = SageAllowanceItemMapper::map($allowance);
        $itemId  = $payload['id'];
        $name    = SageAllowanceItemMapper::name($allowance);

        if ($name && ($existing = $this->findItemIdByName($name))) {
            return $this->finishSync($mapping, $this->driver->updateItem($existing, $payload), $existing, 'link', 'allowance_item', $allowance);
        }

        $res = $this->driver->createItem($payload);
        if (empty($res['success']) && $this->isDuplicate($res['error'] ?? null)) {
            $res = $this->driver->updateItem($itemId, $payload);
            return $this->finishSync($mapping, $res, $itemId, 'update', 'allowance_item', $allowance);
        }

        return $this->finishSync($mapping, $res, $itemId, 'create', 'allowance_item', $allowance);
    }

    /**
     * Ensure a plain Sage ITEM exists by name (not tied to a Gonyeti model) —
     * used for the shared diesel item. Links to an existing item by NAME, else
     * creates one. Returns ['success'=>bool,'external_id'=>?string,'error'=>?].
     */
    public function ensureNamedItem(string $name, ?string $itemId = null, ?string $taxGroup = null): array
    {
        if ($existing = $this->findItemIdByName($name)) {
            return ['success' => true, 'external_id' => $existing];
        }

        $id      = $itemId ?: \App\Services\Sage\Support\SageFormat::id('ITM-' . \Illuminate\Support\Str::slug($name), (int) config('sageintacct.class.id_max_length', 20));
        $payload = [
            'id'        => $id,
            'name'      => $name,
            'type'      => (string) config('sageintacct.item.type', 'Non-Inventory'),
            'taxable'   => $taxGroup ? 'true' : null,
            'tax_group' => $taxGroup,
        ];

        $res = $this->driver->createItem($payload);
        if (! empty($res['success']) || $this->isDuplicate($res['error'] ?? null)) {
            return ['success' => true, 'external_id' => $id];
        }

        return ['success' => false, 'error' => $res['error'] ?? 'Diesel item could not be created in Sage.'];
    }

    protected function findItemIdByName(string $name): ?string
    {
        $safe = str_replace("'", '', $name);
        $res  = $this->driver->readByQuery('ITEM', ['ITEMID', 'NAME'], "NAME = '{$safe}'", 1);

        return (! empty($res['success']) && ! empty($res['data'][0]['ITEMID'])) ? $res['data'][0]['ITEMID'] : null;
    }
}
