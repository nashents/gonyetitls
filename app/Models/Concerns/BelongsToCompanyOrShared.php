<?php

namespace App\Models\Concerns;

/**
 * Multi-tenancy for lookups with shared defaults: rows of the user's company
 * plus rows with no company (e.g. national public holidays, statutory PAYE
 * tables). New rows are NOT auto-stamped - null stays shared on purpose.
 */
trait BelongsToCompanyOrShared
{
    use BelongsToCompany;

    protected static function companyScopeShared(): bool
    {
        return true;
    }
}
