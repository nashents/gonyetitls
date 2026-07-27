<?php

namespace App\Services\Sage;

use App\Integrations\Contracts\SageDriver;
use App\Models\CompanyIntegration;
use App\Models\Expense;
use App\Services\Sage\Concerns\ManagesMappings;
use App\Services\Sage\Mappers\SageExpenseItemMapper;

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

    protected function findItemIdByName(string $name): ?string
    {
        $safe = str_replace("'", '', $name);
        $res  = $this->driver->readByQuery('ITEM', ['ITEMID', 'NAME'], "NAME = '{$safe}'", 1);

        return (! empty($res['success']) && ! empty($res['data'][0]['ITEMID'])) ? $res['data'][0]['ITEMID'] : null;
    }
}
