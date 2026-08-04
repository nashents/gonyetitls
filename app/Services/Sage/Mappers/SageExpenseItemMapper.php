<?php

namespace App\Services\Sage\Mappers;

use App\Models\Expense;
use App\Services\Sage\Support\SageFormat;

/**
 * Gonyeti Expense (master) → Sage ITEM (Non-Inventory).
 * Requisition lines reference these items. De-dup by NAME so we link to the
 * client's existing, tax-configured items instead of creating duplicates.
 */
class SageExpenseItemMapper
{
    /** Stable ITEMID: prefix + id (e.g. EXP-12). */
    public static function itemId(Expense $e): string
    {
        $prefix = (string) config('sageintacct.item.id_prefix', 'EXP-');
        $max    = (int) config('sageintacct.class.id_max_length', 20);

        return SageFormat::id($prefix . $e->id, $max);
    }

    /** NAME = the expense name (also the de-dup match key). */
    public static function name(Expense $e): ?string
    {
        return $e->name ?: null;
    }

    public static function map(Expense $e): array
    {
        $taxable = config('sageintacct.item.taxable', true);

        return [
            'id'            => self::itemId($e),
            'name'          => $e->name ?: ('Expense ' . $e->id),
            'type'          => (string) config('sageintacct.item.type', 'Non-Inventory'),
            'taxable'   => $taxable === false || $taxable === 'false' ? 'false' : 'true',
            // Item tax group name (nested form); helps the item resolve a
            // purchase tax schedule. Null ⇒ omitted.
            'tax_group' => config('sageintacct.item.tax_group') ?: null,
        ];
    }
}
