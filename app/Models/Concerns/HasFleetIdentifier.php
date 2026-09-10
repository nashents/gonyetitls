<?php

namespace App\Models\Concerns;

use App\Support\FleetIdentifier;

/**
 * Applied to Horse/Trailer/Vehicle: exposes the company's Fleet Number vs
 * VRN preference as a display label and a query sort scope.
 */
trait HasFleetIdentifier
{
    public function getIdentifierLabelAttribute(): string
    {
        return FleetIdentifier::label($this);
    }

    public function scopeOrderByIdentifier($query, string $direction = 'asc')
    {
        return $query->orderBy(FleetIdentifier::primaryColumn(), $direction);
    }
}
