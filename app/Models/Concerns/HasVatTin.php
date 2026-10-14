<?php

namespace App\Models\Concerns;

/**
 * Combined "VAT / TIN" display value for Customer and Vendor. Only the
 * numbers actually captured are shown, so a lone VAT or TIN number never
 * renders with a dangling "/".
 */
trait HasVatTin
{
    public function getVatTinAttribute(): string
    {
        return collect([$this->vat_number, $this->tin_number])
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->implode(' / ');
    }
}
