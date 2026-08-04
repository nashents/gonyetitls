<?php

namespace App\Services\Sage\Mappers;

use App\Models\Product;
use App\Services\Sage\Support\SageFormat;

/**
 * Gonyeti Product (products & services listing) ↔ Sage ITEM.
 *
 * Push: type maps to Sage ITEMTYPE and the product's linked Tax (whose name
 * mirrors a Sage item tax group — see the tax pull) becomes the item TAXGROUP.
 * Pull: the reverse maps run in SagePullService.
 * De-dup on push is by NAME so we link to the client's existing, tax-configured
 * catalogue items instead of creating duplicates.
 */
class SageProductItemMapper
{
    /** Stable ITEMID: the Gonyeti product_number when set, else prefix + id. */
    public static function itemId(Product $p): string
    {
        $prefix = (string) config('sageintacct.item.product_id_prefix', 'PRD-');
        $max    = (int) config('sageintacct.class.id_max_length', 20);

        $base = $p->product_number ?: ($prefix . $p->id);

        return SageFormat::id($base, $max);
    }

    /** NAME = the product name (also the de-dup match key). */
    public static function name(Product $p): ?string
    {
        return $p->name ?: null;
    }

    /** Gonyeti product type → Sage ITEMTYPE ("Inventory" | "Non-Inventory"). */
    public static function sageType(?string $type): string
    {
        $t = strtolower(trim((string) $type));

        if ($t === '') {
            return (string) config('sageintacct.item.type', 'Non-Inventory');
        }

        return str_starts_with($t, 'inventory') ? 'Inventory' : 'Non-Inventory';
    }

    /** Sage ITEMTYPE → Gonyeti product type (form options: Inventory | Non Inventory). */
    public static function gonyetiType(?string $sageType): string
    {
        return str_starts_with(strtolower(trim((string) $sageType)), 'non')
            ? 'Non Inventory'
            : 'Inventory';
    }

    public static function map(Product $p): array
    {
        // The product's linked Tax name is the Sage item tax group name; when a
        // product has no tax, fall back to the item default (so the item still
        // resolves a tax schedule on purchase orders / receipts / requisitions).
        $taxGroup = optional($p->tax)->name ?: (config('sageintacct.item.tax_group') ?: null);
        $type     = self::sageType($p->type);

        return [
            'id'        => self::itemId($p),
            'name'      => $p->name ?: ('Product ' . $p->id),
            'type'      => $type,
            'taxable'   => $taxGroup ? 'true' : null,
            // Item GL Group (revenue/COGS/inventory accounts). Prefer the value
            // captured when this product was pulled from Sage; else the configured
            // default for its type. Null ⇒ omitted.
            'gl_group'  => self::glGroup($p, $type),
            // Item tax group (nested form); null ⇒ omitted.
            'tax_group' => $taxGroup,
        ];
    }

    /** Resolve the item GL group: the product's own value, else a type default. */
    public static function glGroup(Product $p, ?string $sageType = null): ?string
    {
        if (! empty($p->gl_group)) {
            return $p->gl_group;
        }

        $sageType = $sageType ?: self::sageType($p->type);
        $default  = $sageType === 'Inventory'
            ? config('sageintacct.item.gl_group_inventory')
            : config('sageintacct.item.gl_group');

        return $default ?: null;
    }
}
