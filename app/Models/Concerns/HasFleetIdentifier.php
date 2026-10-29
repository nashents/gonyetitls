<?php

namespace App\Models\Concerns;

use App\Models\Transporter;
use App\Support\FleetIdentifier;

/**
 * Applied to Horse/Trailer/Vehicle: exposes the company's Fleet Number vs
 * VRN preference as a display label and a query sort scope, plus the
 * operational (own + managed fleet) filter.
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

    /**
     * Assets that belong in operational modules (bookings, inspections,
     * workshop, stores...): own-fleet and managed transporters, plus assets
     * with no transporter. Once-off transporters' assets are trips-only.
     */
    public function scopeOperational($query)
    {
        $column = $query->qualifyColumn('transporter_id');

        return $query->where(function ($q) use ($column) {
            $q->whereNull($column)
                ->orWhereIn($column, Transporter::query()->operational()->select('id'));
        });
    }

    public function isOperational(): bool
    {
        return ! $this->transporter_id || (bool) $this->transporter?->isOperational();
    }
}
