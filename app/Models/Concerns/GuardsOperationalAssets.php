<?php

namespace App\Models\Concerns;

use App\Models\Horse;
use App\Models\Trailer;
use App\Models\Vehicle;
use Illuminate\Validation\ValidationException;

/**
 * Applied to operational records (bookings, inspections, workshop, stores...):
 * refuses to attach a once-off transporter's horse/trailer/vehicle. Only runs
 * when the asset column changes, so older records keep saving as before.
 */
trait GuardsOperationalAssets
{
    public static function bootGuardsOperationalAssets()
    {
        static::saving(function ($model) {
            foreach (['horse_id' => Horse::class, 'trailer_id' => Trailer::class, 'vehicle_id' => Vehicle::class] as $column => $class) {
                if (! $model->isDirty($column) || ! $model->{$column}) {
                    continue;
                }

                $asset = $class::with('transporter')->find($model->{$column});

                if ($asset && ! $asset->isOperational()) {
                    throw ValidationException::withMessages([
                        $column => ($asset->identifier_label ?: $asset->registration_number)
                            .' belongs to a once-off transporter ('.$asset->transporter->name.') and can only be used on trips. Mark the transporter as Managed to use it here.',
                    ]);
                }
            }
        });
    }
}
