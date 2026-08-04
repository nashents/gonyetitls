<?php

namespace App\Services\Sage\Mappers;

use App\Models\Allowance;
use App\Services\Sage\Support\SageFormat;

/**
 * Gonyeti Allowance (trip allowance master) → Sage ITEM (Non-Inventory).
 * Dispatch-sheet / requisition lines reference these items. De-dup by NAME so we
 * link to the client's existing, tax-configured items instead of duplicating.
 */
class SageAllowanceItemMapper
{
    /** Stable ITEMID: prefix + id (e.g. ALW-5). */
    public static function itemId(Allowance $a): string
    {
        $prefix = (string) config('sageintacct.item.allowance_id_prefix', 'ALW-');
        $max    = (int) config('sageintacct.class.id_max_length', 20);

        return SageFormat::id($prefix . $a->id, $max);
    }

    /** NAME = the allowance name (also the de-dup match key). */
    public static function name(Allowance $a): ?string
    {
        return $a->name ?: null;
    }

    public static function map(Allowance $a): array
    {
        // Prefer the allowance's own tax group; else the item default.
        $taxGroup = optional($a->tax)->name ?: (config('sageintacct.item.tax_group') ?: null);

        return [
            'id'        => self::itemId($a),
            'name'      => $a->name ?: ('Allowance ' . $a->id),
            'type'      => (string) config('sageintacct.item.type', 'Non-Inventory'),
            'taxable'   => $taxGroup ? 'true' : 'false',
            'tax_group' => $taxGroup,
        ];
    }
}
